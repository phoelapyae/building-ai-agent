<?php

namespace App\Ai\Agents;

use App\Ai\Attributes\CompactAfter;
use App\Ai\Tools\Tool;
use Illuminate\Support\Facades\Http;
use ReflectionClass;

class Agent
{
    protected array $history = [];

    public function instructions(): string
    {
        $instructions = $this->persona();
        if (file_exists(base_path('LARY.md'))) {
            $instructions .= "\n\n Project Guidelines:\n".file_get_contents(base_path('LARY.md'));
        }
        $memoryFile = storage_path('LARY_MEMORY.md');
        if (file_exists($memoryFile) && trim((string) file_get_contents($memoryFile)) !== '') {
            $instructions .= "\n\n Long-term Memory:\n".file_get_contents($memoryFile);
        }

        return $instructions;
    }

    public function persona(): string
    {
        $default = 'You are a helpful AI assistant.';

        return $default;
    }

    public function prompt(string $prompt)
    {
        $this->maybeCompact();

        $this->history[] = [
            'role' => 'user',
            'content' => $prompt,
        ];

        while (true) {
            $response = $this->runModel();

            $this->history = [...$this->history, ...$response['output']];

            $toolCalls = collect($response['output'])->filter(
                fn ($item) => $item['type'] === 'function_call'
            );

            if ($toolCalls->isEmpty()) {
                return $response['output'][0]['content'][0]['text'];
            }

            $toolCalls->each(function ($call) {
                $this->runTool($call);
            });
        }
    }

    private function runTool(array $call = [])
    {
        foreach ($this->tools() as $tool) {
            if ($tool->definition()['name'] === $call['name']) {
                $arguments = json_decode($call['arguments'], associative: true);

                $result = $tool->use($arguments);

                $this->history[] = [
                    'type' => 'function_call_output',
                    'call_id' => $call['call_id'],
                    'output' => (string) $result,
                ];
            }
        }
    }

    public function runModel(): mixed
    {
        return Http::withToken(config('services.openai.api_key'))
            ->post('https://api.openai.com/v1/responses', [
                'model' => 'gpt-5.4-nano',
                'instructions' => $this->instructions(),
                'input' => $this->history,
                'tools' => array_map(fn (Tool $tool) => $tool->definition(), $this->tools()),
                'text' => $this->schema() ? [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'assistant_response',
                        'strict' => true,
                        'schema' => $this->schema(),
                    ],
                ] : null,
            ])
            ->throw()
            ->json();
    }

    protected function maybeCompact()
    {
        if ($this->shouldCompact()) {
            // we need to compact the history to avoid hitting the token limit
            $this->compact($this->history);
        }
    }

    protected function shouldCompact(): bool
    {
        $attributes = (new ReflectionClass($this))->getAttributes(CompactAfter::class);

        $config = $attributes ? $attributes[0]->newInstance() : new CompactAfter(threshold: 3);

        return count($this->history) > $config->threshold;
    }

    protected function compact(array $history): void
    {
        $response = Http::withToken(config('services.openai.api_key'))
            ->post('https://api.openai.com/v1/responses', [
                'model' => 'gpt-5.4-nano',
                'instructions' => 'You are a helpful AI assistant. You are given a conversation history between a user and an AI assistant. Your task is to summarize the conversation into a single message that captures the essence of the conversation. The summary should be concise and should not include any personal information or sensitive data.',
                'input' => $history,
            ])
            ->throw()
            ->json();

        $summary = $response['output'][0]['content'][0]['text'];

        $this->history = [
            [
                'role' => 'user',
                'content' => '[Earlier conversation summary]: '.$summary,
            ],
        ];
        // dump('Compacted conversation history to avoid hitting token limit. New history: ', $this->history);
    }

    public function tools(): array
    {
        return [];
    }

    public function schema(): array
    {
        return [];
    }

    protected function messages(): array
    {
        return $this->history;
    }
}
