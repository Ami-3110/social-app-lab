<?php

namespace Tests\Feature\Notifications;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostRepostNotificationTest extends TestCase
{
  use RefreshDatabase;

  #[Test]
  public function it_creates_a_notification_when_reposting_a_post(): void
  {
    $originalPostOwner = User::factory()->create();
    $actor = User::factory()->create();

    $originalPost = Post::factory()->create([
      'user_id' => $originalPostOwner->id,
    ]);

    Post::factory()->create([
      'user_id' => $actor->id,
      'repost_of_post_id' => $originalPost->id,
      'quote_body' => null,
    ]);

    $this->assertDatabaseHas('notifications', [
      'user_id' => $originalPostOwner->id,
      'actor_id' => $actor->id,
      'type' => 'repost',
      'post_id' => $originalPost->id,
    ]);
  }

  #[Test]
  public function it_does_not_create_a_notification_when_reposting_own_post(): void
  {
    $user = User::factory()->create();

    $originalPost = Post::factory()->create([
      'user_id' => $user->id,
    ]);

    Post::factory()->create([
      'user_id' => $user->id,
      'repost_of_post_id' => $originalPost->id,
      'quote_body' => null,
    ]);

    $this->assertDatabaseCount('notifications', 0);
  }

  #[Test]
  public function it_does_not_create_a_notification_for_unrelated_users_when_reposting_a_post(): void
  {
    $originalPostOwner = User::factory()->create();
    $actor = User::factory()->create();
    $otherUser = User::factory()->create();

    $originalPost = Post::factory()->create([
      'user_id' => $originalPostOwner->id,
    ]);

    Post::factory()->create([
      'user_id' => $actor->id,
      'repost_of_post_id' => $originalPost->id,
      'quote_body' => null,
    ]);

    $this->assertDatabaseHas('notifications', [
      'user_id' => $originalPostOwner->id,
      'actor_id' => $actor->id,
      'type' => 'repost',
      'post_id' => $originalPost->id,
    ]);

    $this->assertDatabaseMissing('notifications', [
      'user_id' => $otherUser->id,
      'actor_id' => $actor->id,
      'type' => 'repost',
      'post_id' => $originalPost->id,
    ]);

    $this->assertDatabaseCount('notifications', 1);
  }

  #[Test]
  public function it_creates_one_notification_when_reposting_a_post(): void
  {
    $originalPostOwner = User::factory()->create();
    $actor = User::factory()->create();

    $originalPost = Post::factory()->create([
      'user_id' => $originalPostOwner->id,
    ]);

    Post::factory()->create([
      'user_id' => $actor->id,
      'repost_of_post_id' => $originalPost->id,
      'quote_body' => null,
    ]);

    $this->assertDatabaseCount('notifications', 1);
  }

  #[Test]
  public function it_creates_a_notification_when_quoting_a_post(): void
  {
    $originalPostOwner = User::factory()->create();
    $actor = User::factory()->create();

    $originalPost = Post::factory()->create([
      'user_id' => $originalPostOwner->id,
    ]);

    Post::factory()->create([
      'user_id' => $actor->id,
      'repost_of_post_id' => $originalPost->id,
      'quote_body' => 'quoted text',
    ]);

    $this->assertDatabaseHas('notifications', [
      'user_id' => $originalPostOwner->id,
      'actor_id' => $actor->id,
      'type' => 'quote',
      'post_id' => $originalPost->id,
    ]);
  }


  #[Test]
  public function it_does_not_create_a_notification_when_quoting_own_post(): void
  {
    $user = User::factory()->create();

    $originalPost = Post::factory()->create([
      'user_id' => $user->id,
    ]);

    Post::factory()->create([
      'user_id' => $user->id,
      'repost_of_post_id' => $originalPost->id,
      'quote_body' => 'quoted text',
    ]);

    $this->assertDatabaseCount('notifications', 0);
  }

  #[Test]
  public function it_does_not_create_a_notification_for_unrelated_users_when_quoting_a_post(): void
  {
    $originalPostOwner = User::factory()->create();
    $actor = User::factory()->create();
    $otherUser = User::factory()->create();

    $originalPost = Post::factory()->create([
      'user_id' => $originalPostOwner->id,
    ]);

    Post::factory()->create([
      'user_id' => $actor->id,
      'repost_of_post_id' => $originalPost->id,
      'quote_body' => 'quoted text',
    ]);

    $this->assertDatabaseHas('notifications', [
      'user_id' => $originalPostOwner->id,
      'actor_id' => $actor->id,
      'type' => 'quote',
      'post_id' => $originalPost->id,
    ]);

    $this->assertDatabaseMissing('notifications', [
      'user_id' => $otherUser->id,
      'actor_id' => $actor->id,
      'type' => 'quote',
      'post_id' => $originalPost->id,
    ]);

    $this->assertDatabaseCount('notifications', 1);
  }

  #[Test]
  public function it_creates_one_notification_when_quoting_a_post(): void
  {
    $originalPostOwner = User::factory()->create();
    $actor = User::factory()->create();

    $originalPost = Post::factory()->create([
      'user_id' => $originalPostOwner->id,
    ]);

    Post::factory()->create([
      'user_id' => $actor->id,
      'repost_of_post_id' => $originalPost->id,
      'quote_body' => 'quoted text',
    ]);

    $this->assertDatabaseCount('notifications', 1);
  }
}
