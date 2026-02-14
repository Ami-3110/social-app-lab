<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Post;

class DummyTimelineSeeder extends Seeder
{
  public function run(): void
  {
    DB::transaction(function () {
      // ===== 設定 =====
      $users = [
        ['name' => 'Ami',      'email' => 'ami@example.com'],
        ['name' => 'Mika',     'email' => 'mika@example.com'],
        ['name' => 'Ken',      'email' => 'ken@example.com'],
        ['name' => 'Yui',      'email' => 'yui@example.com'],
        ['name' => 'Taro',     'email' => 'taro@example.com'],
      ];

      $topics = ['seeder', 'laravel', 'nuxt', 'diving', 'random'];

      // 投稿文のネタ（適当でOK）
      $titleSeeds = [
        'Hello world',
        'Small update',
        'Today I learned',
        'Hot take',
        'Weekend vibes',
        'Quick note',
        'Random thought',
        'Mini log',
        'Status',
        'Idea dump',
      ];
      $bodySeeds = [
        'Just testing the timeline.',
        'Trying something new today.',
        'This feature is getting nicer.',
        'Need coffee.',
        'It works on my machine!',
        'Refactoring time.',
        'Keeping it simple.',
        'Shipping small changes.',
        'I should write tests...',
        'One step at a time.',
      ];

      // ===== ユーザー作成（何回実行してもOK）=====
      $createdUsers = [];
      foreach ($users as $u) {
        $createdUsers[] = User::updateOrCreate(
          ['email' => $u['email']],
          [
            'name' => $u['name'],
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
          ]
        );
      }

      // ===== ダミー投稿を毎回作り直す（重複防止）=====
      // 今回のダミー投稿は title が "[Seed]" で始まるものに固定して、それだけ消す
      $dummyUserIds = collect($createdUsers)->pluck('id')->all();

      Post::query()
        ->whereIn('user_id', $dummyUserIds)
        ->where('title', 'like', '[Seed]%')
        ->delete();

      // ユーザーごとに5投稿ずつ作成
      $now = now();
      foreach ($createdUsers as $idx => $user) {
        for ($i = 1; $i <= 5; $i++) {
          $title = "[Seed] {$user->name} #{$i} - " . $titleSeeds[($idx + $i) % count($titleSeeds)];
          $body  = $bodySeeds[($idx * 2 + $i) % count($bodySeeds)];
          $topic = $topics[($idx + $i) % count($topics)];

          Post::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            // topic カラムが無いなら次の行は消してOK
            'topic' => $topic,
            'created_at' => $now->copy()->subMinutes(($idx * 10) + $i),
            'updated_at' => $now->copy()->subMinutes(($idx * 10) + $i),
          ]);
        }
      }

      // ===== フォロー関係（サンプルで適当に作る）=====
      // 例：Amiが Mika/Ken をフォロー、MikaがYui、KenがTaro など
      // unique制約があるので updateOrInsert を使う
      $byEmail = collect($createdUsers)->keyBy('email');

      $pairs = [
        ['ami@example.com',  'mika@example.com'],
        ['ami@example.com',  'ken@example.com'],
        ['mika@example.com', 'yui@example.com'],
        ['ken@example.com',  'taro@example.com'],
        ['yui@example.com',  'ami@example.com'],
      ];

      foreach ($pairs as [$followerEmail, $followingEmail]) {
        $follower = $byEmail[$followerEmail];
        $following = $byEmail[$followingEmail];

        DB::table('follows')->updateOrInsert(
          ['follower_id' => $follower->id, 'following_id' => $following->id],
          ['created_at' => $now, 'updated_at' => $now]
        );
      }
    });
  }
}