<?php

namespace Tests\Feature\Notifications;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentRepostNotificationTest extends TestCase
{
  use RefreshDatabase;

  #[Test]
  public function it_creates_a_notification_when_reposting_a_comment(): void
  {
    $originalCommentOwner = User::factory()->create();
    $actor = User::factory()->create();

    $post = Post::factory()->create([
      'user_id' => $originalCommentOwner->id,
    ]);

    $originalComment = Comment::factory()->create([
      'user_id' => $originalCommentOwner->id,
      'post_id' => $post->id,
    ]);

    Post::factory()->create([
      'user_id' => $actor->id,
      'repost_of_comment_id' => $originalComment->id,
      'quote_body' => null,
    ]);

    $this->assertDatabaseHas('notifications', [
      'user_id' => $originalCommentOwner->id,
      'actor_id' => $actor->id,
      'type' => 'comment_repost',
      'comment_id' => $originalComment->id,
    ]);
  }

  #[Test]
  public function it_does_not_create_a_notification_when_reposting_own_comment(): void
  {
    $actor = User::factory()->create();

    $post = Post::factory()->create([
      'user_id' => $actor->id,
    ]);

    $originalComment = Comment::factory()->create([
      'user_id' => $actor->id,
      'post_id' => $post->id,
    ]);

    Post::factory()->create([
      'user_id' => $actor->id,
      'repost_of_comment_id' => $originalComment->id,
      'quote_body' => null,
    ]);

    $this->assertDatabaseCount('notifications', 0);
  }

  #[Test]
  public function it_does_not_create_a_notification_for_unrelated_users_when_reposting_a_comment(): void 
  {
    $originalCommentOwner = User::factory()->create();
    $actor = User::factory()->create();
    $otherUser = User::factory()->create();

    $post = Post::factory()->create([
      'user_id' => $originalCommentOwner->id,
    ]);

    $originalComment = Comment::factory()->create([
      'user_id' => $originalCommentOwner->id,
      'post_id' => $post->id,
    ]);

    $repost = Post::factory()->create([
      'user_id' => $actor->id,
      'repost_of_comment_id' => $originalComment->id,
      'quote_body' => null,
    ]);

    $this->assertDatabaseHas('notifications', [
      'user_id' => $originalCommentOwner->id,
      'actor_id' => $actor->id,
      'type' => 'comment_repost',
      'post_id' => $repost->id,
      'comment_id' => $originalComment->id,
    ]);

    $this->assertDatabaseMissing('notifications', [
      'user_id' => $otherUser->id,
      'actor_id' => $actor->id,
      'type' => 'repost_comment',
      'post_id' => $originalComment->id,
    ]);

    $this->assertDatabaseCount('notifications', 1);
  }

  #[Test]
  public function it_creates_one_notification_when_reposting_a_comment(): void 
  {
    $originalCommentOwner = User::factory()->create();
    $actor = User::factory()->create();

    $post = Post::factory()->create([
      'user_id' => $originalCommentOwner->id,
    ]);

    $originalComment = Comment::factory()->create([
      'user_id' => $originalCommentOwner->id,
      'post_id' => $post->id,
    ]);

    Post::factory()->create([
      'user_id' => $actor->id,
      'repost_of_comment_id' => $originalComment->id,
      'quote_body' => null,
    ]);

    $this->assertDatabaseCount('notifications', 1);
  }

}