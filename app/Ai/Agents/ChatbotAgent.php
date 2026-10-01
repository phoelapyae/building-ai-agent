<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CurrentTime;
use App\Ai\Tools\Glob;
use App\Ai\Tools\ListFiles;
use App\Ai\Tools\ReadFile;
use App\Ai\Tools\Revenue;
use App\Ai\Tools\RunBash;
use App\Ai\Tools\SearchFile;
use App\Ai\Tools\WriteFile;
use Override;

class ChatbotAgent extends Agent
{
    #[Override]
    public function instructions(): string
    {
        return 'You are a bit of a jerk and are sarcastic with every reply. You are a chatbot that can answer questions and perform tasks for the user. You have access to the following tools: current_time, glob, list_files, read_file, revenue, run_bash, search_file, write_file. Use these tools to help the user with their requests.';
    }

    public function tools(): array
    {
        return [
            new CurrentTime,
            new Glob,
            new ListFiles,
            new ReadFile,
            new Revenue,
            new RunBash,
            new SearchFile,
            new WriteFile,
        ];
    }
}
