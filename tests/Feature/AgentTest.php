<?php

use App\Ai\Agents\Agent;

test('it prepares the agent instructions', function () {
    $agent = new Agent;
    $instructions = $agent->instructions();

    expect($instructions)->toContain('You are a helpful AI assistant.');
});

test('it includes project guidelines and long-term memory when files exist', function () {
    $agent = new Agent;
    $instructions = $agent->instructions();

    if (file_exists(base_path('LARY.md'))) {
        expect($instructions)->toContain('Project Guidelines:');
    }

    if (file_exists(storage_path('LARY_MEMORY.md'))) {
        expect($instructions)->toContain('Long-term Memory:');
    }
});
