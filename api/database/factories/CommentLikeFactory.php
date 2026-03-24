<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentLikeFactory extends Factory
{
  public function definition(): array
  {
    return [
      'user_id' => User::factory(),
      'comment_id' => Comment::factory(),
    ];
  }
}
