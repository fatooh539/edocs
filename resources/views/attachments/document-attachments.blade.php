<style>
    .document-empty {
    padding: .75rem;
    font-size: .875rem;
    font-style: italic;
    color: #6b7280;
    background: #f9fafb;
    border: 1px dashed #d1d5db;
    border-radius: .75rem;
}

.document-attachments {
    display: flex;
    flex-direction: column;
    gap: .75rem;
    margin-top: .75rem;
}

.document-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: .875rem;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: .75rem;
    transition: .2s;
}

.document-item:hover {
    border-color: #cbd5e1;
}

.document-info {
    display: flex;
    align-items: center;
    gap: .75rem;
    min-width: 0;
}

.document-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: .75rem;
    background: #f3f4f6;
    color: #6b7280;
    flex-shrink: 0;
}

.document-details {
    min-width: 0;
}

.document-title {
    margin: 0;
    font-size: .9rem;
    font-weight: 600;
    color: #374151;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.document-size {
    margin-top: .25rem;
    font-size: .75rem;
    color: #9ca3af;
}

.document-actions {
    display: flex;
    gap: .5rem;
}

.document-btn {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .45rem .85rem;
    border-radius: .5rem;
    text-decoration: none;
    font-size: .8rem;
    font-weight: 500;
    transition: .2s;
}

.document-btn-view {
    color: #374151;
    background: #f9fafb;
    border: 1px solid #d1d5db;
}

.document-btn-view:hover {
    background: #f3f4f6;
}

.document-btn-download {
    color: #fff;
    background: #2563eb;
}

.document-btn-download:hover {
    background: #1d4ed8;
}

@media (max-width:640px){

    .document-item{
        flex-direction:column;
        align-items:flex-start;
    }

    .document-actions{
        width:100%;
    }

    .document-btn{
        flex:1;
        justify-content:center;
    }
}
    </style>
@php
    $mediaItems = $getRecord()->getMedia('attachment_file');
@endphp

@if($mediaItems->isEmpty())

<div class="document-empty">
    لا توجد مرفقات مصاحبة لهذه المعاملة.
</div>

@else

<div class="document-attachments">

@foreach($mediaItems as $media)

@php
    $icon = match(strtolower($media->extension)) {
        'pdf' => 'heroicon-o-document-text',
        'doc', 'docx' => 'heroicon-o-document',
        'xls', 'xlsx' => 'heroicon-o-table-cells',
        'jpg', 'jpeg', 'png', 'webp' => 'heroicon-o-photo',
        default => 'heroicon-o-paper-clip'
    };
@endphp

<div class="document-item">

    <div class="document-info">

        <div class="document-icon">
            @svg($icon,'w-6 h-6')
        </div>

        <div class="document-details">

            <p class="document-title">
                {{ $media->name ?? $media->file_name }}
            </p>

            <p class="document-size">
                {{ $media->human_size }}
            </p>

        </div>

    </div>

    <div class="document-actions">

        <a href="{{ $media->getUrl() }}"
           target="_blank"
           class="document-btn document-btn-view">

            @svg('heroicon-o-eye','w-4 h-4')
            <span>عرض</span>

        </a>

        <a href="{{ $media->getUrl() }}"
           download
           class="document-btn document-btn-download">

            @svg('heroicon-o-arrow-down-tray','w-4 h-4')
            <span>تحميل</span>

        </a>

    </div>

</div>

@endforeach

</div>

@endif