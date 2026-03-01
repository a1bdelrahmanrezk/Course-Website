<link rel="stylesheet" href="https://cdn.plyr.io/3.8.4/plyr.css" />
<script src="https://cdn.plyr.io/3.8.4/plyr.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $lesson->title }} - {{ $course->title }}
        </h2>
    </x-slot>

    <div class="py-6" x-data="lessonPlayer()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" style="gap:18px !important">
                <!-- Video Player Section -->
                <div class="lg:col-span-2">
                    <!-- Video Player -->
                    <div class="bg-black rounded-lg overflow-hidden mb-6">
                        @if($lesson->getFirstMedia('lessons'))
                            @if(str_contains($lesson->getFirstMedia('lessons')->getUrl(), 'youtube.com') || str_contains($lesson->getFirstMedia('lessons')->getUrl(), 'youtu.be'))
                                <div 
                                    id="lesson-player" 
                                    x-ref="videoPlayer"
                                    data-plyr-provider="youtube"
                                    data-plyr-video-id="{{ str_contains($lesson->getFirstMedia('lessons')->getUrl(), 'youtu.be') ? substr($lesson->getFirstMedia('lessons')->getUrl(), strrpos($lesson->getFirstMedia('lessons')->getUrl(), '/') + 1) : substr(parse_url($lesson->getFirstMedia('lessons')->getUrl(), PHP_URL_QUERY), 2) }}"
                                ></div>
                            @else
                                <video 
                                    id="lesson-player" 
                                    x-ref="videoPlayer"
                                    playsinline 
                                    controls
                                >
                                    <source src="{{ $lesson->getFirstMedia('lessons')->getUrl() }}" type="video/mp4" />
                                </video>
                            @endif
                        @else
                            <div class="flex items-center justify-center h-64 bg-gray-100 text-gray-500">
                                <p>No video available</p>
                            </div>
                        @endif
                    </div>

                    <!-- Lesson Navigation -->
                    <div class="flex items-center justify-between rounded-lg shadow p-4" style="background-color: #374151 !important; gap: 1rem !important;">
                        <div class="flex items-center space-x-4">
                            @if($previousLesson)
                                <a href="{{ route('lessons.show', [$course->slug, $previousLesson->id]) }}" 
                                   class="flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                    Previous
                                </a>
                            @endif

                            <div class="flex items-center space-x-4">
                                <button 
                                    @click="toggleComplete()"
                                    x-bind:class="isCompleted ? 'bg-green-600 hover:bg-green-700' : 'bg-indigo-600 hover:bg-indigo-700'"
                                    class="px-6 py-3 text-white rounded-lg font-medium transition-colors duration-200 transform hover:scale-105"
                                >
                                    <span x-text="isCompleted ? '✓ Completed' : 'Mark as Complete'"></span>
                                </button>

                                @if($nextLesson)
                                    <a href="{{ route('lessons.show', [$course->slug, $nextLesson->id]) }}" 
                                       class="flex items-center px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200 transform hover:scale-105">
                                        Next Lesson
                                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                @endif
                        </div>
                    </div>


                </div>
                    <!-- Lesson Info -->
                    <div class="bg-white rounded-lg shadow p-6 mt-6 mb-6">
                        <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $lesson->title }}</h1>
                        <p class="text-gray-600 mb-4">{{ $lesson->description }}</p>
                        
                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $lesson->formatted_duration }}
                            </span>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                Lesson {{ $lesson->order }} of {{ $course->lessons->count() }}
                            </span>
                            @if($lesson->is_free_preview)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Free Preview
                                </span>
                            @endif
                        </div>
                    </div>
                <!-- Lessons Sidebar -->

            </div>
                            <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Course Lessons</h3>
                        
                        <div class="space-y-2">
                            @foreach($course->lessons->sortBy('order') as $lessonItem)
                                <div class="border rounded-lg overflow-hidden" x-data="{ open: @if($lessonItem->id === $lesson->id) true @else false @endif }" style="margin-bottom: 18px !important;">
                                    <!-- Lesson Header -->
                                    <button 
                                        @click="open = !open"
                                        class="w-full px-4 py-3 text-left flex items-center justify-between hover:bg-gray-50 transition-colors duration-200"
                                        x-bind:class="{
                                            'bg-blue-50 border-blue-200': @json($lessonItem->id === $lesson->id),
                                            'bg-white': @json($lessonItem->id !== $lesson->id)
                                        }"
                                    >
                                        <div class="flex items-center space-x-3">
                                            <!-- Lesson Number -->
                                            <!-- <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium"
                                                 x-bind:class="{
                                                     'bg-blue-600 text-white': @json($lessonItem->id === $lesson->id),
                                                     'bg-gray-200 text-gray-600': @json($lessonItem->id !== $lesson->id)
                                                 }">
                                                {{ $loop->iteration }}
                                            </div> -->
                                            
                                            <!-- Lesson Title -->
                                            <div class="flex-1">
                                                <h4 class="text-sm font-medium text-gray-900">{{ $lessonItem->title }}</h4>
                                                <p class="text-xs text-gray-500">{{ $lessonItem->formatted_duration }}</p>
                                            </div>
                                        </div>

                                        <!-- Accordion Icon -->
                                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" 
                                             x-bind:class="{ 'rotate-180': open }"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>

                                    <!-- Accordion Content -->
                                    <div x-show="open" x-transition class="border-t">
                                        <div class="p-4">
                                            <p class="text-sm text-gray-600 mb-3">{{ Str::limit($lessonItem->description, 150) }}</p>
                                            
                                            <div class="flex items-center justify-between">
                                                @if($lessonItem->is_free_preview)
                                                    <span class="text-xs text-green-600 font-medium">Free Preview</span>
                                                @else
                                                    <span class="text-xs text-gray-500">Enrolled only</span>
                                                @endif

                                                @if($lessonItem->id === $lesson->id)
                                                    <span class="text-xs text-blue-600 font-medium">Current</span>
                                                @else
                                                    <a href="{{ route('lessons.show', [$course->slug, $lessonItem->id]) }}"
                                                       class="inline-flex items-center px-4 py-2 text-sm bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        Watch Now
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Course Progress -->
                    @if(auth()->check() && auth()->user()->isEnrolledIn($course))
                        <div class="bg-white rounded-lg shadow p-6 mt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Progress</h3>
                            
                            <div class="mb-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-2">
                                    <span>Completed</span>
                                    <span x-text="progressPercentage + '%'"></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                         x-bind:style="'width: ' + progressPercentage + '%'"></div>
                                </div>
                            </div>
                            
                            <p class="text-sm text-gray-600">
                                <span x-text="completedLessons"></span> of {{ $course->lessons->count() }} lessons completed
                            </p>
                        </div>
                    @endif
                </div>
        </div>
    </div>
</x-app-layout>

<script>
function lessonPlayer() {
    return {
        player: null,
        isCompleted: {!! $lessonProgress && $lessonProgress->completed_at ? 'true' : 'false' !!},
        watchSeconds: {!! $lessonProgress ? $lessonProgress->watch_seconds : 0 !!},
        lessonTitle: {!! json_encode($lesson->title) !!},
        progressUrl: {!! json_encode(route('lessons.progress', [$course->slug, $lesson->id])) !!},
        nextLessonUrl: @if($nextLesson) {!! json_encode(route('lessons.show', [$course->slug, $nextLesson->id])) !!} @else null @endif,
        courseProgressUrl: {!! json_encode('/api/courses/' . $course->slug . '/progress') !!},
        csrfToken: {!! json_encode(csrf_token()) !!},
        isEnrolled: @if(auth()->check()) @if(auth()->user()->enrollments()->where('course_id', $course->id)->exists()) true @else false @endif @else false @endif,
        progressPercentage: 0,
        completedLessons: 0,
        lastSaveTime: null,
        
        init() {
            // Initialize Plyr player for all video types
            this.player = new Plyr('#lesson-player', {
                title: this.lessonTitle,
                controls: ['play-large', 'play', 'progress', 'current-time', 'duration', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen'],
                settings: ['captions', 'quality', 'speed'],
                tooltips: { controls: true, seek: true },
                captions: { active: false, update: false, language: 'auto' },
                quality: { default: 720, options: [4320, 2880, 2160, 1440, 1080, 720, 576, 480, 360, 240] },
                speed: { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2] },
                loop: { active: false },
                clickToPlay: true,
                hideControls: true,
                resetOnEnd: false,
                keyboard: { focused: true, global: true },
                // YouTube specific settings
                youtube: { 
                    noCookie: false, 
                    rel: 0, 
                    showinfo: 0, 
                    iv_load_policy: 3, 
                    modestbranding: 1 
                }
            });

            // Set initial time if user has progress
            if (this.watchSeconds > 0) {
                this.player.once('ready', () => {
                    this.player.currentTime = this.watchSeconds;
                });
            }

            // Set up event listeners
            this.setupEventListeners();
            
            // Calculate initial progress
            this.calculateProgress();
        },
        
        setupEventListeners() {
            if (!this.player) return;
            
            console.log('Setting up event listeners for player');
            
            // Track progress - save every second
            this.player.on('timeupdate', () => {
                const currentTime = Math.floor(this.player.currentTime);
                console.log('Timeupdate - Current time:', currentTime, 'Watch seconds:', this.watchSeconds);
                this.watchSeconds = currentTime;  // Update first
                this.saveProgress();  // Save every second
            });
            
            // Auto-advance to next lesson when completed
            this.player.on('ended', () => {
                console.log('Video ended');
                if (!this.isCompleted && this.isEnrolled) {
                    this.markAsCompleted();
                }
                // Removed auto-redirect - user can click Next button manually
            });
        },

        toggleComplete() {
            if (this.isCompleted) {
                this.markAsIncomplete();
            } else {
                this.markAsCompleted();
            }
        },

        markAsCompleted() {
            this.isCompleted = true;
            this.saveProgress(true);
            this.calculateProgress();
        },

        markAsIncomplete() {
            this.isCompleted = false;
            this.saveProgress(false);
            this.calculateProgress();
        },

        saveProgress(completed = false) {
            // Save progress for all users - no enrollment check
            const data = {
                watch_seconds: this.watchSeconds,
                completed: completed
            };

            console.log('Saving progress:', data);

            fetch(this.progressUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                console.log('Progress saved response:', data);
                if (data.success && data.completed_at) {
                    this.isCompleted = true;
                }
            })
            .catch(error => console.error('Error saving progress:', error));
        },

        calculateProgress() {
            // Skip API call for now - use local calculation
            // This would typically come from your enrolled lessons data
            // For now, we'll use a simple calculation
            this.progressPercentage = this.isCompleted ? 100 : Math.floor((this.watchSeconds / 300) * 100); // Assuming 5min videos
            this.completedLessons = this.isCompleted ? 1 : 0;
        },
    }
}
</script>
