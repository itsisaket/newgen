<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTechnologyRequest;
use App\Models\Technology;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * F12 - Technology Management, master type list (Blueprint section 8 /
 * Appendix C.4 `technologies`). See TechnologyPolicy's doc-comment for
 * authorization. No edit/destroy for now (same "master data grows,
 * doesn't get renamed/removed" convention as DurianVariety/ActivityType/
 * Material - those have no CRUD screens at all yet either, seeded only;
 * this at least gets create so new technology types don't require a
 * developer to add a seeder row).
 */
class TechnologyController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Technology::class);

        $technologies = Technology::withCount('assets')->orderBy('name')->paginate(20);

        return view('technologies.index', compact('technologies'));
    }

    public function create()
    {
        $this->authorize('create', Technology::class);

        return view('technologies.create');
    }

    public function store(StoreTechnologyRequest $request)
    {
        $this->authorize('create', Technology::class);

        $technology = Technology::create($request->validated());

        return redirect()->route('technologies.show', $technology)->with('status', 'เพิ่มประเภทเทคโนโลยีเรียบร้อยแล้ว');
    }

    public function show(Technology $technology)
    {
        $this->authorize('view', $technology);

        $technology->load(['assets' => fn ($q) => $q->orderBy('asset_code')]);

        return view('technologies.show', compact('technology'));
    }
}
