<?php

namespace App\Observers;

use App\Models\CommentLike;
use App\Models\Notification;

class CommentLikeObserver
{
  public function created(CommentLike $commentLike): void
  {
    $comment = $commentLike->comment;

    if (! $comment || $comment->user_id === $commentLike->user_id) {
      return;
    }

    Notification::create([
      'user_id' => $comment->user_id,
      'actor_id' => $commentLike->user_id,
      'type' => 'comment_like',
      'comment_id' => $commentLike->comment_id,
    ]);
  }
}
