<?php

namespace App\Models;

use App\Traits\TracksActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory, SoftDeletes, TracksActivity;

    protected $fillable = ['post_id', 'user_id', 'parent_id', 'body', 'depth', 'likes_count', 'is_edited'];

    protected $casts = ['is_edited' => 'boolean', 'likes_count' => 'integer', 'depth' => 'integer'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id')->withTrashed();
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->withTrashed()->with('replies');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(CommentLike::class);
    }
}