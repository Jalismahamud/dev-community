<?php

namespace App\Models;

use App\Models\Message;
use App\Traits\TracksActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory, TracksActivity;

    protected $fillable = ['type'];

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')->withPivot('joined_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}