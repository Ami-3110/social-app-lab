<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentMedia extends Model
{
  protected $fillable = [
    'comment_id',
    'path',
    'sort_order',
  ];

  public function comment(): BelongsTo
  {
    return $this->belongsTo(Comment::class);
  }

}
