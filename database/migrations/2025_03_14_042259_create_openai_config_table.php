<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('openai_config', function (Blueprint $table) {
            $table->id();
            $table->string('api_key');
            $table->string('model')->default('gpt-4');
            $table->decimal('temperature', 3, 2)->default(0.7);
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('openai_config');
    }
};
