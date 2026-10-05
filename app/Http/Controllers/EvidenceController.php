<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEvidenceRequest;
use App\Models\BranchDisposalRecord;
use App\Models\Evidence;
use App\Models\Farm;
use App\Models\FarmActivity;
use App\Models\HarvestRecord;
use App\Models\HouseholdBaseline;
use App\Models\KilnBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * F10 - Evidence Management (Blueprint section 9). Stored on the local
 * disk for the MVP via the "evidence" filesystem disk (config/filesystems.php)
 * so switching to S3/MinIO later is a config change, not a code change.
 *
 * Authorization (15 ก.ย. round): before this, any logged-in user could
 * upload or delete evidence on ANY record (FarmActivity/HouseholdBaseline),
 * since this controller had no check at all beyond `auth`. It now
 * authorizes against whichever Policy already governs the parent record
 * (FarmActivityPolicy/HouseholdBaselinePolicy's `view`) - StoreEvidenceRequest
 * already restricts evidenceable_type to that fixed allow-list, so
 * `$type::findOrFail()` below never instantiates an arbitrary class.
 *
 * EXIF GPS check (Blueprint section 9, 4th bullet - this round): reads
 * GPS + capture time from a JPEG's EXIF (if present, best-effort only -
 * PNG/WebP/PDF never carry this) and compares it against the nearest
 * `farms.gps_lat/gps_lng` we can resolve for the record this evidence is
 * attached to. Too far apart only WARNS the reviewer (session
 * 'gps_warning', rendered in layouts/app.blade.php) - it never blocks the
 * upload, since handheld GPS can legitimately be off by a few hundred
 * meters. Image compression and thumbnail generation are handled by the
 * queued CompressEvidenceImage job after the original is stored.
 */
class EvidenceController extends Controller
{
    use AuthorizesRequests;

    public function store(StoreEvidenceRequest $request)
    {
        $type = $request->string('evidenceable_type')->toString();
        $evidenceable = $type::findOrFail($request->integer('evidenceable_id'));
        $this->authorize('view', $evidenceable);

        $file = $request->file('file');
        $path = $file->store(
            Str::of($type)->classBasename()->lower().'/'.$request->integer('evidenceable_id'),
            'evidence'
        );

        [$gpsLat, $gpsLng, $capturedAt] = $this->readExifGps($file);

        $evidence = Evidence::create([
            'evidenceable_type' => $type,
            'evidenceable_id' => $request->integer('evidenceable_id'),
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'gps_lat' => $gpsLat,
            'gps_lng' => $gpsLng,
            'captured_at' => $capturedAt,
            'uploaded_by' => $request->user()->id,
        ]);

        // Backlog item (Blueprint หัวข้อ 9): compress + generate a thumbnail
        // on the queue, never synchronously here, so a slow/large upload
        // never blocks the HTTP response - see CompressEvidenceImage's
        // doc-comment for why it's safe to fire-and-forget.
        if (str_starts_with($evidence->file_type, 'image/')) {
            \App\Jobs\CompressEvidenceImage::dispatch($evidence->id);
        }

        $warning = $this->gpsWarningFor($evidenceable, $gpsLat, $gpsLng);

        $redirect = back()->with('status', 'อัปโหลดหลักฐานเรียบร้อย');

        if ($warning) {
            $redirect->with('gps_warning', $warning);
        }

        return $redirect;
    }

    public function destroy(Evidence $evidence)
    {
        $this->authorize('view', $evidence->evidenceable);

        Storage::disk('evidence')->delete(array_filter([
            $evidence->file_path,
            $evidence->thumbnail_path,
        ]));
        $evidence->delete();

        return back()->with('status', 'ลบหลักฐานแล้ว');
    }

    public function download(Evidence $evidence, Request $request)
    {
        $this->authorize('view', $evidence->evidenceable);

        $path = $request->boolean('thumbnail') && $evidence->thumbnail_path
            ? $evidence->thumbnail_path
            : $evidence->file_path;

        abort_unless(Storage::disk('evidence')->exists($path), 404);

        return Storage::disk('evidence')->response(
            $path,
            basename($path),
            ['Content-Disposition' => 'inline; filename="'.basename($path).'"']
        );
    }

    /**
     * @return array{0: ?float, 1: ?float, 2: ?Carbon}
     */
    private function readExifGps(UploadedFile $file): array
    {
        if (! function_exists('exif_read_data') || ! str_contains((string) $file->getMimeType(), 'jpeg')) {
            return [null, null, null];
        }

        // exif_read_data() warns (not throws) on a file with no/malformed
        // EXIF block - @ is deliberate here, "no EXIF" is an expected,
        // silent outcome for plenty of legitimate photos, not an error to
        // surface to the uploader.
        $exif = @exif_read_data($file->getRealPath(), 'ANY_TAG', true);

        if (! $exif) {
            return [null, null, null];
        }

        $gps = $exif['GPS'] ?? [];
        $lat = $this->exifGpsToDecimal($gps, 'GPSLatitude', 'GPSLatitudeRef');
        $lng = $this->exifGpsToDecimal($gps, 'GPSLongitude', 'GPSLongitudeRef');

        $capturedAt = null;
        $dateString = $exif['EXIF']['DateTimeOriginal'] ?? $exif['IFD0']['DateTime'] ?? null;

        if ($dateString) {
            try {
                $capturedAt = Carbon::createFromFormat('Y:m:d H:i:s', $dateString);
            } catch (\Throwable) {
                $capturedAt = null;
            }
        }

        return [$lat, $lng, $capturedAt];
    }

    /**
     * EXIF GPS coordinates come as three [degrees, minutes, seconds]
     * fractions (each "num/den" strings) plus a hemisphere ref (N/S/E/W) -
     * this turns that into a signed decimal degree.
     */
    private function exifGpsToDecimal(array $gps, string $coordKey, string $refKey): ?float
    {
        if (empty($gps[$coordKey]) || empty($gps[$refKey]) || count($gps[$coordKey]) < 3) {
            return null;
        }

        $degrees = $this->exifFractionToFloat($gps[$coordKey][0]);
        $minutes = $this->exifFractionToFloat($gps[$coordKey][1]);
        $seconds = $this->exifFractionToFloat($gps[$coordKey][2]);

        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

        if (in_array(strtoupper((string) $gps[$refKey]), ['S', 'W'], true)) {
            $decimal *= -1;
        }

        return round($decimal, 7);
    }

    private function exifFractionToFloat(string $fraction): float
    {
        if (! str_contains($fraction, '/')) {
            return (float) $fraction;
        }

        [$numerator, $denominator] = explode('/', $fraction, 2);

        return (float) $denominator !== 0.0 ? ((float) $numerator / (float) $denominator) : 0.0;
    }

    /**
     * "พิกัดของแปลงที่เกี่ยวข้อง" (Blueprint 9) - plots no longer carry their
     * own GPS (see the remove_gps_from_plots_table migration), so this
     * resolves the farm underneath whatever record the evidence is
     * attached to. HouseholdBaseline/KilnBatch are household-level, not
     * plot-level, so for those this is a best-effort "any farm of this
     * household that has GPS set" rather than one specific plot's farm.
     */
    private function resolveReferenceFarm(Model $evidenceable): ?Farm
    {
        return match (true) {
            $evidenceable instanceof FarmActivity, $evidenceable instanceof HarvestRecord, $evidenceable instanceof BranchDisposalRecord => $evidenceable->plot?->farm,
            $evidenceable instanceof HouseholdBaseline => $evidenceable->household?->farms
                ->first(fn (Farm $farm) => $farm->gps_lat !== null && $farm->gps_lng !== null),
            $evidenceable instanceof KilnBatch => $evidenceable->household?->farms
                ->first(fn (Farm $farm) => $farm->gps_lat !== null && $farm->gps_lng !== null),
            default => null,
        };
    }

    private function gpsWarningFor(Model $evidenceable, ?float $gpsLat, ?float $gpsLng): ?string
    {
        if ($gpsLat === null || $gpsLng === null) {
            return null;
        }

        $farm = $this->resolveReferenceFarm($evidenceable);

        if (! $farm || $farm->gps_lat === null || $farm->gps_lng === null) {
            return null;
        }

        $distanceKm = $this->haversineKm(
            $gpsLat, $gpsLng,
            (float) $farm->gps_lat, (float) $farm->gps_lng
        );

        $thresholdKm = (float) config('drfis.evidence_gps_warning_km', 2.0);

        if ($distanceKm <= $thresholdKm) {
            return null;
        }

        return sprintf(
            'คำเตือน: พิกัด GPS ในรูปภาพห่างจากพิกัดสวน "%s" ที่บันทึกไว้ประมาณ %.1f กม. กรุณาตรวจสอบว่าเป็นรูปจากพื้นที่ถูกต้องหรือไม่ (GPS มือถืออาจคลาดเคลื่อนได้บ้าง)',
            $farm->farm_name ?? $farm->farm_code,
            $distanceKm
        );
    }

    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }
}
