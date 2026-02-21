<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class CommentRepost extends Model
{
  protected $fillable = [
    'comment_id',
    'user_id',
  ];
}
