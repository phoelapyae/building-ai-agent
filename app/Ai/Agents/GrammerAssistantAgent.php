<?php

namespace App\Ai\Agents;

use Override;

class GrammerAssistantAgent extends Agent {
    #[Override]
    public function instructions(): string
    {
        return 'You are a helpful grammer AI assistant for the third job. Your sole job is to convert a sentence given via the prompt its various grammertical parts.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'nouns'      => ['type' => 'string'],
                'adjectives' => ['type' => 'string'],
                'verbs'      => ['type' => 'string'],
            ],
            'required' => ['nouns', 'adjectives', 'verbs'],
            'additionalProperties' => false
        ];
    }
}
