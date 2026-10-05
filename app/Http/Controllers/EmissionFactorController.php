<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmissionFactorRequest;
use App\Models\EmissionFactor;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;

/**
 * F15 - master data for emission coefficients. See EmissionFactorPolicy's
 * doc-comment for why create is Super Admin/Project Admin only, and the
 * emission_factors migration's doc-comment for the versioning rule this
 * store() enforces: a factor_value already used to calculate a
 * carbon_calculations row is never edited, only superseded.
 */
class EmissionFactorController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', EmissionFactor::class);

        $factors = EmissionFactor::with('createdBy')
            ->orderBy('category')
            ->orderByDesc('effective_from')
            ->paginate(20);

        return view('emission-factors.index', compact('factors'));
    }

    public function create()
    {
        $this->authorize('create', EmissionFactor::class);

        return view('emission-factors.create');
    }

    public function store(StoreEmissionFactorRequest $request)
    {
        $this->authorize('create', EmissionFactor::class);

        $data = $request->validated();

        // Close out the currently-open row (if any) for this category so
        // effectiveFor() never has two overlapping "current" rows to
        // choose between - see the migration's doc-comment.
        $previous = EmissionFactor::where('category', $data['category'])
            ->whereNull('effective_to')
            ->first();

        if ($previous) {
            $previous->update([
                'effective_to' => Carbon::parse($data['effective_from'])->subDay()->toDateString(),
            ]);
        }

        $data['created_by'] = $request->user()->id;
        EmissionFactor::create($data);

        return redirect()->route('emission-factors.index')
            ->with('status', 'เพิ่มค่าสัมประสิทธิ์การปล่อยก๊าซเรือนกระจกเรียบร้อยแล้ว');
    }
}
