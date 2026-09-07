<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\TracksActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use Filterable, HasFactory, TracksActivity;

    protected $fillable = ['user_id', 'title', 'body_markdown', 'likes_count', 'comments_count'];

    protected $casts = ['likes_count' => 'integer', 'comments_count' => 'integer'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(TechTag::class, 'post_tag');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class)->orderBy('sort_order');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->withTrashed();
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }
}