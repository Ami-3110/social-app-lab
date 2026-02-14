<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class FollowTestSeeder extends Seeder
{
  public function run(): void
  {
    $me = User::first();

    // 別ユーザー
    $user2 = User::updateOrCreate(
        ['email' => 'followed@example.com'], // 検索条件
        [
            'name' => 'Followed User',
            'password' => bcrypt('password'),
        ]
    );

    // 投稿（Factory使わない）
    Post::create([
      'user_id' => $user2->id,
      'title' => 'Followed user post',
      'body' => 'This is a post from followed user.',
      'topic' => 'seeder',
    ]);

    DB::table('follows')->updateOrInsert(
      [
        'follower_id' => $me->id,
        'following_id' => $user2->id,
      ],
      [
        'created_at' => now(),
        'updated_at' => now(),
      ]
    );
  }
}