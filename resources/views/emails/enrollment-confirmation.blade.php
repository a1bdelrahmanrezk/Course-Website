@component('mail::message')
# Enrollment Confirmation

Congratulations! You have successfully enrolled in **{{ $course->title }}**.

## Course Details
- **Title:** {{ $course->title }}
- **Level:** {{ Str::title($course->level->name) }}
- **Duration:** {{ $course->formatted_duration }}
- **Lessons:** {{ $course->total_lessons }}

## Enrollment Information
- **Enrolled on:** {{ $enrollment->enrolled_at->format('F j, Y \a\t g:i A') }}
- **Enrollment ID:** #{{ $enrollment->id }}

## Next Steps
You can now access your course materials and track your progress through your dashboard.

@component('mail::button', ['url' => route('dashboard')])
Go to Dashboard
@endcomponent

Thank you for choosing our learning platform!

@component('mail::subcopy')
If you're having trouble clicking the button, copy and paste the URL below into your web browser:
{{ route('dashboard') }}
@endcomponent
@endcomponent
