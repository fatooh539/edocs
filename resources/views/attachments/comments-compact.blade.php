{{-- resources/views/filament/resources/documents/components/comments-compact.blade.php --}}
@php
    $comments = $getState();
    $count = $comments->count();
@endphp

<div class="comments-compact">
    @if($count > 0)
        <div class="space-y-1">
            @foreach($comments as $comment)
                @php
                    $user = $comment->user;
                    $department = $user?->department;
                    $isNew = $comment->created_at->diffInMinutes(now()) < 5;
                @endphp

                <div class="comment-item flex items-start gap-2 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <!-- Avatar -->
                    <div class="flex-shrink-0 mt-0.5">
                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-[10px] font-medium">
                            {{ $user?->name ? Str::limit($user->name, 1, '') : '?' }}
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-xs font-medium text-gray-900 dark:text-gray-100">
                                {{ $user?->name ?? 'مستخدم غير معروف' }}
                            </span>

                            @if($department)
                                <span class="text-[10px] px-1.5 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full">
                                    {{ $department->name }}
                                </span>
                            @endif

                            <span class="text-[10px] text-gray-400 dark:text-gray-500">
                                {{ $comment->created_at->diffForHumans() }}
                            </span>

                            @if($isNew)
                                <span class="text-[10px] px-1.5 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full animate-pulse">
                                    جديد
                                </span>
                            @endif
                        </div>

                        <p class="text-sm text-gray-700 dark:text-gray-300 mt-0.5 leading-relaxed">
                            {{ $comment->comment }}
                        </p>
                    </div>

                    <span class="text-[10px] text-gray-400 dark:text-gray-500 flex-shrink-0">
                        #{{ $comment->id }}
                    </span>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-sm text-gray-400 dark:text-gray-500 italic">
            لا توجد تعليقات
        </div>
    @endif
</div>