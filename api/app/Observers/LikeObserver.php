<?php

namespace App\Observers;

use App\Models\Like;
use App\Models\Notification;

class LikeObserver
{
  public function created(Like $like): void
  {
    $post = $like->post;

    if (! $post || $post->user_id === $like->user_id) {
      return;
    }

    Notification::create([
      'user_id' => $post->user_id,
      'actor_id' => $like->user_id,
      'type' => 'like',
      'post_id' => $like->post_id,
    ]);
  }
}
