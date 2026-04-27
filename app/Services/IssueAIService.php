<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generates a short summary and a suggested next action for an issue.
 *
 * Strategy (in order of preference):
 *   1. Claude API  – if ANTHROPIC_API_KEY is set and the request succeeds.
 *   2. Rules-based – deterministic fallback that works without any external service.
 */
class IssueAIService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const MODEL   = 'claude-3-5-haiku-20241022';

    // -------------------------------------------------------------------------
    // Public interface
    // -------------------------------------------------------------------------

    /**
     * @return array{summary: string, next_action: string, source: string}
     */
    public function generate(string $title, string $description, string $priority, string $category): array
    {
        $apiKey = config('services.anthropic.key');

        if ($apiKey) {
            $result = $this->generateWithClaude($apiKey, $title, $description, $priority, $category);
            if ($result) {
                return $result + ['source' => 'claude_api'];
            }
        }

        return $this->generateWithRules($title, $description, $priority, $category) + ['source' => 'rules_based'];
    }

    // -------------------------------------------------------------------------
    // Claude API path
    // -------------------------------------------------------------------------

    private function generateWithClaude(
        string $apiKey,
        string $title,
        string $description,
        string $priority,
        string $category
    ): ?array {
        $systemPrompt = <<<'PROMPT'
You are a concise support-operations assistant. Given an issue ticket, return ONLY a JSON object with two keys:
- "summary": A single sentence (max 25 words) capturing the core problem.
- "next_action": A single sentence describing the most important immediate action for the team.
Do not include markdown, code fences, or any extra text. Return only valid JSON.
PROMPT;

        $userMessage = sprintf(
            "Title: %s\nCategory: %s\nPriority: %s\nDescription: %s",
            $title,
            $category,
            $priority,
            $description
        );

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(15)->post(self::API_URL, [
                'model'      => self::MODEL,
                'max_tokens' => 256,
                'system'     => $systemPrompt,
                'messages'   => [
                    ['role' => 'user', 'content' => $userMessage],
                ],
            ]);

            if ($response->failed()) {
                Log::warning('Claude API returned error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            $content = $response->json('content.0.text', '');
            $parsed  = json_decode($content, true);

            if (
                is_array($parsed)
                && isset($parsed['summary'], $parsed['next_action'])
                && is_string($parsed['summary'])
                && is_string($parsed['next_action'])
            ) {
                return [
                    'summary'     => trim($parsed['summary']),
                    'next_action' => trim($parsed['next_action']),
                ];
            }

            Log::warning('Claude API returned unexpected JSON', ['content' => $content]);
            return null;

        } catch (\Throwable $e) {
            Log::warning('Claude API request failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // -------------------------------------------------------------------------
    // Rules-based fallback
    // -------------------------------------------------------------------------

    private function generateWithRules(
        string $title,
        string $description,
        string $priority,
        string $category
    ): array {
        return [
            'summary'     => $this->buildSummary($title, $priority, $category),
            'next_action' => $this->buildNextAction($priority, $category, $description),
        ];
    }

    private function buildSummary(string $title, string $priority, string $category): string
    {
        $categoryLabel = ucfirst($category);
        $priorityLabel = ucfirst($priority);

        // Truncate long titles for readability
        $shortTitle = strlen($title) > 80 ? substr($title, 0, 77) . '...' : $title;

        return "{$priorityLabel}-priority {$categoryLabel} issue: {$shortTitle}.";
    }

    private function buildNextAction(string $priority, string $category, string $description): string
    {
        // Security issues always get immediate escalation advice
        if ($category === 'security') {
            return 'Escalate immediately to the security team and open a war-room channel.';
        }

        // Critical priority
        if ($priority === 'critical') {
            return 'Page on-call engineer now and begin incident-response procedure.';
        }

        // High priority
        if ($priority === 'high') {
            return 'Assign to a senior engineer within the hour and add to the current sprint.';
        }

        // Category-specific guidance for lower priorities
        return match ($category) {
            'bug'            => 'Reproduce the issue locally, identify the root cause, and schedule a fix in the next sprint.',
            'feature'        => 'Add to the product backlog, estimate effort, and schedule for an upcoming sprint.',
            'infrastructure' => 'Review infrastructure metrics, identify affected services, and plan remediation.',
            default          => 'Review the issue details, assign an owner, and determine priority and timeline.',
        };
    }
}
