<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Department;
use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DocumentRoute extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'parent_route_id',
        'document_id',
        'from_department_id',
        'to_department_id',
        'sent_by',
        'received_by',
        'route_status',
        'sent_at',
        'received_at',
        'completed_at',
        'is_cc'
    ];

    protected function casts(): array
    {
        return [
            'route_status' => Status::class,
            'sent_at' => 'datetime',
            'received_at' => 'datetime',
            'completed_at' => 'datetime',
            'is_cc' => 'boolean',
        ];
    }


    protected static function booted()
    {
        parent::boot();

        static::creating(function ($DocumentRoute) {
            
             if (empty($DocumentRoute->uuid)) {
                $DocumentRoute->uuid = (string) Str::uuid();
            }

            // $DocumentRoute->created_by = auth()->id();
        });

       
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

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(
            Department::class,
            'from_department_id'
        );
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(
            Department::class,
            'to_department_id'
        );
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'sent_by'
        );
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'received_by'
        );
    }


    public function comments(): HasMany
    {
        return $this->hasMany(DocumentComment::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_route_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_route_id');
    }


    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */
  

    public function scopePending($query)
    {
        return $query->where('route_status', Status::DRAFT);
    }

    public function scopeReceived($query)
    {
        return $query->whereNotNull('received_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeMyDepartment($query)
    {
        return $query->where(
            'to_department_id',
            auth()->user()->department_id
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public function getIsReceivedAttribute(): bool
    {
        return !is_null($this->received_at);
    }

    public function getIsCompletedAttribute(): bool
    {
        return !is_null($this->completed_at);
    }

    public function getDurationAttribute(): ?int
    {
        if (!$this->received_at || !$this->completed_at) {
            return null;
        }

        return $this->received_at
            ->diffInDays($this->completed_at);
    }

    /*
    |--------------------------------------------------------------------------
    | Business Logic
    |--------------------------------------------------------------------------
    */

    public function receive(User $user): void
    {
        $this->update([
            'received_by' => $user->id,
            'received_at' => now(),
            'route_status' => Status::RECEIVED,
        ]);
    }

    public function complete(User $user): void
    {
        $this->update([
            'received_by' => $user->id,
            'completed_at' => now(),
            'route_status' => Status::COMPLETED,
        ]);
    }

    public function forward(
        Department $department,
        User $sender
    ): self {

        return self::create([
            'document_id' => $this->document_id,
            'from_department_id' => $this->to_department_id,
            'to_department_id' => $department->id,
            'sent_by' => $sender->id,
            'sent_at' => now(),
            'route_status' => Status::DRAFT,
        ]);
    }
}