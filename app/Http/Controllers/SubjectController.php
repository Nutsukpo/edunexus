<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects.
     *
     * Super Admin and unscoped users see all subjects.
     * Scoped Teaching Staff see only subjects assigned to them
     * through the class_subject_staff table.
     */
    public function index()
    {
        $user = auth()->user();

        $query = Subject::with('staff')
            ->orderBy('name');

        if ($this->isScopedStaff($user)) {
            $staff = $this->staffForUser($user);

            abort_if(!$staff, 403);

            $query->whereIn('id', $this->assignedSubjectIds($staff->id));
        }

        $subjects = $query->get();

        return view('subjects.index', compact('subjects'));
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create()
    {
        $this->ensureCanManageSubjects();

        $staffs = Staff::orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('subjects.create', compact('staffs'));
    }

    /**
     * Store a newly created subject.
     */
    public function store(Request $request)
    {
        $this->ensureCanManageSubjects();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:subjects,code',
            'description' => 'nullable|string',
            'education_level' => 'required|in:Early Childhood,Primary,JHS,SHS',
            'category' => 'required|in:Core,Elective,Vocational,Technical',
            'staff_id' => 'nullable|exists:staff,id',
            'is_active' => 'nullable|boolean',
        ]);

        Subject::create([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'description' => $validated['description'] ?? null,
            'education_level' => $validated['education_level'],
            'category' => $validated['category'],
            'staff_id' => $validated['staff_id'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    /**
     * Display the specified subject.
     */
    public function show(Subject $subject)
    {
        $this->ensureSubjectIsVisible($subject);

        $subject->load('staff');

        return view('subjects.show', compact('subject'));
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(Subject $subject)
    {
        $this->ensureCanManageSubjects();
        $this->ensureSubjectIsVisible($subject);

        $staffs = Staff::orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('subjects.edit', compact('subject', 'staffs'));
    }

    /**
     * Update the specified subject.
     */
    public function update(Request $request, Subject $subject)
    {
        $this->ensureCanManageSubjects();
        $this->ensureSubjectIsVisible($subject);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:subjects,code,' . $subject->id,
            'description' => 'nullable|string',
            'education_level' => 'required|in:Early Childhood,Primary,JHS,SHS',
            'category' => 'required|in:Core,Elective,Vocational,Technical',
            'staff_id' => 'nullable|exists:staff,id',
            'is_active' => 'nullable|boolean',
        ]);

        $subject->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'description' => $validated['description'] ?? null,
            'education_level' => $validated['education_level'],
            'category' => $validated['category'],
            'staff_id' => $validated['staff_id'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    /**
     * Remove the specified subject.
     */
    public function destroy(Subject $subject)
    {
        $this->ensureCanManageSubjects();
        $this->ensureSubjectIsVisible($subject);

        $subject->delete();

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }

    /**
     * Determine whether the logged-in user is scoped Teaching Staff.
     */
    private function isScopedStaff($user): bool
    {
        return app(\App\Services\EdunexusAuthorizationService::class)
            ->isScopedStaff($user);
    }

    /**
     * Resolve the Staff record connected to the logged-in user.
     */
    private function staffForUser($user): ?Staff
    {
        return app(\App\Services\EdunexusAuthorizationService::class)
            ->staffFor($user);
    }

    /**
     * Return subject IDs assigned to a Staff member.
     *
     * The class_subject_staff table is the source of truth for
     * subject-teacher assignments.
     */
    private function assignedSubjectIds(int $staffId)
    {
        return DB::table('class_subject_staff')
            ->where('staff_id', $staffId)
            ->whereNotNull('subject_id')
            ->distinct()
            ->pluck('subject_id');
    }

    /**
     * Ensure the subject is visible to the current user.
     */
    private function ensureSubjectIsVisible(Subject $subject): void
    {
        $user = auth()->user();

        if (!$this->isScopedStaff($user)) {
            return;
        }

        $staff = $this->staffForUser($user);

        abort_if(!$staff, 403);

        abort_unless(
            DB::table('class_subject_staff')
                ->where('staff_id', $staff->id)
                ->where('subject_id', $subject->id)
                ->exists(),
            403
        );
    }

    /**
     * Keep subject-management permissions authoritative.
     */
    private function ensureCanManageSubjects(): void
    {
        $user = auth()->user();

        abort_unless($user, 403);

        abort_unless(
            $user->can('subjects.create') ||
            $user->can('subjects.edit') ||
            $user->can('subjects.manage') ||
            !$this->isScopedStaff($user),
            403
        );
    }
}
