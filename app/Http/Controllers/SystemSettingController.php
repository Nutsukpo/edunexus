<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\SystemSetting;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::instance();

        $academicYears = AcademicYear::query()
            ->where('is_active', true)
            ->orderByDesc('start_date')
            ->get();

        $viewingTerms = collect();
        $operationalTerms = collect();

        if ($settings->current_academic_year_id) {
            $viewingTerms = Term::query()
                ->where('academic_year_id', $settings->current_academic_year_id)
                ->where('is_active', true)
                ->orderBy('start_date')
                ->orderBy('id')
                ->get();
        }

        if ($settings->operational_academic_year_id) {
            $operationalTerms = Term::query()
                ->where('academic_year_id', $settings->operational_academic_year_id)
                ->where('is_active', true)
                ->orderBy('start_date')
                ->orderBy('id')
                ->get();
        }

        return view('settings.index', [
            'settings' => $settings,
            'academicYears' => $academicYears,
            'viewingTerms' => $viewingTerms,
            'operationalTerms' => $operationalTerms,
            'viewingAcademicYear' => $settings->currentAcademicYear,
            'viewingTerm' => $settings->currentTerm,
            'operationalAcademicYear' => $settings->operationalAcademicYear,
            'operationalTerm' => $settings->operationalTerm,
        ]);
    }

    public function terms(AcademicYear $academicYear)
    {
        return response()->json(
            Term::query()
                ->where('academic_year_id', $academicYear->id)
                ->where('is_active', true)
                ->orderBy('start_date')
                ->orderBy('id')
                ->get([
                    'id',
                    'academic_year_id',
                    'name',
                    'start_date',
                    'end_date',
                    'is_active',
                ])
        );
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'current_academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'current_term_id' => ['required', 'integer', 'exists:terms,id'],
            'auto_assign_current_period' => ['nullable', 'boolean'],
            'use_current_period_defaults' => ['nullable', 'boolean'],
        ]);

        $academicYear = AcademicYear::query()
            ->whereKey($validated['current_academic_year_id'])
            ->where('is_active', true)
            ->first();

        if (!$academicYear) {
            throw ValidationException::withMessages([
                'current_academic_year_id' => 'The selected academic year is not active.',
            ]);
        }

        $term = Term::query()
            ->whereKey($validated['current_term_id'])
            ->where('academic_year_id', $academicYear->id)
            ->where('is_active', true)
            ->first();

        if (!$term) {
            throw ValidationException::withMessages([
                'current_term_id' => 'The selected term does not belong to the selected academic year or is not active.',
            ]);
        }

        try {
            SystemSetting::instance()->update([
                'current_academic_year_id' => $academicYear->id,
                'current_term_id' => $term->id,
                'auto_assign_current_period' => (bool) ($validated['auto_assign_current_period'] ?? false),
                'use_current_period_defaults' => (bool) ($validated['use_current_period_defaults'] ?? false),
            ]);

            return redirect()
                ->route('settings.index')
                ->with('success', "Viewing/default period changed to {$academicYear->name} — {$term->name}. Historical data was not modified.");
        } catch (\Throwable $e) {
            Log::error('Viewing/default academic period update failed', [
                'message' => $e->getMessage(),
                'academic_year_id' => $academicYear->id,
                'term_id' => $term->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Unable to update the viewing/default period.');
        }
    }

    public function updateOperationalPeriod(Request $request)
    {
        $validated = $request->validate([
            'operational_academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'operational_term_id' => ['required', 'integer', 'exists:terms,id'],
        ]);

        $academicYear = AcademicYear::query()
            ->whereKey($validated['operational_academic_year_id'])
            ->where('is_active', true)
            ->first();

        if (!$academicYear) {
            throw ValidationException::withMessages([
                'operational_academic_year_id' => 'The selected operational academic year is not active.',
            ]);
        }

        $term = Term::query()
            ->whereKey($validated['operational_term_id'])
            ->where('academic_year_id', $academicYear->id)
            ->where('is_active', true)
            ->first();

        if (!$term) {
            throw ValidationException::withMessages([
                'operational_term_id' => 'The selected operational term does not belong to the selected academic year or is not active.',
            ]);
        }

        $settings = SystemSetting::instance();

        if (
            (int) $settings->operational_academic_year_id === (int) $academicYear->id &&
            (int) $settings->operational_term_id === (int) $term->id
        ) {
            return redirect()
                ->route('settings.index')
                ->with('success', "The operational period is already {$academicYear->name} — {$term->name}.");
        }

        try {
            $settings->update([
                'operational_academic_year_id' => $academicYear->id,
                'operational_term_id' => $term->id,
            ]);

            return redirect()
                ->route('settings.index')
                ->with('success', "Operational school period changed to {$academicYear->name} — {$term->name}. Student assignments were not activated automatically. Use Open Academic Period to activate prepared assignments.");
        } catch (\Throwable $e) {
            Log::error('Operational academic period update failed', [
                'message' => $e->getMessage(),
                'new_academic_year_id' => $academicYear->id,
                'new_term_id' => $term->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Unable to change the operational period. No student assignments were changed.');
        }
    }

    public function current()
    {
        $settings = SystemSetting::instance();

        return response()->json([
            'viewing' => [
                'academic_year_id' => $settings->current_academic_year_id,
                'term_id' => $settings->current_term_id,
                'academic_year' => $settings->currentAcademicYear?->name,
                'term' => $settings->currentTerm?->name,
            ],
            'operational' => [
                'academic_year_id' => $settings->operational_academic_year_id,
                'term_id' => $settings->operational_term_id,
                'academic_year' => $settings->operationalAcademicYear?->name,
                'term' => $settings->operationalTerm?->name,
            ],
        ]);
    }
}
