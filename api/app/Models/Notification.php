<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'actor_id',
    'type',
    'post_id',
    'comment_id',
    'read_at',
  ];

  protected $casts = [
    'read_at' => 'datetime',
  ];

  // reciever
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  // actor
  public function actor()
  {
    return $this->belongsTo(User::class, 'actor_id');
  }

  // post
  public function post()
  {
    return $this->belongsTo(Post::class);
  }

  // comment
  public function comment()
  {
    return $this->belongsTo(Comment::class);
  }
}
