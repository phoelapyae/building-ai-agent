<?php

namespace App\Ai\Tools;

class ListFiles implements Tool
{
    public function definition(): array
    {
        return [
            'type' => 'function',
            'name' => 'list_files',
            'description' => 'List files in a directory, relative to the project root',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'path' => [
                        'type' => 'string',
                        'description' => 'The relative path to the directory.',
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
            return "Error: Directory not found: {$path}";
        }

        if (! is_dir($fullPath)) {
            return "Error: Path is not a directory: {$path}";
        }

        $entries = scandir($fullPath);

        if ($entries === false) {
            return "Error: Could not list directory: {$path}";
        }

        $entries = array_values(array_diff($entries, ['.', '..']));

        if ($entries === []) {
            return '(empty directory)';
        }

        foreach ($entries as &$entry) {
            if (is_dir($fullPath.DIRECTORY_SEPARATOR.$entry)) {
                $entry .= '/';
            }
        }

        return implode("\n", $entries);
    }
}
