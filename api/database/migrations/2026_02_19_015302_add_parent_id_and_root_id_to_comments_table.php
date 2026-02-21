<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
          // 返信先（直上の親）
          $table->foreignId('parent_id')
            ->nullable()
            ->after('post_id')
            ->constrained('comments')
            ->cascadeOnDelete();

          // 会話スレッドの起点（トップレベルコメントのid）
          $table->foreignId('root_id')
            ->nullable()
            ->after('parent_id')
            ->constrained('comments')
            ->cascadeOnDelete();

          // 取得高速化（スレッド表示・返信数）
          $table->index(['post_id', 'parent_id', 'created_at']);
          $table->index(['post_id', 'root_id', 'created_at']);
          $table->index(['root_id', 'created_at']);
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
          // index -> FK -> column の順で落とす
          $table->dropIndex(['post_id', 'parent_id', 'created_at']);
          $table->dropIndex(['post_id', 'root_id', 'created_at']);
          $table->dropIndex(['root_id', 'created_at']);

          $table->dropConstrainedForeignId('root_id');
          $table->dropConstrainedForeignId('parent_id');
        });
    }
};
