<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Term;
use App\Services\AI\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AIController extends Controller
{
    public function __construct(
        protected AIService $aiService
    ) {
    }

    /**
     * Display the AI lesson-note generator.
     */
    public function create()
    {
        $staff = auth()->user();

        $academicYears = AcademicYear::query()
            ->orderByDesc('id')
            ->get();

        $terms = Term::query()
            ->orderBy('id')
            ->get();

        $studentClasses = StudentClass::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $subjects = Subject::query()
            ->orderBy('name')
            ->get();

        return view('ai.lesson-note', compact(
            'academicYears',
            'terms',
            'studentClasses',
            'subjects'
        ));
    }

    /**
     * Generate a lesson note through OpenAI.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'topic' => [
                'required',
                'string',
                'max:255',
            ],

            'subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
            ],

            'student_class_id' => [
                'required',
                'integer',
                'exists:student_classes,id',
            ],

            'academic_year_id' => [
                'nullable',
                'integer',
                'exists:academic_years,id',
            ],

            'term_id' => [
                'nullable',
                'integer',
                'exists:terms,id',
            ],

            'duration' => [
                'nullable',
                'string',
                'max:100',
            ],

            'language' => [
                'nullable',
                'string',
                'max:100',
            ],

            'teaching_style' => [
                'nullable',
                'string',
                'max:100',
            ],

            'extra_instructions' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $subject = Subject::findOrFail($validated['subject_id']);
        $studentClass = StudentClass::findOrFail($validated['student_class_id']);

        $academicYear = null;
        if (!empty($validated['academic_year_id'])) {
            $academicYear = AcademicYear::find($validated['academic_year_id']);
        }

        $term = null;
        if (!empty($validated['term_id'])) {
            $term = Term::find($validated['term_id']);
        }

        $aiInput = [
            'topic' => $validated['topic'],
            'subject' => $subject->name,
            'student_class' => $studentClass->name,
            'academic_year' => $academicYear?->name ?? '',
            'term' => $term?->name ?? '',
            'duration' => $validated['duration'] ?? '40 minutes',
            'language' => $validated['language'] ?? 'English',
            'teaching_style' => $validated['teaching_style'] ?? 'Learner-centred',
            'extra_instructions' => $validated['extra_instructions'] ?? '',
        ];

        try {
            $generatedLessonNote = $this->aiService
                ->generateLessonNote($aiInput);

            return view('ai.lesson-note', [
                'academicYears' => AcademicYear::query()
                    ->orderByDesc('id')
                    ->get(),

                'terms' => Term::query()
                    ->orderBy('id')
                    ->get(),

                'studentClasses' => StudentClass::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),

                'subjects' => Subject::query()
                    ->orderBy('name')
                    ->get(),

                'generatedLessonNote' => $generatedLessonNote,
                'submittedData' => $validated,
            ]);
        } catch (Throwable $exception) {
            Log::error('EDUNEXUS AI lesson-note generation failed', [
                'message' => $exception->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }
    }

    /**
     * Send generated lesson-note content to the existing create form.
     */
    public function useGenerated(Request $request)
    {
        $validated = $request->validate([
            'generated_json' => [
                'required',
                'string',
            ],

            'topic' => [
                'required',
                'string',
                'max:255',
            ],

            'subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
            ],

            'student_class_id' => [
                'required',
                'integer',
                'exists:student_classes,id',
            ],

            'academic_year_id' => [
                'nullable',
                'integer',
                'exists:academic_years,id',
            ],

            'term_id' => [
                'nullable',
                'integer',
                'exists:terms,id',
            ],
        ]);

        $generated = json_decode(
            $validated['generated_json'],
            true
        );

        if (!is_array($generated)) {
            return back()
                ->with('error', 'The generated lesson note is invalid.');
        }

        return redirect()
            ->route('lesson-notes.create')
            ->withInput([
                'topic' => $validated['topic'],
                'subject_id' => $validated['subject_id'],
                'student_class_id' => $validated['student_class_id'],
                'academic_year_id' => $validated['academic_year_id'] ?? null,
                'term_id' => $validated['term_id'] ?? null,

                'description' => $generated['description'] ?? '',
                'content' => $generated['content'] ?? '',
                'learning_objectives' => $generated['learning_objectives'] ?? [],
                'learning_outcomes' => $generated['learning_outcomes'] ?? [],
                'teaching_aids' => $generated['teaching_aids'] ?? [],
                'assessment_methods' => $generated['assessment_methods'] ?? [],
                'homework' => $generated['homework'] ?? '',
                'remarks' => $generated['remarks'] ?? '',
                'recommendations' => $generated['recommendations'] ?? '',
                'challenges' => $generated['challenges'] ?? '',
                'delivery_method' => $generated['delivery_method'] ?? '',
                'resources' => $generated['resources'] ?? [],
            ])
            ->with('ai_notice', 'AI-generated lesson note loaded. Review it before saving.');
    }
}