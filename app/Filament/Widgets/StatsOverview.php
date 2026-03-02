<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('All registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([7, 12, 10, 14, 15, 18, 20]),

            Stat::make('Total Courses', Course::count())
                ->description('All courses in system')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success')
                ->chart([2, 3, 5, 7, 8, 10, 12]),

            Stat::make('Total Lessons', Lesson::count())
                ->description('All lessons across courses')
                ->descriptionIcon('heroicon-m-play-circle')
                ->color('warning')
                ->chart([10, 15, 25, 35, 45, 55, 70]),

            Stat::make('Active Enrollments', Enrollment::whereNull('completed_at')->count())
                ->description('Currently enrolled users')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info')
                ->chart([5, 8, 12, 18, 22, 25, 30]),

            Stat::make('Completed Courses', Enrollment::whereNotNull('completed_at')->count())
                ->description('Successfully completed')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([1, 2, 3, 5, 7, 9, 12]),

            Stat::make('Published Courses', Course::where('is_published', true)->count())
                ->description('Available to students')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('primary')
                ->chart([2, 3, 4, 6, 7, 9, 11]),
        ];
    }
}
