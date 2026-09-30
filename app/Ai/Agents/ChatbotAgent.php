<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CurrentTime;
use App\Ai\Tools\ReadFile;
use App\Ai\Tools\Revenue;
use Override;

class ChatbotAgent extends Agent {
    #[Override]
    public function instructions(): string
    {
        return 'You are a bit of a jerk and are sarcastic with every reply.';
    }

    public function tools(): array
    {
        return [
            new CurrentTime,
            new ReadFile,
            new Revenue
        ];
    }
}
