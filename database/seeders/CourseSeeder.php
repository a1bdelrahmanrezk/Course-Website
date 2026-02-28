<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            [
                'data' => [
                    'level_id' => 1,
                    'title' => 'Course 1',
                    'slug' => 'course-1',
                    'description' => 'Course 1 description',
                    'is_published' => true,
                ],
                'lessons' => [
                    [
                        'title' => 'Lesson 1',
                        'order' => 1,
                        'duration_seconds' => 60,
                        'is_free_preview' => true,
                    ],
                    [
                        'title' => 'Lesson 2',
                        'order' => 2,
                        'duration_seconds' => 60,
                        'is_free_preview' => false,
                    ]
                ],
            ],
            [
                'data' => [
                    'level_id' => 1,
                    'title' => 'Course 2',
                    'slug' => 'course-2',
                    'description' => 'Course 2 description',
                    'is_published' => true,
                ],
                'lessons' => [
                    [
                        'title' => 'Lesson 1',
                        'order' => 1,
                        'duration_seconds' => 60,
                        'is_free_preview' => false,
                    ],
                    [
                        'title' => 'Lesson 2',
                        'order' => 2,
                        'duration_seconds' => 60,
                        'is_free_preview' => true,
                    ]
                ],
            ]
        ];

        foreach($courses as $course){
            $courseData = $course['data'];
            $lessons = $course['lessons'];

            $createdCourse = Course::create($courseData);

            foreach($lessons as $lesson){
                $lesson['course_id'] = $createdCourse->id;
                Lesson::create($lesson);
            }
        }
    }
}
