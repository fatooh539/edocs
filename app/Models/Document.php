<?php

namespace App\Models;

use App\enums\Priority;
use App\enums\status;
use App\Models\Department;
use Filament\Forms\Components\RichEditor\FileAttachmentProviders\SpatieMediaLibraryFileAttachmentProvider;
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;

use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;



class Document extends Model implements HasRichContent, HasMedia
{
    use InteractsWithRichContent;
    use InteractsWithMedia;

    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'document_no',
        'subject',
        'body',
        'sender_department_id',
        'current_department_id',
        'attachment_file',
        'created_by',
        'priority',
        'status',
        'confidential',
        'document_date',
        'archive_no',
        // 'closed_at',
        // 'closed_by',
    ];

    protected function casts(): array
    {
        return [
            'priority' => Priority::class,
            'status' => Status::class,

            'document_date' => 'date',

            'confidential' => 'boolean',


        ];
    }

    protected static function booted()
    {
        parent::boot();

        static::creating(function ($document) {

            //  if (empty($document->uuid)) {
            //     $document->uuid = (string) Str::uuid();
            // }

            $document->created_by = auth()->id();
        });


    }

    public function setUpRichContent(): void
    {
        $this->registerRichContent('content')
            ->fileAttachmentProvider(
                SpatieMediaLibraryFileAttachmentProvider::make()
                    ->collection('content-file-attachments'),
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function comments(): HasManyThrough
    {
        return $this->hasManyThrough(
            DocumentComment::class,
            DocumentRoute::class,
            'document_id',        // FK على DocumentRoute
            'document_route_id',  // FK على DocumentComment
            'id',
            'id'
        );
    }

    //to_cc
    public function ccDepartments(): BelongsToMany
    {
        return $this->belongsToMany(
            Department::class,
            'document_routes',
            'document_id',
            'to_department_id'
        )->wherePivot('is_cc', true);
    }

    public function receiveDepartment(): BelongsTo
    {
        //where ('sender_department_id','<>', auth()->user()->department_id)
        return $this->belongsTo(
            Department::class,
            'sender_department_id'
        )->where('id', '<>', auth()->user()->department_id);
    }

    public function currentDepartment(): BelongsTo
    {
        return $this->belongsTo(
            Department::class,
            'current_department_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        )->where('id', '<>', auth()->user()->id);
    }



    public function routes(): HasMany
    {
        return $this->hasMany(DocumentRoute::class);
    }


    public function currentRoute()
    {
        return $this->hasOne(DocumentRoute::class)
            ->latestOfMany();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DocumentAttachment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    // public function scopeInbox($query)
    // {
    //     return $query->whereHas('routes', function ($q) {
    //         $q->where('to_department_id', auth()->user()->department_id)
    //             ->whereNull('completed_at');
    //     });
    // }

    public function scopeInbox($query)
    {
        return $query->whereHas('routes', function ($q) {
            $q->where('to_department_id', auth()->user()->department_id)
                ->where('is_cc', false)
                ->whereNull('completed_at');
        });
    }

    public function scopeOutbox($query)
    {
        return $query->whereHas('routes', function ($q) {
            $q->where('from_department_id', auth()->user()->department_id);
        });
    }

    public function scopeOpen($query)
    {
        return $query->whereHas('routes', function ($q) {
            $q->where(function ($s) {
                $s->where('from_department_id', auth()->user()->department_id)
                    ->orWhere('to_department_id', auth()->user()->department_id);
            });
        })
            ->whereNotIn('status', [
                Status::COMPLETED,
                Status::ARCHIVED,
            ]);
    }

    public function scopeCc($query)
    {
        return $query->whereHas('routes', function ($q) {
            $q->where('is_cc', true)
                ->where('to_department_id', auth()->user()->department_id);
        });
    }

    public function scopeArchived($query)
    {
        return $query->whereIn('status', [
            Status::COMPLETED,
            Status::ARCHIVED,
        ]);
    }

    public function scopeMyArchive($query)
    {
        return $query->whereHas('routes', function ($q) {
            $q->where(function ($s) {
                $s->where('from_department_id', auth()->user()->department_id)
                    ->orWhere('to_department_id', auth()->user()->department_id);
            });
        })
            ->whereIn('status', [
                Status::COMPLETED,
                Status::ARCHIVED,
            ]);
    }

    public function scopeMyDocuments($query)
    {
        return $query->where(function ($q) {

            $q->where('sender_department_id', auth()->user()->department_id)

                ->orWhereHas('routes', function ($r) {
                    $r->where('from_department_id', auth()->user()->department_id)
                        ->orWhere('to_department_id', auth()->user()->department_id);
                });

        });
    }
    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public function getIsClosedAttribute(): bool
    {
        return in_array($this->status, [
            Status::COMPLETED,
            Status::ARCHIVED,
        ]);
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            Priority::LOW => 'gray',
            Priority::NORMAL => 'success',
            Priority::HIGH => 'warning',
            Priority::URGENT => 'danger',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function markAsCompleted(User $user): void
    {
        $this->update([
            'status' => Status::COMPLETED,
            'closed_at' => now(),
            'closed_by' => $user->id,
        ]);
    }
}