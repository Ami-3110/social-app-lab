<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
  public function definition(): array
  {
    return [
      'user_id' => User::factory(),
      'title' =>fake()->sentence(),
      'body' => fake()->sentence(),
      'topic' => null,
      'repost_of_post_id' => null,
      'quote_body' => null,
      'repost_of_comment_id' => null,
    ];
  }
}
