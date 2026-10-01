<?php

namespace App\Ai\Tools;

class RunBash implements Tool
{
    public function definition(): array
    {
        return [
            'type' => 'function',
            'name' => 'run_bash',
            'description' => 'Run a bash command',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'command' => [
                        'type' => 'string',
                        'description' => 'The bash command to run.',
                    ],
                ],
                'required' => ['command'],
                'additionalProperties' => false,
            ],
            'strict' => true,
        ];
    }

    public function use(array $arguments): string
    {
        $command = trim($arguments['command'] ?? '');

        if ($command === '') {
            return 'Error: No command provided.';
        }

        $output = shell_exec('cd '.escapeshellarg(base_path()).' && ('.$command.') 2>&1');

        if ($output === null) {
            return 'Error: Could not execute command (shell unavailable).';
        }

        return $output === '' ? '(command produced no output)' : $output;
    }
}
