<?php

namespace App\Models;

use App\Models\Post;
use App\Traits\TracksActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class PostImage extends Model
{
    use HasFactory, TracksActivity;

    protected $fillable = ['post_id', 'uploaded_by', 'path_webp', 'sort_order', 'alt_text'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}