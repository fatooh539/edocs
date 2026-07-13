{{-- resources/views/filament/resources/documents/components/attachments-compact.blade.php --}}
@php
    $record = $getRecord();
    $attachments = $record->attachments;
@endphp

<div class="attachments-compact">
    @if($attachments->isEmpty())
        <div class="text-sm text-gray-400 dark:text-gray-500 italic">
            لا توجد مرفقات
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
            @foreach($attachments as $attachment)
                <div class="attachment-item group relative bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-primary-400 dark:hover:border-primary-500 transition-all duration-200 overflow-hidden">
                    <a href="{{ $attachment->url }}" target="_blank" class="block p-2">
                        <div class="flex items-center gap-2">
                            <div class="flex-shrink-0 text-gray-500 dark:text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition">
                                @svg($attachment->icon, 'w-5 h-5')
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition" title="{{ $attachment->original_name }}">
                                    {{ $attachment->original_name }}
                                </p>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500">
                                    {{ $attachment->human_size }}
                                </p>
                            </div>
                        </div>
                    </a>
                    <div class="absolute inset-0 bg-primary-500/5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
                </div>
            @endforeach
        </div>
    @endif
</div>