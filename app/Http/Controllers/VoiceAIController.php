<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Student;
use App\Services\AI\VoiceAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VoiceAIController extends Controller
{
    public function __construct(
        protected VoiceAIService $voiceAIService
    ) {
    }

    public function command(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'command' => [
                'required',
                'string',
                'max:500',
            ],
        ]);

        $command = $validated['command'];

        $interpretation = $this->voiceAIService
            ->understandCommand($command);

        if ($interpretation['intent'] === 'school_summary') {
            return response()->json([
                'success' => true,
                'intent' => 'school_summary',
                'message' => $this->schoolSummary(),
            ]);
        }

        return response()->json([
            'success' => true,
            'intent' => $interpretation['intent'],
            'message' => $interpretation['message']
                ?? 'That command is not available yet.',
        ]);
    }

    protected function schoolSummary(): string
    {
        try {
            $studentCount = class_exists(Student::class)
                ? Student::query()->count()
                : null;

            $staffCount = class_exists(Staff::class)
                ? Staff::query()->count()
                : null;

            $date = now()->format('l, d F Y');

            $summary = "Here is your EDUNEXUS school summary for {$date}. ";

            if ($studentCount !== null) {
                $summary .= "The school has {$studentCount} registered students. ";
            }

            if ($staffCount !== null) {
                $summary .= "There are {$staffCount} staff records. ";
            }

            $summary .=
                'Attendance, fees, and payment summaries will be added in the next step.';

            return $summary;
        } catch (\Throwable $exception) {
            Log::error('Voice AI school summary failed', [
                'message' => $exception->getMessage(),
            ]);

            return 'I could not retrieve the school summary at the moment.';
        }
    }
}