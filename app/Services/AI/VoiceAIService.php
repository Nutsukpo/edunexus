<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VoiceAIService
{
    public function understandCommand(string $command): array
    {
        $command = trim($command);

        if ($command === '') {
            return [
                'intent' => 'unknown',
                'message' => 'Please provide a command.',
            ];
        }

        $lowerCommand = strtolower($command);

        /*
        |--------------------------------------------------------------------------
        | Safe local command detection
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($lowerCommand, 'school summary') ||
            str_contains($lowerCommand, 'school report') ||
            str_contains($lowerCommand, 'today summary') ||
            str_contains($lowerCommand, "today's summary")
        ) {
            return [
                'intent' => 'school_summary',
                'message' => null,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Ask OpenAI to classify other commands
        |--------------------------------------------------------------------------
        */

        try {
            $response = Http::withToken(config('services.openai.key'))
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'temperature' => 0,
                    'response_format' => [
                        'type' => 'json_object',
                    ],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => <<<'PROMPT'
You are the command interpreter for EDUNEXUS School Management System.

Return valid JSON only using this format:

{
  "intent": "unknown",
  "message": "A short explanation"
}

Currently supported intent:
- school_summary

If the user requests anything else, return:
{
  "intent": "unknown",
  "message": "That command is not available yet."
}

Do not execute actions.
Do not create, update, delete, approve, or pay anything.
PROMPT,
                        ],
                        [
                            'role' => 'user',
                            'content' => $command,
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('Voice AI classification failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'intent' => 'unknown',
                    'message' => 'I could not understand that command right now.',
                ];
            }

            $content = $response->json('choices.0.message.content');

            $result = json_decode($content, true);

            if (!is_array($result)) {
                return [
                    'intent' => 'unknown',
                    'message' => 'I could not understand that command.',
                ];
            }

            return [
                'intent' => $result['intent'] ?? 'unknown',
                'message' => $result['message'] ?? 'That command is not available yet.',
            ];
        } catch (\Throwable $exception) {
            Log::error('Voice AI exception', [
                'message' => $exception->getMessage(),
            ]);

            return [
                'intent' => 'unknown',
                'message' => 'Voice AI is temporarily unavailable.',
            ];
        }
    }
}