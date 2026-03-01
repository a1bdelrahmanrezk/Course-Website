<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function show($slug)
    {
        $course = Course::with(['level', 'lessons' => function($query) {
            $query->orderBy('order', 'asc');
        }])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('courses.show', compact('course'));
    }
}
