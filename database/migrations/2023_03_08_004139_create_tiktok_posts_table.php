<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTiktokPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tiktok_posts', function (Blueprint $table) {
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
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tiktok_posts');
    }
}
