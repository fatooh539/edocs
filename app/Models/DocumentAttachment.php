<?php

namespace App\Models;

use App\Models\Document;
use App\Models\User;
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DocumentAttachment extends Model  implements HasRichContent,HasMedia
{
    use InteractsWithRichContent;
    use InteractsWithMedia;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'document_id',
        'file_name',
        'original_name',
        'file_path',
        'disk',
        'mime_type',
        'extension',
        'file_size',
        'checksum',
        'download_count',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'download_count' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    // public function getUrlAttribute(): string
    // {
    //     return Storage::disk($this->disk)
    //         ->url($this->file_path);
    // }

    // public function getExistsAttribute(): bool
    // {
    //     return Storage::disk($this->disk)
    //         ->exists($this->file_path);
    // }

    // public function getHumanSizeAttribute(): string
    // {
    //     return match (true) {

    //         $this->file_size >= 1073741824 =>
    //             round($this->file_size / 1073741824, 2) . ' GB',

    //         $this->file_size >= 1048576 =>
    //             round($this->file_size / 1048576, 2) . ' MB',

    //         $this->file_size >= 1024 =>
    //             round($this->file_size / 1024, 2) . ' KB',

    //         default =>
    //             $this->file_size . ' B',
    //     };
    // }

    // public function getIconAttribute(): string
    // {
    //     return match ($this->extension) {

    //         'pdf' => 'heroicon-o-document-text',

    //         'doc',
    //         'docx' => 'heroicon-o-document',

    //         'xls',
    //         'xlsx' => 'heroicon-o-table-cells',

    //         'jpg',
    //         'jpeg',
    //         'png',
    //         'gif',
    //         'webp' => 'heroicon-o-photo',

    //         default => 'heroicon-o-paper-clip',
    //     };
    // }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function incrementDownload(): void
    {
        $this->increment('download_count');
    }

    public function deleteFile(): void
    {
        if (
            Storage::disk($this->disk)
                ->exists($this->file_path)
        ) {
            Storage::disk($this->disk)
                ->delete($this->file_path);
        }

        $this->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeImages($query)
    {
        return $query->whereIn('extension', [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp',
        ]);
    }

    public function scopeDocuments($query)
    {
        return $query->whereIn('extension', [
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
        ]);
    }
}