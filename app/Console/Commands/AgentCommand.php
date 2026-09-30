<?php

namespace App\Console\Commands;

use App\Ai\Agents\ChatbotAgent;
use App\Ai\Agents\GrammerAssistantAgent;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
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
        // while (true) {
        //     $agent = new ChatbotAgent();

        //     $prompt = text(
        //         label: 'What is on your mind?',
        //         required: true,
        //     );

        //     $response = spin(
        //         fn() => $agent->prompt($prompt),
        //         'Hmm... thinking about that.'
        //     );

        //     info($response);
        // }

        $agent = new GrammerAssistantAgent();

        $response = $agent->prompt('The big brown dog jumped over the white moon and landed on a gigantic piece of cheese.');

        dump($response);
    }
}
