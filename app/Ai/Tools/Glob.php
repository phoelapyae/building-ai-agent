<?php

namespace App\Ai\Tools;

class Glob implements Tool
{
    public function definition(): array
    {
        return [
            'type' => 'function',
            'name' => 'glob',
            'description' => 'Find files using a pattern, relative to the project root',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'pattern' => [
                        'type' => 'string',
                        'description' => 'The pattern to match files.',
                    ],
                ],
                'required' => ['pattern'],
                'additionalProperties' => false,
            ],
            'strict' => true,
        ];
    }

    public function use(array $arguments): string
    {
        $pattern = $arguments['pattern'] ?? '';

        if ($pattern === '') {
            return 'Error: No pattern provided.';
        }

        $root = base_path();
        $matches = [];

        if (str_contains($pattern, '**')) {
            $filePattern = basename(str_replace('**/', '', str_replace('**', '', $pattern)));
            $filePattern = $filePattern === '' ? '*' : $filePattern;

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                $relative = ltrim(str_replace($root, '', $file->getPathname()), DIRECTORY_SEPARATOR);

                if (fnmatch($filePattern, $file->getFilename())) {
                    $matches[] = $relative;
                }
            }
        } else {
            $found = glob($root.DIRECTORY_SEPARATOR.$pattern, GLOB_BRACE);

            if ($found === false) {
                return "Error: Invalid pattern: {$pattern}";
            }

            foreach ($found as $match) {
                $matches[] = ltrim(str_replace($root, '', $match), DIRECTORY_SEPARATOR);
            }
        }

        if ($matches === []) {
            return "(no matches for pattern: {$pattern})";
        }

        sort($matches);

        return implode("\n", $matches);
    }
}
