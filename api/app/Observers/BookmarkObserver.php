<?php

namespace App\Observers;

use App\Models\Bookmark;
use App\Models\Notification;

class BookmarkObserver
{
  public function created(Bookmark $bookmark): void
  {
    $post = $bookmark->post;

    if (! $post || $post->user_id === $bookmark->user_id) {
      return;
    }

    Notification::create([
      'user_id' => $post->user_id,
      'actor_id' => $bookmark->user_id,
      'type' => 'bookmark',
      'post_id' => $bookmark->post_id,
    ]);
  }
}
