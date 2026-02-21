<?php
// app/Models/Comment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
  protected $fillable = [
    'user_id',
    'post_id',
    'body',
    'parent_id',
    'root_id',
    ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function post()
  {
    return $this->belongsTo(Post::class);
  }

  public function likes(): HasMany
  {
    return $this->hasMany(CommentLike::class);
  }

  public function likedUsers(): BelongsToMany
  {
    return $this->belongsToMany(User::class, 'comment_likes')
      ->withTimestamps(); 
  }

  public function bookmarks()
  {
    return $this->hasMany(CommentBookmark::class);
  }

  public function parent()
  {
    return $this->belongsTo(self::class, 'parent_id');
  }

  public function root()
  {
    return $this->belongsTo(self::class, 'root_id');
  }

  public function replies()
  {
    return $this->hasMany(self::class, 'parent_id');
  }

  public function reposts()
  {
    return $this->hasMany(CommentRepost::class);
  }
}
