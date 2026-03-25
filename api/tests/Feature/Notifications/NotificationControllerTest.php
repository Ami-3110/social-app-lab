<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
  use RefreshDatabase;

  public function test_guest_cannot_access_notification_endpoints(): void
  {
    $this->getJson('/api/notifications')->assertUnauthorized();

    $this->getJson('/api/notifications/unread-count')->assertUnauthorized();

    $this->patchJson('/api/notifications/read-all')->assertUnauthorized();
  }

  
  public function test_index_returns_only_authenticated_users_notifications_with_relations(): void
  {
    /** @var User $user */
    $user = User::factory()->createOne();
    /** @var User $otherUser */
    $otherUser = User::factory()->createOne();
    /** @var User $actor */
    $actor = User::factory()->createOne();

    $post = Post::factory()->create([
      'user_id' => $actor->id,
    ]);

    $comment = Comment::factory()->create([
      'user_id' => $actor->id,
      'post_id' => $post->id,
    ]);

    $myNotification = Notification::factory()->create([
      'user_id' => $user->id,
      'actor_id' => $actor->id,
      'post_id' => $post->id,
      'comment_id' => $comment->id,
      'read_at' => null,
    ]);

    Notification::factory()->create([
      'user_id' => $otherUser->id,
      'actor_id' => $actor->id,
      'post_id' => $post->id,
      'comment_id' => $comment->id,
      'read_at' => null,
    ]);

    $response = $this->actingAs($user)->getJson('/api/notifications');

    $response->assertOk()
      ->assertJsonStructure([
        'current_page',
        'data' => [
          '*' => [
            'id',
            'user_id',
            'actor_id',
            'post_id',
            'comment_id',
            'read_at',
            'actor',
            'post',
            'comment',
          ],
        ],
        'per_page',
        'total',
      ]);

    $data = $response->json('data');

    $this->assertCount(1, $data);
    $this->assertSame($myNotification->id, $data[0]['id']);
    $this->assertSame($actor->id, $data[0]['actor']['id']);
    $this->assertSame($post->id, $data[0]['post']['id']);
    $this->assertSame($comment->id, $data[0]['comment']['id']);
  }

  
  public function test_unread_count_returns_only_unread_count_for_authenticated_user(): void
  {
    /** @var User $user */
    $user = User::factory()->createOne();
    /** @var User $otherUser */
    $otherUser = User::factory()->createOne();

    Notification::factory()->count(2)->create([
      'user_id' => $user->id,
      'read_at' => null,
    ]);

    Notification::factory()->create([
      'user_id' => $user->id,
      'read_at' => now(),
    ]);

    Notification::factory()->count(3)->create([
      'user_id' => $otherUser->id,
      'read_at' => null,
    ]);

    $response = $this->actingAs($user)->getJson('/api/notifications/unread-count');

    $response->assertOk()
      ->assertJson([
        'count' => 2,
      ]);
  }


  public function test_read_all_marks_only_authenticated_users_unread_notifications_as_read(): void
  {
    /** @var User $user */
    $user = User::factory()->createOne();
    /** @var User $otherUser */
    $otherUser = User::factory()->createOne();

    $myUnread1 = Notification::factory()->create([
      'user_id' => $user->id,
      'read_at' => null,
    ]);

    $myUnread2 = Notification::factory()->create([
      'user_id' => $user->id,
      'read_at' => null,
    ]);

    $myRead = Notification::factory()->create([
      'user_id' => $user->id,
      'read_at' => now()->subDay(),
    ]);

    $othersUnread = Notification::factory()->create([
      'user_id' => $otherUser->id,
      'read_at' => null,
    ]);

    $response = $this->actingAs($user)->patchJson('/api/notifications/read-all');

    $response->assertOk();

    $this->assertNotNull($myUnread1->fresh()->read_at);
    $this->assertNotNull($myUnread2->fresh()->read_at);

    $this->assertNotNull($myRead->fresh()->read_at);
    $this->assertTrue($myRead->fresh()->read_at->equalTo($myRead->read_at));

    $this->assertNull($othersUnread->fresh()->read_at);
  }
}
