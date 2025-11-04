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
    Schema::create('loyalty_transactions', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('user_id');
      $table->decimal('amount', 10, 2);
      $table->string('type');
      $table->integer('points_earned');
      $table->timestamp('created_at');

      // Index for user_id
      $table->index('user_id');

      // Partition by month
      $table->string('partition_key')->virtualAs("DATE_FORMAT(created_at, '%Y%m')")->index();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('loyalty_transactions');
  }
};
