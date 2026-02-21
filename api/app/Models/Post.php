<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'topic',
        'repost_of_post_id',
        'quote_body',
        'repost_of_comment_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookmarkedBy()
    {
        return $this->belongsToMany(User::class, 'bookmarks')->withTimestamps();
    }

    public function likes()
    {
      return $this->hasMany(Like::class);
    }

    public function likedUsers()
    {
      return $this->belongsToMany(User::class, 'likes')->withTimestamps();
    }

    public function comments()
    {
      return $this->hasMany(Comment::class);
    }

    public function originalPost(): BelongsTo
    {
      return $this->belongsTo(Post::class, 'repost_of_post_id');
    }

    public function reposts(): HasMany
    {
      return $this->hasMany(Post::class, 'repost_of_post_id');
    }

  public function repostOfComment()
  {
    return $this->belongsTo(Comment::class, 'repost_of_comment_id');
  }

}
