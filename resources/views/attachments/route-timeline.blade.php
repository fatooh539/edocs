{{-- resources/views/filament/resources/documents/components/route-timeline.blade.php --}}
@php
    $record = $getRecord();
    $routes = $record->routes()->with(['fromDepartment', 'toDepartment', 'sender', 'receiver'])->orderBy('created_at')->get();

@endphp

<div class="route-timeline">
    @if($routes->isEmpty())
        <div class="text-sm text-gray-400 dark:text-gray-500 italic">
            لا يوجد مسار توجيه
        </div>
    @else
        <div class="relative">
            <!-- Vertical line -->
            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

            <div class="space-y-4">
                @foreach($routes as $index => $route)
                    <div class="relative flex gap-4">
                        <!-- Timeline dot -->
                        <div class="relative z-10 flex-shrink-0">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shadow-sm
                                @if($route->route_status === \App\enums\Status::COMPLETED)
                                    bg-green-500
                                @elseif($route->route_status === \App\enums\Status::RECEIVED)
                                    bg-blue-500
                                @elseif($route->route_status === \App\enums\Status::DRAFT)
                                    bg-yellow-500
                                @else
                                    bg-gray-400
                                @endif
                            ">
                                {{ $index + 1 }}
                            </div>
                        </div>

                        <!-- Route content -->
                        <div class="flex-1 min-w-0 bg-white dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 p-3 hover:shadow-sm transition-shadow">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $route->fromDepartment?->name ?? 'غير محدد' }}
                                    </span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $route->toDepartment?->name ?? 'غير محدد' }}
                                    </span>
                                </div>

                                <span class="text-xs px-2 py-0.5 rounded-full
                                    @if($route->route_status === \App\enums\Status::COMPLETED)
                                        bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                    @elseif($route->route_status === \App\enums\Status::RECEIVED)
                                        bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                                    @elseif($route->route_status === \App\enums\Status::DRAFT)
                                        bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400
                                    @else
                                        bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400
                                    @endif
                                ">
                                    @if($route->route_status === \App\enums\Status::COMPLETED)
                                        مكتمل
                                    @elseif($route->route_status === \App\enums\Status::RECEIVED)
                                        مستلم
                                    @elseif($route->route_status === \App\enums\Status::DRAFT)
                                        معلق
                                    @else
                                        {{ $route->route_status }}
                                    @endif
                                </span>
                            </div>

                            <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                @if($route->sent_at)
                                    <span>أرسل: {{ $route->sent_at->format('d/m/Y H:i') }}</span>
                                @endif
                                @if($route->received_at)
                                    <span>استلم: {{ $route->received_at->format('d/m/Y H:i') }}</span>
                                @endif
                                @if($route->completed_at)
                                    <span>أكمل: {{ $route->completed_at->format('d/m/Y H:i') }}</span>
                                @endif
                            </div>

                            @if($route->sender)
                                <div class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                    مرسل بواسطة: {{ $route->sender->name }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>