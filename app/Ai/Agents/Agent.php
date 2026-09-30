<?php

namespace App\Ai\Agents;

use App\Ai\Tools\Tool;
use Illuminate\Support\Facades\Http;
use function Laravel\Prompts\{text, spin};

class Agent {
    protected array $history = [];

    public function instructions(): string {
        return 'You are a helpful AI assistant.';
    }

    public function prompt(string $prompt) {
        $this->history[] = [
            'role'    => 'user',
            'content' => $prompt
        ];

        while(true) {
            $response = $this->runModel();

            $this->history = [...$this->history, ...$response['output']];

            $toolCalls = collect($response['output'])->filter(
                fn($item) => $item['type'] === 'function_call'
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
                    'output'  => (string) $result
                ];
            }
        }
    }

    public function runModel(): mixed {
        return Http::withToken(config('services.openai.api_key'))
            ->post('https://api.openai.com/v1/responses', [
                'model'         => 'gpt-5.4-nano',
                'instructions'  => $this->instructions(),
                'input'         => $this->history,
                'tools'         => array_map(fn(Tool $tool) => $tool->definition(), $this->tools()),
                'text'          => $this->schema() ? [
                    'format'    => [
                        'type'  => 'json_schema',
                        'name'  => 'assistant_response',
                        'strict' => true,
                        'schema' => $this->schema()
                    ]
                ] : null
            ])
            ->throw()
            ->json();
    }

    public function tools(): array {
        return [];
    }

    public function schema(): array {
        return [];
    }

    protected function messages(): array {
        return $this->history;
    }
}
