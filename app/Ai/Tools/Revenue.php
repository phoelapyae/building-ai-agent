<?php

namespace App\Ai\Tools;

use Override;

class Revenue implements Tool {
    #[Override]
    public function definition(): array
    {
        return [
            'type' => 'function',
            'name' => 'site_revenue',
            'description' => 'Get details about daily/weekly/monthly/quarterly/yearly revenue for the company.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'period' => [
                        'type' => 'string',
                        'enum' => ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'],
                        'description' => 'The period of time to fetch revenue for.'
                    ]
                ],
                'required' => ['period'],
                'additionalProperties' => false
            ],
            'strict' => true
        ];
    }

    public function use(array $arguments = []): string {
        $period = $arguments['period'];

        $dailyRevenue = 1200;

        return [
            'daily'    => $dailyRevenue,
            'weekly'   => $dailyRevenue * 7,
            'monthly'  => $dailyRevenue * 30,
            'quarterly' => ($dailyRevenue * 30) * 3,
            'yearly'   => (($dailyRevenue * 30) * 3) * 4
        ][$period] ?? 'Unknown period.';
    }
}
