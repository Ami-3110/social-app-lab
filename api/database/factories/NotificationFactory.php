<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
  protected $model = Notification::class;

  public function definition(): array
  {
    return [
      'user_id' => User::factory(),
      'actor_id' => User::factory(),
      'type' => 'post_liked',
      'post_id' => Post::factory(),
      'comment_id' => null,
      'read_at' => null,
    ];
  }
}
