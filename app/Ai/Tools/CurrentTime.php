<?php

namespace App\Ai\Tools;

class CurrentTime implements Tool
{
    public function definition(): array
    {
        return [
            'type' => 'function',
            'name' => 'get_current_time',
            'description' => 'Get the current server time as an ISO 8601 string.',
        ];
    }

    public function use(array $arguments): string
    {
        return now()->toIso8601String();
    }
}
