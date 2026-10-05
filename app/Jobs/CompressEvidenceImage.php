<?php

namespace App\Jobs;

use App\Models\Evidence;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * F10 backlog item (Blueprint หัวข้อ 9, 3rd bullet: "บีบอัดรูปภาพ (compress
 * เป็น WebP หรือ JPEG คุณภาพ 80%) และสร้าง thumbnail ผ่าน queued job เพื่อไม่ให้
 * การอัปโหลดช้า"). Dispatched from EvidenceController::store() right after
 * an image Evidence row is created - never done synchronously in the
 * request, exactly per that requirement.
 *
 * Defensive by design: this job NEVER fails the queue or retries
 * indefinitely over a bad/corrupt image or a server without the GD
 * extension - any problem is logged and swallowed, leaving the original
 * upload exactly as EvidenceController::store() saved it (thumbnail_path
 * simply stays null, same as any non-image evidence). A missing
 * thumbnail is a cosmetic degradation, not a data-loss risk - the
 * original file_path is only ever replaced AFTER a successful re-encode.
 */
class CompressEvidenceImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    /** Longest edge (px) for the generated thumbnail. */
    private const THUMBNAIL_MAX_DIMENSION = 400;

    /** GD quality (0-100) - Blueprint's "JPEG คุณภาพ 80%" applied to WebP too. */
    private const QUALITY = 80;

    public function __construct(public int $evidenceId) {}

    public function handle(): void
    {
        $evidence = Evidence::find($this->evidenceId);

        if (! $evidence || ! str_starts_with($evidence->file_type, 'image/')) {
            return;
        }

        if (! extension_loaded('gd')) {
            Log::warning('CompressEvidenceImage: ข้าม - ไม่มี GD extension บนเซิร์ฟเวอร์นี้', ['evidence_id' => $evidence->id]);

            return;
        }

        $disk = Storage::disk('evidence');

        if (! $disk->exists($evidence->file_path)) {
            Log::warning('CompressEvidenceImage: ข้าม - ไม่พบไฟล์ต้นฉบับ', ['evidence_id' => $evidence->id, 'path' => $evidence->file_path]);

            return;
        }

        try {
            $this->compress($evidence, $disk);
        } catch (\Throwable $e) {
            Log::warning('CompressEvidenceImage: บีบอัดไม่สำเร็จ ใช้ไฟล์ต้นฉบับต่อไป', [
                'evidence_id' => $evidence->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function compress(Evidence $evidence, $disk): void
    {
        $raw = $disk->get($evidence->file_path);
        $image = @imagecreatefromstring($raw);

        if ($image === false) {
            Log::warning('CompressEvidenceImage: ไฟล์นี้ไม่ใช่รูปภาพที่ GD อ่านได้', ['evidence_id' => $evidence->id]);

            return;
        }

        // Preserve transparency instead of flattening to black when
        // re-encoding a PNG/GIF with an alpha channel.
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $useWebp = function_exists('imagewebp');
        $extension = $useWebp ? 'webp' : 'jpg';
        $mime = $useWebp ? 'image/webp' : 'image/jpeg';

        $dir = Str::of($evidence->file_path)->beforeLast('/')->toString();
        $basename = Str::of($evidence->file_path)->afterLast('/')->beforeLast('.')->toString();

        $compressedPath = "{$dir}/{$basename}.{$extension}";
        $thumbnailPath = "{$dir}/{$basename}_thumb.{$extension}";

        $compressedFull = $disk->path($compressedPath);
        $thumbnailFull = $disk->path($thumbnailPath);

        $this->encode($image, $compressedFull, $useWebp);

        $thumbnail = $this->makeThumbnail($image, self::THUMBNAIL_MAX_DIMENSION);
        $this->encode($thumbnail, $thumbnailFull, $useWebp);
        imagedestroy($thumbnail);
        imagedestroy($image);

        // Only now (after both new files are confirmed written) remove
        // the original, so a mid-way failure above never leaves the
        // Evidence row pointing at a deleted file.
        if ($compressedPath !== $evidence->file_path) {
            $disk->delete($evidence->file_path);
        }

        $evidence->forceFill([
            'file_path' => $compressedPath,
            'thumbnail_path' => $thumbnailPath,
            'file_type' => $mime,
            'file_size' => filesize($compressedFull) ?: $evidence->file_size,
        ])->save();
    }

    /**
     * @return \GdImage
     */
    private function makeThumbnail($image, int $maxDimension)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min(1.0, $maxDimension / max($width, $height));

        $thumbWidth = max(1, (int) round($width * $scale));
        $thumbHeight = max(1, (int) round($height * $scale));

        $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);

        imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);

        return $thumbnail;
    }

    private function encode($image, string $fullPath, bool $useWebp): void
    {
        if ($useWebp) {
            imagewebp($image, $fullPath, self::QUALITY);
        } else {
            imagejpeg($image, $fullPath, self::QUALITY);
        }
    }
}
