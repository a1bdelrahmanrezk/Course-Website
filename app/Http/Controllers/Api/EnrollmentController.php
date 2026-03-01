<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Mail\EnrollmentConfirmationMail;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EnrollmentController extends Controller
{
    public function enroll(Request $request, Course $course): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to enroll in courses.'
            ], 401);
        }

        $user = Auth::user();

        // Check if course is published
        if (!$course->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'This course is not available for enrollment.'
            ], 403);
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
                return response()->json([
                    'success' => false,
                    'message' => 'You are already enrolled in this course.'
                ], 409);
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

            return response()->json([
                'success' => true,
                'message' => 'Successfully enrolled in ' . $course->title . '!',
                'data' => [
                    'enrollment_id' => $enrollment->id,
                    'course_title' => $course->title,
                    'first_lesson_url' => route('lessons.show', [$course->slug, $course->lessons->sortBy('order')->first()->id])
                ]
            ]);
        } catch (QueryException $e) {
            DB::rollBack();

            if ($e->getCode() == 23000) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already enrolled in this course.'
                ], 409);
            }

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while enrolling. Please try again.'
            ], 500);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Enrollment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again.'
            ], 500);
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
