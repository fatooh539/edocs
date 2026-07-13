<?php

namespace App\Models;

use App\Models\DocumentRoute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class DocumentComment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'document_route_id',
        'user_id',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function route(): BelongsTo
    {
        return $this->belongsTo(DocumentRoute::class, 'document_route_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getAuthorNameAttribute(): string
    {
        return $this->user?->name ?? 'Unknown';
    }

    public function getDepartmentAttribute(): ?Department
    {
        return $this->user?->department;
    }

    public function getShortCommentAttribute(): string
    {
        return str($this->comment)->limit(80);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeLatestFirst($query)
    {
        return $query->latest();
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

  
    /**
     * إضافة تعليق على مستند
     */
    public static function addCommentToDocument(Document $document, string $comment, ?User $user = null): self
    {
        $user = $user ?? Auth::user();
        
        // الحصول على الـ route الحالي للمستند
        $currentRoute = $document->routes()
            ->where('to_department_id', $document->current_department_id)
            ->whereNull('completed_at')
            ->first();

        // إذا لم يوجد route، نبحث عن آخر route
        if (!$currentRoute) {
            $currentRoute = $document->routes()
                ->latest('created_at')
                ->first();
        }

        if ($currentRoute) {
            return self::create([
                'document_route_id' => $currentRoute->id,
                'user_id' => $user->id,
                'comment' => $comment,
            ]);
        }

        throw new \Exception('No route found for this document');
    }

    /**
     * إضافة تعليق على Route محدد
     */
    public static function addCommentToRoute(DocumentRoute $route, string $comment, ?User $user = null): self
    {
        $user = $user ?? Auth::user();

        return self::create([
            'document_route_id' => $route->id,
            'user_id' => $user->id,
            'comment' => $comment,
        ]);
    }

    /**
     * الحصول على جميع تعليقات المستند
     */
    public static function getDocumentComments(Document $document): \Illuminate\Database\Eloquent\Collection
    {
        $routeIds = $document->routes()->pluck('id');
        
        return self::whereIn('document_route_id', $routeIds)
            ->with(['user', 'user.department'])
            ->latest()
            ->get();
    }

    /**
     * الحصول على تعليقات المستند بشكل مرتب
     */
    public static function getDocumentCommentsGrouped(Document $document): array
    {
        $comments = self::getDocumentComments($document);
        
        $grouped = [];
        
        foreach ($comments as $comment) {
            $routeId = $comment->document_route_id;
            if (!isset($grouped[$routeId])) {
                $route = DocumentRoute::find($routeId);
                $grouped[$routeId] = [
                    'route' => $route,
                    'comments' => [],
                ];
            }
            $grouped[$routeId]['comments'][] = $comment;
        }
        
        return $grouped;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

   

    public function scopeForDocument($query, Document $document)
    {
        $routeIds = $document->routes()->pluck('id');
        return $query->whereIn('document_route_id', $routeIds);
    }

    public function scopeForRoute($query, DocumentRoute $route)
    {
        return $query->where('document_route_id', $route->id);
    }

}