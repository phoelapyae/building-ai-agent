<?php

namespace App\Ai\Memory;

use Illuminate\Support\Facades\File;

class Store
{
    public function __construct(protected string $path = '') {}

    public function path(): string
    {
        return $this->path ?: storage_path('LARY_MEMORY.md');
    }

    public function fetch(): ?string
    {
        if (! File::exists($this->path())) {
            return null;
        }

        $contents = trim(File::get($this->path()));

        return $contents === '' ? null : $contents;
    }

    public function update(string $contents): void
    {
        File::ensureDirectoryExists(dirname($this->path()));

        File::put($this->path(), trim($contents)."\n");
    }

    public function forget(): void
    {
        if (File::exists($this->path())) {
            File::put($this->path(), '');
        }
    }
}
