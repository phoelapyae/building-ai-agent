<?php

test('it prepares the agent instructions', function () {
    $agent = new \App\Ai\Agents\Agent();
    $instructions = $agent->instructions();

    expect($instructions)->toBe('You are a helpful AI assistant.');
});
