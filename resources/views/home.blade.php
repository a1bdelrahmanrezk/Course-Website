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

    @if(session('message'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Courses') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="text-center mb-12" style="margin-bottom: 35px;">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    {{ __('Explore Our Courses') }}
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    {{ __('Discover high-quality courses designed to help you master new skills and advance your career.') }}
                </p>
            </div>

            <!-- Courses Grid -->
            @if($courses->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12" style="margin-bottom: 35px;">
                @foreach($courses as $course)
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300" style="border-radius: 18px;">
                    <!-- Course Image -->
                    <div class="relative h-48 bg-gradient-to-br from-blue-500 to-purple-600">
                        <img src="{{ $course->image_url }}"
                            alt="{{ $course->title }}"
                            class="w-full h-full object-cover"
                            onerror="this.src='https://via.placeholder.com/400x300?text={{ urlencode($course->title) }}'">

                        <!-- Level Badge -->
                        <div class="absolute top-4 right-4">
                            <span class="bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-medium text-gray-800">
                                {{ Str::title($course->level->name) }}
                            </span>
                        </div>
                    </div>

                    <!-- Course Content -->
                    <div class="p-6">
                        <!-- Course Title -->
                        <h3 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2">
                            {{ $course->title }}
                        </h3>

                        <!-- Course Description -->
                        <p class="text-gray-600 mb-4 line-clamp-3">
                            {{ Str::limit($course->description, 100) }}
                        </p>

                        <!-- Course Stats -->
                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $course->formatted_duration }}
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    {{ $course->total_lessons }} {{ __('lessons') }}
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex space-x-3">
                            <a href="{{ route('courses.show', $course->slug) }}" class="flex-1 bg-blue-600 text-white text-center py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200" style="color:#374151 !important;">
                                {{ __('View Details') }}
                            </a>

                            @if(auth()->check())
                                        @if(auth()->user()->isEnrolledIn($course))
                                            <a href="{{ route('courses.show', $course->slug) }}" class="flex-1 bg-green-600 text-white text-center py-2 px-4 rounded-lg font-medium hover:bg-green-700 transition-colors duration-200" style="background-color:#37415161 !important;">
                                                {{ __('Continue') }}
                                            </a>
                                        @else
                                            <form action="{{ route('enroll', $course->id) }}" method="POST" class="flex-1">
                                                @csrf
                                                <button type="submit" class="w-full bg-gray-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-gray-700 transition-colors duration-200" style="background-color:#374151 !important;">
                                                    {{ __('Enroll') }}
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <a href="{{ route('login') }}" class="flex-1 bg-gray-600 text-white text-center py-2 px-4 rounded-lg font-medium hover:bg-gray-700 transition-colors duration-200" style="background-color:#374151 !important;">
                                            {{ __('Login to Enroll') }}
                                        </a>
                                    @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Explore All Button -->
            <!-- <div class="text-center">
                    <button class="bg-blue">
                        {{ __('Explore All Courses') }}
                    </button>
                </div> -->
            @else
            <!-- No Courses State -->
            <div class="text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('No courses available') }}</h3>
                <p class="text-gray-600">{{ __('Check back later for new courses.') }}</p>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>