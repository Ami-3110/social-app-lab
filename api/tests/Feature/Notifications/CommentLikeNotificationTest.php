<?php

namespace Tests\Feature\Notifications;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\CommentLike;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentLikeNotificationTest extends TestCase
{
  use RefreshDatabase;

  #[Test]
  public function it_creates_a_notification_when_liking_a_comment(): void
  {
    $commentOwner = User::factory()->create();
    $actor = User::factory()->create();

    $post = Post::factory()->create([
      'user_id' => $commentOwner->id,
    ]);

    $comment = Comment::factory()->create([
      'user_id' => $commentOwner->id,
      'post_id' => $post->id,
    ]);

    CommentLike::factory()->create([
      'user_id' => $actor->id,
      'comment_id' => $comment->id,
    ]);

    $this->assertDatabaseHas('notifications', [
      'user_id' => $commentOwner->id,
      'actor_id' => $actor->id,
      'type' => 'comment_like',
      'comment_id' => $comment->id,
    ]);
  }

  #[Test]
  public function it_does_not_create_a_notification_when_liking_own_comment(): void
  {
    $user = User::factory()->create();

    $post = Post::factory()->create([
      'user_id' => $user->id,
    ]);

    $comment = Comment::factory()->create([
      'user_id' => $user->id,
      'post_id' => $post->id,
    ]);

    CommentLike::factory()->create([
      'user_id' => $user->id,
      'comment_id' => $comment->id,
    ]);

    $this->assertDatabaseCount('notifications', 0);
  }

  #[Test]
  public function it_creates_one_notification_when_liking_a_comment(): void
  {
    $commentOwner = User::factory()->create();
    $actor = User::factory()->create();

    $post = Post::factory()->create([
      'user_id' => $commentOwner->id,
    ]);

    $comment = Comment::factory()->create([
      'user_id' => $commentOwner->id,
      'post_id' => $post->id,
    ]);

    CommentLike::factory()->create([
      'user_id' => $actor->id,
      'comment_id' => $comment->id,
    ]);

    $this->assertDatabaseCount('notifications', 1);
  }
}
