<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('video_code');
            $table->string('video_file_path');
            $table->string('title');
            $table->string('description');
            $table->string('thumbnail_image_path')->nullable();
            $table->enum('watermark_type', ['text', 'image'])->nullable();
            $table->string('watermark_text')->nullable();
            $table->string('watermark_path')->nullable();
            $table->enum('watermark_position', ['top-left', 'top-right', 'bottom-left', 'bottom-right'])->nullable();
            $table->enum('status', ['processing', 'processed', 'failed'])->default('processing');
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
