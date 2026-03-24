<?php

namespace App\Observers;

use App\Models\Post;
use App\Models\Comment;
use App\Models\Notification;

class PostObserver
{
  public function created(Post $post): void
  {
    if ($post->repost_of_post_id && $post->quote_body) {
      $originalPost = Post::find($post->repost_of_post_id);

      if ($originalPost && $originalPost->user_id !== $post->user_id) {
        Notification::create([
          'user_id' => $originalPost->user_id,
          'actor_id' => $post->user_id,
          'type' => 'quote',
          'post_id' => $originalPost->id,
        ]);
      }

      return;
    }

    if ($post->repost_of_post_id) {
      $originalPost = Post::find($post->repost_of_post_id);

      if ($originalPost && $originalPost->user_id !== $post->user_id) {
        Notification::create([
          'user_id' => $originalPost->user_id,
          'actor_id' => $post->user_id,
          'type' => 'repost',
          'post_id' => $originalPost->id,
        ]);
      }

      return;
    }

    if ($post->repost_of_comment_id && $post->quote_body) {
      $comment = Comment::find($post->repost_of_comment_id);

      if ($comment && $comment->user_id !== $post->user_id) {
        Notification::create([
          'user_id' => $comment->user_id,
          'actor_id' => $post->user_id,
          'type' => 'comment_quote',
          'comment_id' => $comment->id,
          'post_id' => $post->id,
        ]);
      }

      return;
    }

    if ($post->repost_of_comment_id) {
      $comment = Comment::find($post->repost_of_comment_id);

      if ($comment && $comment->user_id !== $post->user_id) {
        Notification::create([
          'user_id' => $comment->user_id,
          'actor_id' => $post->user_id,
          'type' => 'comment_repost',
          'comment_id' => $comment->id,
          'post_id' => $post->id,
        ]);
      }

      return;
    }

  }
}
