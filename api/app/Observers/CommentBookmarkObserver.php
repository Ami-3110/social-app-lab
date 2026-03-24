<?php

namespace App\Observers;

use App\Models\CommentBookmark;
use App\Models\Notification;

class CommentBookmarkObserver
{
  public function created(CommentBookmark $commentBookmark): void
  {
    $comment = $commentBookmark->comment;

    if (! $comment || $comment->user_id === $commentBookmark->user_id) {
      return;
    }

    Notification::create([
      'user_id' => $comment->user_id,
      'actor_id' => $commentBookmark->user_id,
      'type' => 'comment_bookmark',
      'comment_id' => $commentBookmark->comment_id,
    ]);
  }
}
