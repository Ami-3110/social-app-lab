<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class CommentBookmark extends Model
{
  use HasFactory;

  protected $fillable = ['user_id', 'comment_id'];

  public function comment()
  {
    return $this->belongsTo(Comment::class);
  }
}
