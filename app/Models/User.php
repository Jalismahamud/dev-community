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
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
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
        'username',
        'email',
        'password',
        'avatar_path',
        'bio',
        'github_url',
        'linkedin_url',
        'current_status',
    ];

    protected $appends = ['profile_completion'];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->username)) {
                $base = \Illuminate\Support\Str::slug(explode('@', $user->email)[0] ?: $user->name);
                $candidate = $base ?: 'dev_' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(6));
                $user->username = static::where('username', $candidate)->exists()
                    ? $candidate . '_' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(4))
                    : $candidate;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'username';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if ($value === 'undefined' || empty($value)) {
            abort(404);
        }

        return $this->where('username', $value)
            ->orWhere('id', $value)
            ->firstOrFail();
    }

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
    public function shares(): HasMany { return $this->hasMany(PostShare::class); }
    public function bookmarks(): HasMany { return $this->hasMany(Bookmark::class); }
    public function bookmarkedPosts(): BelongsToMany { return $this->belongsToMany(Post::class, 'bookmarks'); }

    public function receivesBroadcastNotificationsOn(): string
    {
        return 'private-user.' . $this->id;
    }

    public function getProfileCompletionAttribute(): int
    {
        $fields = [$this->avatar_path, $this->bio, $this->github_url, $this->linkedin_url, $this->current_status];
        $hasTechTags = $this->relationLoaded('techTags')
            ? $this->techTags->isNotEmpty()
            : \Illuminate\Support\Facades\Cache::remember('user_' . $this->id . '_has_tags', 3600, fn () => $this->techTags()->exists());
        $completed = count(array_filter($fields)) + ($hasTechTags ? 1 : 0);

        return (int) round(($completed / 6) * 100);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->email === 'test@example.com' 
            || $this->email === 'admin@dev.community'
            || $this->hasRole('admin')
            || app()->environment('local');
    }
}
