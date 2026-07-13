<style>
  .document-comment{
    display:flex;
    align-items:center;
    gap:.75rem;
    padding:.55rem .8rem;
    margin-bottom:.35rem;
    border:1px solid #e5e7eb;
    border-radius:.65rem;
    background:#fff;
    transition:.2s;
    font-size:.85rem;
}

.document-comment:hover{
    background:#f9fafb;
    border-color:#d1d5db;
}

.document-comment-user{
    font-weight:600;
    color:#111827;
    white-space:nowrap;
}

.document-comment-department{
    padding:2px 8px;
    border-radius:999px;
    background:#eff6ff;
    color:#2563eb;
    font-size:.72rem;
    white-space:nowrap;
}

.document-comment-text{
    flex:1;
    color:#374151;
    overflow:hidden;
    white-space:nowrap;
    text-overflow:ellipsis;
}

.document-comment-time{
    color:#6b7280;
    font-size:.75rem;
    white-space:nowrap;
}

.document-comment-new{
    padding:2px 8px;
    border-radius:999px;
    background:#dcfce7;
    color:#15803d;
    font-size:.70rem;
    font-weight:600;
    white-space:nowrap;
}

.document-comment-id{
    color:#9ca3af;
    font-size:.72rem;
}

/* Dark Mode */

.dark .document-comment{
    background:#1f2937;
    border-color:#374151;
}

.dark .document-comment:hover{
    background:#273548;
}

.dark .document-comment-user{
    color:#f3f4f6;
}

.dark .document-comment-text{
    color:#d1d5db;
}

.dark .document-comment-time,
.dark .document-comment-id{
    color:#9ca3af;
}

.dark .document-comment-department{
    background:rgba(59,130,246,.18);
    color:#93c5fd;
}

.dark .document-comment-new{
    background:rgba(34,197,94,.18);
    color:#86efac;
}
</style>
@php
    /** @var \App\Models\DocumentComment $getState() */
    $comment = $getRecord();

    $user = $comment->user;
    $department = $user?->department;
    
@endphp

<div class="document-comment">

    <span class="document-comment-user">
        {{ $user?->name ?? 'غير معروف' }}
    </span>

    @if($department)
        <span class="document-comment-department">
            {{ $department->name }}
        </span>
    @endif

    <span class="document-comment-text">
        {{ $comment->comment }}
    </span>

    <span
        class="document-comment-time"
        title="{{ $comment->created_at->format('Y-m-d H:i') }}"
    >
        {{ $comment->created_at->diffForHumans() }}
    </span>

    @if($comment->created_at->diffInMinutes(now()) < 5)
        <span class="document-comment-new">
            جديد
        </span>
    @endif

    <span class="document-comment-id">
        #{{ $comment->id }}
    </span>

</div>