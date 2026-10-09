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

    protected $fillable = ['user_id', 'code', 'slug', 'title', 'body_markdown', 'likes_count', 'comments_count', 'shares_count', 'views_count'];

    protected $casts = ['likes_count' => 'integer', 'comments_count' => 'integer', 'shares_count' => 'integer', 'views_count' => 'integer'];

    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            if (empty($post->code)) {
                $post->code = 'sig_' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(10));
            }
            if (empty($post->slug)) {
                $baseSlug = $post->title ? \Illuminate\Support\Str::slug($post->title) : 'signal';
                $post->slug = substr($baseSlug, 0, 50) . '-' . substr($post->code, 4, 6);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('code', $value)
            ->orWhere('slug', $value)
            ->orWhere('id', $value)
            ->firstOrFail();
    }

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

    public function shares(): HasMany
    {
        return $this->hasMany(PostShare::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }
}