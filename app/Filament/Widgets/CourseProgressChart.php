<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\Enrollment;
use Filament\Widgets\ChartWidget;

class CourseProgressChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Course Completion Rates';

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $courses = Course::withCount(['enrollments' => function ($query) {
            $query->whereNotNull('completed_at');
        }])->withCount('enrollments')->get();

        $labels = [];
        $completedData = [];
        $totalData = [];

        foreach ($courses->take(10) as $course) {
            $labels[] = str_limit($course->title, 20);
            $completedData[] = $course->enrollments_count - $course->enrollments_count_completed;
            $totalData[] = $course->enrollments_count_completed;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Completed',
                    'data' => $totalData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'In Progress',
                    'data' => $completedData,
                    'backgroundColor' => 'rgba(251, 146, 60, 0.8)',
                    'borderColor' => 'rgba(251, 146, 60, 1)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

if (!function_exists('str_limit')) {
    function str_limit($string, $limit = 100, $end = '...')
    {
        if (strlen($string) <= $limit) {
            return $string;
        }
        return substr($string, 0, $limit) . $end;
    }
}
