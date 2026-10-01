<?php

namespace App\Ai\Tools;

class SearchFile implements Tool
{
    public function definition(): array
    {
        return [
            'type' => 'function',
            'name' => 'search_file',
            'description' => 'Search for a file by name, relative to the project root',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'name' => [
                        'type' => 'string',
                        'description' => 'The name of the file to search for.',
                    ],
                ],
                'required' => ['name'],
                'additionalProperties' => false,
            ],
            'strict' => true,
        ];
    }

    public function use(array $arguments): string
    {
        $name = $arguments['name'] ?? '';

        if ($name === '') {
            return 'Error: No file name provided.';
        }

        $root = base_path();
        $matches = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            if (fnmatch($name, $file->getFilename()) || str_contains($file->getFilename(), $name)) {
                $matches[] = ltrim(str_replace($root, '', $file->getPathname()), DIRECTORY_SEPARATOR);

                if (count($matches) >= 50) {
                    break;
                }
            }
        }

        if ($matches === []) {
            return "(no files found matching: {$name})";
        }

        sort($matches);

        return implode("\n", $matches);
    }
}
