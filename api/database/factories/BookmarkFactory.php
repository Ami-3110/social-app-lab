<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookmarkFactory extends Factory
{
  public function definition(): array
  {
    return [
      'user_id' => User::factory(),
      'post_id' => Post::factory(),
    ];
  }
}
