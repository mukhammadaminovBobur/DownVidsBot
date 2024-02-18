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
        Schema::create('d_v_b_users', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('step')->default("New");
            $table->string('admin')->default(false);
            $table->string('lang')->nullable();
            $table->boolean('music')->default(true);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('d_v_b_users');
    }
};
