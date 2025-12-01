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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // AUTO_INCREMENT
            $table->string('customer_code', 7)->unique(); // UN00001形式
            $table->string('name', 50);
            $table->string('email', 100)->unique();
            $table->string('tel', 11)->nullable();
            $table->string('post_code', 7)->nullable();
            $table->string('address', 200)->nullable();
            $table->integer('age')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
