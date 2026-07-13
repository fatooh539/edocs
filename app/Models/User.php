<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password','department_id', 'job_title', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    return $this->belongsTo(Department::class);
}

public function createdDocuments(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(Document::class, 'created_by');
}

public function documentActions(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(DocumentAction::class);
}

public function documentNotes(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(DocumentNote::class);
}

public function uploadedAttachments(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(DocumentAttachment::class, 'uploaded_by');
}

public function tasksAssignedTo(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(DocumentAssignment::class, 'assigned_to');
}

public function tasksAssignedBy(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(DocumentAssignment::class, 'assigned_by');
}
}
