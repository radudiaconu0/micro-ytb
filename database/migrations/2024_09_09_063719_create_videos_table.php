<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('video_code')->unique();
            $table->string('original_s3_key')->unique();
            $table->string('processed_s3_key')->nullable()->unique();

            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['processing', 'processed', 'failed'])->default('processing');
            $table->unsignedBigInteger('user_id');
            $table->json('metadata')->nullable();

            $table->enum('watermark_type', ['text', 'image'])->nullable();
            $table->string('watermark_content')->nullable();
            $table->enum('watermark_position', ['top-left', 'top-right', 'bottom-left', 'bottom-right'])->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
