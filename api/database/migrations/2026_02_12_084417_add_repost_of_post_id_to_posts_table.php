<?php

// database/migrations/2026_02_12_084417_add_repost_of_post_id_to_posts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('posts', function (Blueprint $table) {
      $table->foreignId('repost_of_post_id')
        ->nullable()
        ->constrained('posts')
        ->nullOnDelete() // 元投稿が消えたら参照だけnull
        ->after('topic'); // topicの後ろに置く（好みで）
    });
  }

  public function down(): void
  {
    Schema::table('posts', function (Blueprint $table) {
      $table->dropConstrainedForeignId('repost_of_post_id');
    });
  }
};
