<?php

namespace Tests\Feature\Notifications;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Like;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LikeNotificationTest extends TestCase
{
  use RefreshDatabase;

  #[Test]
  public function it_creates_a_notification_when_liking_a_post(): void
  {
    $postOwner = User::factory()->create();
    $actor = User::factory()->create();

    $post = Post::factory()->create([
      'user_id' => $postOwner->id,
    ]);

    Like::factory()->create([
      'user_id' => $actor->id,
      'post_id' => $post->id,
    ]);

    $this->assertDatabaseHas('notifications', [
      'user_id' => $postOwner->id,
      'actor_id' => $actor->id,
      'type' => 'like',
      'post_id' => $post->id,
    ]);
  }

  #[Test]
  public function it_does_not_create_a_notification_when_liking_own_post(): void
  {
    $user = User::factory()->create();

    $post = Post::factory()->create([
      'user_id' => $user->id,
    ]);

    Like::factory()->create([
      'user_id' => $user->id,
      'post_id' => $post->id,
    ]);

    $this->assertDatabaseCount('notifications', 0);
  }

  #[Test]
  public function it_does_not_create_a_notification_for_unrelated_users_when_liking_a_post(): void
  {
    $postOwner = User::factory()->create();
    $actor = User::factory()->create();
    $otherUser = User::factory()->create();

    $post = Post::factory()->create([
      'user_id' => $postOwner->id,
    ]);

    Like::factory()->create([
      'user_id' => $actor->id,
      'post_id' => $post->id,
    ]);

    $this->assertDatabaseHas('notifications', [
      'user_id' => $postOwner->id,
      'actor_id' => $actor->id,
      'type' => 'like',
      'post_id' => $post->id,
    ]);

    $this->assertDatabaseMissing('notifications', [
      'user_id' => $otherUser->id,
      'actor_id' => $actor->id,
      'type' => 'like',
      'post_id' => $post->id,
    ]);

    $this->assertDatabaseCount('notifications', 1);
  }

  #[Test]
  public function it_creates_one_notification_when_liking_a_post(): void
  {
    $postOwner = User::factory()->create();
    $actor = User::factory()->create();

    $post = Post::factory()->create([
      'user_id' => $postOwner->id,
    ]);

    Like::factory()->create([
      'user_id' => $actor->id,
      'post_id' => $post->id,
    ]);

    $this->assertDatabaseCount('notifications', 1);
  }
}
