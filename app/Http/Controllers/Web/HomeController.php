<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $courses = Course::with('level')
            ->published()
            ->orderBy('title')
            ->get();

        return view('home', compact('courses'));
    }
}
