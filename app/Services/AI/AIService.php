<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AIService
{
    /**
     * Generate a structured lesson note using OpenAI.
     */
    public function generateLessonNote(array $data): array
    {
        $apiKey = config('services.openai.api_key');
        $model = config('services.openai.model', 'gpt-4o-mini');
        $timeout = (int) config('services.openai.timeout', 60);

        if (empty($apiKey)) {
            throw new RuntimeException(
                'OpenAI API key is missing. Please configure OPENAI_API_KEY in your .env file.'
            );
        }

        $systemPrompt = <<<'PROMPT'
You are an experienced professional basic-school lesson-note writer.

Create clear, practical, curriculum-friendly lesson notes for schools in Ghana.

The lesson note must be suitable for the selected class level and subject. Use simple but professional language. Make the lesson practical, learner-centred, and suitable for classroom delivery.

Return ONLY valid JSON using exactly these fields:

{
  "description": "Short lesson description",
  "content": "Detailed lesson content with clear headings and explanations",
  "learning_objectives": [
    "Objective 1",
    "Objective 2",
    "Objective 3"
  ],
  "learning_outcomes": [
    "Outcome 1",
    "Outcome 2",
    "Outcome 3"
  ],
  "teaching_aids": [
    "Teaching aid 1",
    "Teaching aid 2"
  ],
  "assessment_methods": [
    "Assessment method 1",
    "Assessment method 2"
  ],
  "homework": "Homework or follow-up activity",
  "remarks": "Teacher remarks",
  "recommendations": "Recommendations for effective delivery",
  "challenges": "Possible classroom challenges and how to address them",
  "delivery_method": "Discussion, demonstration, group work, practical activity, or another suitable method",
  "resources": [
    "Suggested resource 1",
    "Suggested resource 2"
  ]
}

Do not include Markdown code fences.
Do not include any text before or after the JSON.
PROMPT;

        $userPrompt = $this->buildUserPrompt($data);

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout($timeout)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'temperature' => 0.7,
                'response_format' => [
                    'type' => 'json_object',
                ],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $userPrompt,
                    ],
                ],
            ]);

        if ($response->failed()) {
            $message = $response->json('error.message');

            throw new RuntimeException(
                $message ?: 'OpenAI request failed. Please check your API key, billing, and configuration.'
            );
        }

        $content = $response->json('choices.0.message.content');

        if (empty($content)) {
            throw new RuntimeException(
                'OpenAI returned an empty response.'
            );
        }

        $content = $this->cleanJsonResponse($content);

        $result = json_decode($content, true);

        if (!is_array($result)) {
            throw new RuntimeException(
                'OpenAI returned an invalid lesson-note response.'
            );
        }

        return $this->normalizeLessonNote($result);
    }

    /**
     * Build the prompt sent to OpenAI.
     */
    protected function buildUserPrompt(array $data): string
    {
        $topic = $data['topic'] ?? '';
        $subject = $data['subject'] ?? '';
        $studentClass = $data['student_class'] ?? '';
        $academicYear = $data['academic_year'] ?? '';
        $term = $data['term'] ?? '';
        $duration = $data['duration'] ?? '';
        $language = $data['language'] ?? 'English';
        $teachingStyle = $data['teaching_style'] ?? 'Learner-centred';
        $extraInstructions = $data['extra_instructions'] ?? '';

        return <<<PROMPT
Generate a complete lesson note using the following information:

Topic:
{$topic}

Subject:
{$subject}

Class:
{$studentClass}

Academic Year:
{$academicYear}

Term:
{$term}

Lesson Duration:
{$duration}

Language:
{$language}

Teaching Style:
{$teachingStyle}

Additional Instructions:
{$extraInstructions}

Make the lesson note appropriate for the selected class in Ghana. Include practical examples, suitable classroom activities, assessment, and homework where appropriate.
PROMPT;
    }

    /**
     * Remove accidental Markdown code fences from the response.
     */
    protected function cleanJsonResponse(string $content): string
    {
        $content = trim($content);

        $content = preg_replace('/^```json\s*/i', '', $content);
        $content = preg_replace('/^```\s*/', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);

        return trim($content);
    }

    /**
     * Ensure all expected fields exist and have the correct format.
     */
    protected function normalizeLessonNote(array $result): array
    {
        $arrayFields = [
            'learning_objectives',
            'learning_outcomes',
            'teaching_aids',
            'assessment_methods',
            'resources',
        ];

        $stringFields = [
            'description',
            'content',
            'homework',
            'remarks',
            'recommendations',
            'challenges',
            'delivery_method',
        ];

        foreach ($arrayFields as $field) {
            if (!isset($result[$field])) {
                $result[$field] = [];
            }

            if (!is_array($result[$field])) {
                $result[$field] = [$result[$field]];
            }

            $result[$field] = array_values(
                array_filter(
                    array_map(
                        fn ($item) => is_string($item)
                            ? trim($item)
                            : (string) $item,
                        $result[$field]
                    )
                )
            );
        }

        foreach ($stringFields as $field) {
            if (!isset($result[$field])) {
                $result[$field] = '';
            }

            if (is_array($result[$field])) {
                $result[$field] = implode("\n", $result[$field]);
            }

            $result[$field] = (string) $result[$field];
        }

        return $result;
    }
}