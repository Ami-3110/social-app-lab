<?php

namespace App\Observers;

use App\Models\Comment;
use App\Models\Notification;

class CommentObserver
{
  public function created(Comment $comment): void
  {
    $recipientIds = [];

    $post = $comment->post;
    if ($post) {
      $recipientIds[] = $post->user_id;
    }

    $parentComment = null;
    if ($comment->parent_id) {
      $parentComment = Comment::find($comment->parent_id);

      if ($parentComment) {
        $recipientIds[] = $parentComment->user_id;
      }
    }

    $recipientIds = array_unique($recipientIds);
    $recipientIds = array_filter(
      $recipientIds,
      fn($userId) => $userId !== $comment->user_id
    );

    foreach ($recipientIds as $userId) {
      Notification::create([
        'user_id' => $userId,
        'actor_id' => $comment->user_id,
        'type' => $comment->parent_id ? 'comment_reply' : 'comment',
        'post_id' => $comment->post_id,
        'comment_id' => $comment->parent_id ? $parentComment?->id : $comment->id,
      ]);
    }
  }
}
