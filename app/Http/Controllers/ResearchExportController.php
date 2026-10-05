<?php

namespace App\Http\Controllers;

use App\Exports\ResearchDatasetExport;
use App\Models\AuditLog;
use App\Models\Household;
use App\Services\AreaScopeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResearchExportController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Household::class);

        return view('reports.index');
    }

    public function csv(Request $request, AreaScopeService $areaScope): StreamedResponse
    {
        $ids = $this->authorizeAndScope($request, $areaScope);
        $export = new ResearchDatasetExport($ids);
        $this->audit($request, 'export_csv');

        return response()->streamDownload(function () use ($export) {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, $export->headings());
            foreach ($export->array() as $row) {
                fputcsv($stream, $row);
            }
            fclose($stream);
        }, 'drfis-research-dataset-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function xlsx(Request $request, AreaScopeService $areaScope)
    {
        $ids = $this->authorizeAndScope($request, $areaScope);
        $this->audit($request, 'export_xlsx');

        return Excel::download(new ResearchDatasetExport($ids), 'drfis-research-dataset-'.now()->format('Ymd-His').'.xlsx');
    }

    public function pdf(Request $request, AreaScopeService $areaScope)
    {
        $ids = $this->authorizeAndScope($request, $areaScope);
        $rows = (new ResearchDatasetExport($ids))->array();
        $this->audit($request, 'export_pdf');

        return Pdf::loadView('reports.summary-pdf', ['rows' => $rows, 'generatedAt' => now()])
            ->setPaper('a4', 'landscape')
            ->download('drfis-summary-'.now()->format('Ymd-His').'.pdf');
    }

    private function authorizeAndScope(Request $request, AreaScopeService $areaScope): ?array
    {
        $this->authorize('viewAny', Household::class);

        return $areaScope->householdIdsFor($request->user());
    }

    private function audit(Request $request, string $action): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'auditable_type' => Household::class,
            'auditable_id' => 0,
            'action' => $action,
            'old_values' => null,
            'new_values' => ['format' => substr($action, 7)],
            'ip_address' => $request->ip(),
        ]);
    }
}
