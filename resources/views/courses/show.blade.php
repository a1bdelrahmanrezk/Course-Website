<x-app-layout>
    <!-- Flash Messages -->
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    @if(session('info'))
    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6" role="alert">
        <span class="block sm:inline">{{ session('info') }}</span>
    </div>
    @endif

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $course->title }}
        </h2>
    </x-slot>

            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Course Header -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8" style="padding: 18px !important;">
                <!-- Course Image -->
                <div class="relative h-64 bg-gradient-to-br from-blue-500 to-purple-600">
                    <img
                        style="width: fit-content;"
                        src="{{ $course->image_url }}"
                        alt="{{ $course->title }}"
                        class="w-full h-full object-cover"
                        onerror="this.src='https://via.placeholder.com/800x400?text={{ urlencode($course->title) }}'">
                </div>

                <!-- Course Info -->
                <div class="p-8">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $course->title }}</h1>
                            <p class="text-gray-600">{{ $course->description }}</p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-6 md:mt-0 flex flex-col sm:flex-row gap-3">
                            @if(auth()->check())
                                @if(auth()->user()->isEnrolledIn($course))
                                    <a href="{{ route('lessons.show', [$course->slug, $course->lessons->sortBy('order')->first()->order]) }}" class="px-6 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200">
                                        {{ __('Continue Learning') }}
                                    </a>
                                @else
                                    <form action="{{ route('enroll', $course->id) }}" method="POST" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                                            {{ __('Enroll Now') }}
                                        </button>
                                    </form>
                                @endif
                            @else
                            <a href="{{ route('login') }}" class="px-6 py-3 bg-blue-600 text-white text-center rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                                {{ __('Login to Enroll') }}
                            </a>
                            @endif
                        </div>
                    </div>

                    <!-- Course Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="flex items-center space-x-3" style="gap: 12px !important;">
                            <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Duration</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $course->formatted_duration }}</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3" style="gap: 12px !important;">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Lessons</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $course->total_lessons }} {{ __('lessons') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3" style="gap: 12px !important;">
                            <div class="flex-shrink-0 w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Level</p>
                                <p class="text-lg font-semibold text-gray-900">{{ Str::title($course->level->name) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lessons Section -->
            <div class="bg-white rounded-xl shadow-lg p-8" style="padding: 18px !important;margin-bottom:20px !important;">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Course Content') }}</h2>

                @if($course->lessons->count() > 0)
                <div class="space-y-4">
                    @foreach($course->lessons->sortBy('order') as $lesson)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200 mb-8" style="margin-bottom: 18px !important;">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4 gap-6">
                                <!-- Lesson Number -->
                                <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-semibold text-blue-600">{{ $loop->iteration }}</span>
                                </div>

                                <!-- Lesson Info -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ $lesson->title }}</h3>
                                    <p class="text-sm text-gray-600">{{ Str::limit($lesson->description, 100) }}</p>

                                    <!-- Lesson Meta -->
                                    <div class="flex items-center space-x-4 mt-2">

                                        <span class="text-sm text-gray-500" style="margin-right: 8px !important;">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $lesson->formatted_duration }}
                                        </span>
                                        @if($lesson->is_free_preview)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ __('Free Preview') }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Action Button -->
                                    <div>
                                        @if($lesson->is_free_preview)
                                             <a href="{{ route('lessons.show', [$course->slug, $lesson->id]) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200" style="background-color:#374151 !important;">
                                                {{ __('Watch Lesson') }}
                                             </a>
                                         @elseif(auth()->check() && auth()->user()->isEnrolledIn($course))
                                             <a href="{{ route('lessons.show', [$course->slug, $lesson->id]) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200" style="background-color:#374151 !important;">
                                                {{ __('Watch Lesson') }}
                                            </a>
                                        @else
                                            <button class="px-4 py-2 bg-gray-300 text-gray-600 rounded-lg font-medium cursor-not-allowed" disabled>
                                                {{ __('Enroll to Access') }}
                                            </button>
                                        @endif
                                    </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <!-- No Lessons State -->
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('No lessons available yet') }}</h3>
                    <p class="text-gray-600">{{ __('Check back later for course content.') }}</p>
                </div>
                @endif
            </div>

            <!-- Back to Courses -->
            <div class="mt-8 text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('Back to Courses') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
