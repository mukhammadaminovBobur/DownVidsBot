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
        Schema::create('d_v_b_tiktoks', function (Blueprint $table) {
            $table->id();
            $table->string('tiktok_id');
            $table->longText('images')->nullable();
            $table->string('video')->nullable();
            $table->string('audio')->nullable();
            $table->bigInteger('downloads')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('d_v_b_tiktoks');
    }
};
