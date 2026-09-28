<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use function Laravel\Prompts\{text, spin};

#[Signature('dialog')]
#[Description('Retrieve a response from the OpenAI API')]
class DialogCommand extends Command
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

            $response = spin(
                fn() => $this->runModel(),
                message: 'Retrieving response from OpenAI...'
            );

            $this->history = [...$this->history, ...$response['output']];

            dump($this->history);

            $this->info($response['output'][0]['content'][0]['text'] ?? 'No response from OpenAI.');
        }
    }

    private function runModel(): array
    {
        return Http::withToken(config('services.openai.api_key'))
            ->post('https://api.openai.com/v1/responses', [
                'model' => 'gpt-5.4-nano',
                'instructions' => 'You are a helpful assistant.',
                'input' => $this->history,
            ])
            ->throw()
            ->json();
    }
}
