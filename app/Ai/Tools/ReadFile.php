<?php

namespace App\Ai\Tools;

class ReadFile implements Tool
{
    public function definition(): array
    {
        return [
            'type' => 'function',
            'name' => 'read_file',
            'description' => 'Read the contents of a file, relative to the project root',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'path' => [
                        'type' => 'string',
                        'description' => 'The relative path to the file.',
                    ],
                ],
                'required' => ['path'],
                'additionalProperties' => false,
            ],
            'strict' => true,
        ];
    }

    public function use(array $arguments): string
    {
        $path = $arguments['path'] ?? '';

        if ($path === '') {
            return 'Error: No path provided.';
        }

        $fullPath = base_path($path);

        if (! file_exists($fullPath)) {
            return "Error: File not found: {$path}";
        }

        if (is_dir($fullPath)) {
            return "Error: Path is a directory, not a file: {$path}";
        }

        if (! is_readable($fullPath)) {
            return "Error: File is not readable: {$path}";
        }

        $contents = file_get_contents($fullPath);

        if ($contents === false) {
            return "Error: Could not read file: {$path}";
        }

        return $contents === '' ? '(empty file)' : $contents;
    }
}
