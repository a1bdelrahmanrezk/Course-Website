<?php

namespace App\Filament\Widgets;

use App\Models\Enrollment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RecentEnrollmentsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Recent Enrollments (Last 7 Days)';

    protected static ?int $navigationSort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M j');
            $data[] = Enrollment::whereDate('enrolled_at', $date->format('Y-m-d'))->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Enrollments',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
