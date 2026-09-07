<?php

namespace App\Models;

use App\Traits\TracksActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

class TechTag extends Model
{
    use HasFactory, TracksActivity;

    protected $fillable = ['name', 'slug', 'color_hex'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_tech_tag');
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tag');
    }
}