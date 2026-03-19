<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $appends = [
      'avatar_url',
      ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function bookmarks()
    {
        return $this->belongsToMany(Post::class, 'bookmarks')->withTimestamps();
    }

    public function likes()
    {
      return $this->hasMany(\App\Models\Like::class);
    }

    public function likedPosts()
    {
      return $this->belongsToMany(\App\Models\Post::class, 'likes')->withTimestamps();
    }

    public function comments()
    {
      return $this->hasMany(\App\Models\Comment::class);
    }
    
    public function commentLikes(): HasMany
    {
      return $this->hasMany(CommentLike::class);
    }

    public function likedComments(): BelongsToMany
    {
      return $this->belongsToMany(Comment::class, 'comment_likes')
        ->withTimestamps();
    }

    public function following()
    {
      return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
        ->withTimestamps();
    }

    public function followers()
    {
      return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')
        ->withTimestamps();
    }

    public function getAvatarUrlAttribute(): ?string
    {
      if (!$this->avatar_path) return null;
      return asset('storage/' . $this->avatar_path);
    }

    public function notifications()
    {
      return $this->hasMany(Notification::class)->latest();
    }
}
