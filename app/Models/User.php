<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\TracksActivity;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, TracksActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_path',
        'bio',
        'github_url',
        'linkedin_url',
        'current_status',
    ];

    protected $appends = ['profile_completion'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    public function posts(): HasMany { return $this->hasMany(Post::class); }
    public function techTags(): BelongsToMany { return $this->belongsToMany(TechTag::class, 'user_tech_tag'); }
    public function followers(): BelongsToMany { return $this->belongsToMany(self::class, 'follows', 'following_id', 'follower_id'); }
    public function following(): BelongsToMany { return $this->belongsToMany(self::class, 'follows', 'follower_id', 'following_id'); }
    public function conversations(): BelongsToMany { return $this->belongsToMany(Conversation::class, 'conversation_participants')->withPivot('joined_at'); }
    public function messages(): HasMany { return $this->hasMany(Message::class, 'sender_id'); }

    public function receivesBroadcastNotificationsOn(): string
    {
        return 'private-user.' . $this->id;
    }

    public function getProfileCompletionAttribute(): int
    {
        $fields = [$this->avatar_path, $this->bio, $this->github_url, $this->linkedin_url, $this->current_status];
        $completed = count(array_filter($fields)) + ($this->techTags()->exists() ? 1 : 0);

        return (int) round(($completed / 6) * 100);
    }
}
