<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
  public function up(): void
  {
    // SQLiteの現行postsテーブルには
    // user_id + repost_of_comment_id のunique制約が存在しないため何もしない
  }

  public function down(): void
  {
    // 元に戻す処理も不要
  }
};
