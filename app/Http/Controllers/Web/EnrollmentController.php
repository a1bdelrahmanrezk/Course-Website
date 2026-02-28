<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Mail\EnrollmentConfirmationMail;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EnrollmentController extends Controller
{
    public function enroll(Course $course)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('message', 'Please login to enroll in courses.');
        }

        $user = Auth::user();

        // Check if course is published
        if (!$course->is_published) {
            return back()->with('error', 'This course is not available for enrollment.');
        }

        try {
            DB::beginTransaction();

            // Check if already enrolled (this also handles race conditions)
            $existingEnrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->lockForUpdate()
                ->first();

            if ($existingEnrollment !== null) {
                DB::rollBack();
                return back()->with('info', 'You are already enrolled in this course.');
            }

            // Create enrollment
            $enrollment = Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'enrolled_at' => now(),
            ]);
            
            DB::commit();

            // Send email confirmation 
            $this->sendEnrollmentConfirmation($user, $course, $enrollment);

            return back()->with('success', 'Successfully enrolled in ' . $course->title . '!');
        } catch (QueryException $e) {
            DB::rollBack();

            if ($e->getCode() == 23000) {
                return back()->with('info', 'You are already enrolled in this course.');
            }

            return back()->with('error', 'An error occurred while enrolling. Please try again.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Enrollment error: ' . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    private function sendEnrollmentConfirmation($user, $course, $enrollment)
    {
        try {
            // Use queue to handle email sending asynchronously
            Mail::to($user->email)->queue(new EnrollmentConfirmationMail($course, $enrollment));
        } catch (Exception $e) {
            Log::error('Failed to send enrollment confirmation: ' . $e->getMessage());
        }
    }
}
