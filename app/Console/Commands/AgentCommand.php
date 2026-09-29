<?php

namespace App\Console\Commands;

use App\Ai\Tools\CurrentTime;
use App\Ai\Tools\ReadFile;
use App\Ai\Tools\Revenue;
use App\Ai\Tools\Tool;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use function Laravel\Prompts\{text, spin, info};

#[Signature('agent')]
#[Description('Retrieve a response from the OpenAI API')]
class AgentCommand extends Command
{
    protected $history = [];
    /**
     * Execute the console command.
     */
    public function handle()
    {
        while (true) {
            $prompt = text(
                label: 'What is on your mind?',
                required: true,
            );

            if (in_array(strtolower(trim($prompt)), ['exit', 'quit'], true)) {
                return self::SUCCESS;
            }

            $this->history[] = [
                'role' => 'user',
                'content' => $prompt
            ];

            while(true){
                $response = spin(
                    fn() => $this->runModel(),
                    message: 'Retrieving response from OpenAI...'
                );

                $this->history = [...$this->history, ...$response['output']];

                $functionCalls = collect($response['output'])->filter(
                    fn($item) => $item['type'] === 'function_call'
                );

                if ($functionCalls->isEmpty()) {
                    // dump($response);

                    $this->info($response['output'][0]['content'][0]['text'] ?? 'No response from OpenAI.');

                    break;
                }

                $functionCalls->each(function ($call) {

                    info("Running Tool:" . $call['name'] . "(". json_encode($call['arguments']).")");

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
                });
            }
        }
    }

    private function runModel(): array
    {
        return Http::withToken(config('services.openai.api_key'))
            ->post('https://api.openai.com/v1/responses', [
                'model' => 'gpt-5.4-nano',
                'instructions' => 'You are a helpful assistant.',
                'input' => $this->history,
                'tools' => array_map(fn(Tool $tool) => $tool->definition(), $this->tools())
            ])
            ->throw()
            ->json();
    }

    private function tools(): array {
        return [
            new CurrentTime(),
            new ReadFile(),
            new Revenue()
        ];
    }
}
