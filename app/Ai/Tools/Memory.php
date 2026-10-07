<?php

namespace App\Ai\Tools;

use App\Ai\Memory\Store;
use Illuminate\Support\Facades\Http;

class Memory implements Tool
{
    public function __construct(protected Store $store = new Store) {}

    public function definition(): array
    {
        return [
            'type' => 'function',
            'name' => 'remember',
            'description' => <<<'DESC'
                Save a stable, long-term fact about the user or their project.
                Before remembering a fact, check whether it is already covered by the contents of the user project guidelines
                or existing memories. If it's already known, do not duplicate it.
                Use the tool only for for information that will remain useful for the future sessions. Good examples
                - Stated preferences ("perfers Pest over PHPUnit", "Use tabs, not spaces")
                - Persistent project context("works on a Laravel + Inertia codebase")
                - Identity("goes by Jeffery")

                Do NOT use this tool for:
                - Transitent state ("currently debugging a route issue")
                - Conversational pleasantries("say hello")
                - Sensitive Data (credentials, API keys, personal identifiers the user hasn't volunterred as context)

                Phrase the memory in a concise, third-person statement that will make sense in a future conversation without context.
                Perfer "Users perfers Pest over PHPUnit" over "User said they prefer Pest over PHPUnit".
            DESC,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'fact' => [
                        'type' => 'string',
                        'description' => 'The fact to remember. Should be a concise, third-person statement that will make sense in a future conversation without context.',
                    ],
                ],
                'required' => ['fact'],
                'additionalProperties' => false,
            ],
            'strict' => true,
        ];
    }

    public function use(array $arguments = []): string
    {
        $fact = trim($arguments['fact'] ?? '');

        if ($fact === '') {
            return 'No fact provided. Nothing remembered.';
        }

        $memory = $this->store->fetch();

        $updated = $this->refresh($memory, $fact);

        $this->store->update($updated);

        return 'Remembered: '.$fact;
    }

    private function refresh(?string $memory, string $fact): string
    {
        $response = Http::withToken(config('services.openai.api_key'))
            ->post('https://api.openai.com/v1/responses', [
                'model' => 'gpt-5.4-nano',
                'instructions' => <<<'INSTR'
                    You maintain a small markdown scratch document of stable, long-term facts about a user.

                    Given the existing markdown and a new candidate fact, return the full updated markdown document. Apply these rules:

                    - If the candidate is genuinely new information, add it.
                    - If the candidate is already represented (including rephrasings or subsets), return the existing markdown unchanged.
                    - If the candidate is a more specific or more current version of an existing fact, replace the old fact with the new one.
                    - If the candidate contradicts an existing fact, replace the old fact (the new fact is presumed more current).
                    - Do not editorialize. Do not add facts that weren't in the inputs. Do not remove facts unrelated to the candidate.
                    - Return only markdown. Do not wrap it in a code fence.
                INSTR,
                'input' => [
                    ['role' => 'user', 'content' => 'Existing memory: '.($memory ?? '(empty)')."\n\nCandidate fact: ".$fact],
                ],
            ])
            ->throw()
            ->json();

        $text = $response['output'][0]['content'][0]['text'] ?? $memory ?? '';

        return $this->stripCodeFence(trim((string) $text));
    }

    private function stripCodeFence(string $markdown): string
    {
        return (string) preg_replace('/^```(?:markdown)?\s*|\s*```$/', '', $markdown);
    }
}
