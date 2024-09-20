<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('view_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('video_id');
            $table->string('ip_address');
            $table->string('user_agent');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('view_logs');
    }
};
