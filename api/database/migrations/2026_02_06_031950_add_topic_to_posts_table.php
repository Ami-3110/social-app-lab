<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('posts', function (Blueprint $table) {
      $table->string('topic', 100)->nullable()->index()->after('body');
    });
  }

  public function down(): void
  {
    Schema::table('posts', function (Blueprint $table) {
      $table->dropIndex(['topic']);
      $table->dropColumn('topic');
    });
  }
};
