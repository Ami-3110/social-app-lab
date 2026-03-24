<?php

namespace Tests\Feature\Notifications;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentNotificationTest extends TestCase
{
  use RefreshDatabase;

  #[Test]
  public function it_creates_a_notification_when_commenting_on_a_post(): void
  {
    $postOwner = User::factory()->create();
    $actor = User::factory()->create();

    $post = Post::factory()->create([
      'user_id' => $postOwner->id,
    ]);

    Comment::factory()->create([
      'user_id' => $actor->id,
      'post_id' => $post->id,
    ]);

    $this->assertDatabaseHas('notifications', [
      'user_id' => $postOwner->id,
      'actor_id' => $actor->id,
      'type' => 'comment',
      'post_id' => $post->id,
    ]);
  }

  #[Test]
  public function it_creates_notifications_for_post_owner_and_parent_comment_owner_when_replying_to_a_comment(): void
  {
    $postOwner = User::factory()->create();
    $parentCommentOwner = User::factory()->create();
    $actor = User::factory()->create();

    $post = Post::factory()->create([
      'user_id' => $postOwner->id,
    ]);

    $parentComment = Comment::withoutEvents(function () use ($parentCommentOwner, $post) {
      return Comment::factory()->create([
        'user_id' => $parentCommentOwner->id,
        'post_id' => $post->id,
      ]);
    });

    Comment::factory()->create([
      'user_id' => $actor->id,
      'post_id' => $post->id,
      'parent_id' => $parentComment->id,
    ]);

    $this->assertDatabaseHas('notifications', [
      'user_id' => $postOwner->id,
      'actor_id' => $actor->id,
      'type' => 'comment_reply',
      'post_id' => $post->id,
    ]);

    $this->assertDatabaseHas('notifications', [
      'user_id' => $parentCommentOwner->id,
      'actor_id' => $actor->id,
      'type' => 'comment_reply',
      'post_id' => $post->id,
      'comment_id' => $parentComment->id,
    ]);

    $this->assertDatabaseCount('notifications', 2);
  }

  #[Test]
  public function it_does_not_create_a_notification_when_commenting_on_own_post(): void
  {
    $user = User::factory()->create();

    $post = Post::factory()->create([
      'user_id' => $user->id,
    ]);

    Comment::factory()->create([
      'user_id' => $user->id,
      'post_id' => $post->id,
    ]);

    $this->assertDatabaseCount('notifications', 0);
  }

  #[Test]
  public function it_does_not_create_a_notification_when_replying_to_own_comment(): void
  {
    $user = User::factory()->create();
    $parentCommentOwner = User::factory()->create();

    $post = Post::factory()->create([
      'user_id' => $user->id,
    ]);

    $parentComment = Comment::withoutEvents(function () use ($parentCommentOwner, $post) {
      return Comment::factory()->create([
        'user_id' => $parentCommentOwner->id,
        'post_id' => $post->id,
      ]);
    });

    Comment::factory()->create([
      'user_id' => $user->id,
      'post_id' => $post->id,
      'parent_id' => $parentComment->id,
    ]);

    $this->assertDatabaseCount('notifications', 1);
  }

  #[Test]
  public function it_does_not_create_a_notification_for_unrelated_users(): void
  {
    $postOwner = User::factory()->create();
    $actor = User::factory()->create();
    $otherUser = User::factory()->create();

    $post = Post::factory()->create([
      'user_id' => $postOwner->id,
    ]);

    Comment::factory()->create([
      'user_id' => $actor->id,
      'post_id' => $post->id,
    ]);

    $this->assertDatabaseHas('notifications', [
      'user_id' => $postOwner->id,
      'actor_id' => $actor->id,
      'type' => 'comment',
      'post_id' => $post->id,
    ]);

    $this->assertDatabaseMissing('notifications', [
      'user_id' => $otherUser->id,
      'actor_id' => $actor->id,
      'type' => 'comment',
      'post_id' => $post->id,
    ]);

    $this->assertDatabaseCount('notifications', 1);
  }

  #[Test]
  public function it_creates_one_notification_when_commenting_on_a_post(): void
  {
    $postOwner = User::factory()->create();
    $actor = User::factory()->create();

    $post = Post::factory()->create([
      'user_id' => $postOwner->id,
    ]);

    Comment::factory()->create([
      'user_id' => $actor->id,
      'post_id' => $post->id,
    ]);

    $this->assertDatabaseCount('notifications', 1);
  }

  #[Test]
  public function it_creates_notifications_for_each_relevant_user_when_replying_to_a_comment(): void
  {
    $parentCommentOwner = User::factory()->create();
    $commentOwner = User::factory()->create();
    $actor = User::factory()->create();

    $post = Post::factory()->create([
      'user_id' => $commentOwner->id,
    ]);

    $parentComment = Comment::withoutEvents(function () use ($parentCommentOwner, $post) {
      return Comment::factory()->create([
        'user_id' => $parentCommentOwner->id,
        'post_id' => $post->id,
      ]);
    });

    Comment::factory()->create([
      'user_id' => $actor->id,
      'post_id' => $post->id,
      'parent_id' => $parentComment->id,
    ]);

    $this->assertDatabaseCount('notifications', 2);
  }

  #[Test]
  public function it_does_not_create_duplicate_notifications_when_post_owner_and_parent_comment_owner_are_the_same(): void
  {
    $user = User::factory()->create();
    $actor = User::factory()->create();

    $post = Post::factory()->create([
      'user_id' => $user->id,
    ]);

    $parentComment = Comment::withoutEvents(function () use ($user, $post) {
      return Comment::factory()->create([
        'user_id' => $user->id,
        'post_id' => $post->id,
      ]);
    });

    Comment::factory()->create([
      'user_id' => $actor->id,
      'post_id' => $post->id,
      'parent_id' => $parentComment->id,
    ]);

    $this->assertDatabaseHas('notifications', [
      'user_id' => $user->id,
      'actor_id' => $actor->id,
      'type' => 'comment_reply',
      'post_id' => $post->id,
    ]);

    $this->assertDatabaseCount('notifications', 1);
  }
}
