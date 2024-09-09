<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');
            $table->string('title');
            $table->string('description');
            $table->string('thumbnail_path')->nullable();
            $table->string('watermark_path')->nullable();
            $table->integer('duration');
            $table->enum('status', ['processing', 'processed', 'failed']);
            $table->unsignedBigInteger('user_id');
            $table->json('metadata');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
