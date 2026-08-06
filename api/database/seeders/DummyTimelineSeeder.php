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
        ['name' => 'Demo',      'email' => 'demo@example.com'],
        ['name' => 'Mika',     'email' => 'mika@example.com'],
        ['name' => 'Ken',      'email' => 'ken@example.com'],
        ['name' => 'Yui',      'email' => 'yui@example.com'],
        ['name' => 'Taro',     'email' => 'taro@example.com'],
      ];

      $topics = ['seeder', 'laravel', 'nuxt', 'diving', 'random'];

      // 投稿文のネタ
      $titleSeeds = [
          '新しい機能を追加しました',
          '今日の学び',
          '週末の記録',
          '最近気になっていること',
          'ちょっとした近況',
          '作業メモ',
          'おすすめの場所',
          '今日のひとこと',
          '開発の進捗',
          '次に試したいこと',
      ];

      $bodySeeds = [
          'タイムラインの表示を少し調整しました。',
          '今日はLaravelのリレーションについて学びました。',
          '天気が良かったので、久しぶりに散歩してきました。',
          '少しずつ使いやすい画面になってきています。',
          'コーヒーを飲みながら作業しています。',
          '細かい部分をリファクタリングしました。',
          'シンプルで分かりやすい設計を意識しています。',
          '小さな改善を積み重ねています。',
          '次はテストコードも追加したいです。',
          '焦らず一歩ずつ進めます。',
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
          ->delete();

      // ユーザーごとに5投稿ずつ作成
      $now = now();
      foreach ($createdUsers as $idx => $user) {
        for ($i = 1; $i <= 5; $i++) {
          $title = $titleSeeds[($idx + $i) % count($titleSeeds)];
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
        ['demo@example.com', 'mika@example.com'],
        ['demo@example.com', 'ken@example.com'],
        ['mika@example.com', 'yui@example.com'],
        ['ken@example.com',  'taro@example.com'],
        ['yui@example.com',  'demo@example.com'],
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