<?php

namespace App\Ai\Tools;

class WriteFile implements Tool
{
    public function definition(): array
    {
        return [
            'type' => 'function',
            'name' => 'write_file',
            'description' => 'Write content to a file, relative to the project root',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'path' => [
                        'type' => 'string',
                        'description' => 'The relative path to the file.',
                    ],
                    'content' => [
                        'type' => 'string',
                        'description' => 'The content to write to the file.',
                    ],
                ],
                'required' => ['path', 'content'],
                'additionalProperties' => false,
            ],
            'strict' => true,
        ];
    }

    public function use(array $arguments): string
    {
        $path = $arguments['path'] ?? '';
        $content = $arguments['content'] ?? null;

        if ($path === '') {
            return 'Error: No path provided.';
        }

        if ($content === null) {
            return 'Error: No content provided.';
        }

        $fullPath = base_path($path);
        $directory = dirname($fullPath);

        if (! is_dir($directory)) {
            if (! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                return "Error: Could not create directory: {$directory}";
            }
        }

        $bytes = file_put_contents($fullPath, $content);

        if ($bytes === false) {
            return "Error: Could not write to file: {$path}";
        }

        return "Wrote {$bytes} bytes to {$path}";
    }
}
