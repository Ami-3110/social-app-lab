<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('posts', function (Blueprint $table) {
      $table->foreignId('repost_of_comment_id')
        ->nullable()
        ->constrained('comments')
        ->nullOnDelete();

      // 1ユーザーが同じコメントを複数回TLに出さないため（重要）
      $table->unique(['user_id', 'repost_of_comment_id']);
    });
  }

  public function down(): void
  {
    Schema::table('posts', function (Blueprint $table) {
      $table->dropUnique(['user_id', 'repost_of_comment_id']);
      $table->dropConstrainedForeignId('repost_of_comment_id');
    });
  }
};
