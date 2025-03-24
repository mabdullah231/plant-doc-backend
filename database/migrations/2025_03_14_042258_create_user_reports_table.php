<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('user_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plant_type_id')->constrained('plant_types')->onDelete('cascade');
            $table->json('symptom_details');
            $table->text('ai_diagnosis');
            $table->text('treatment_recommendations');
            $table->enum('severity_level', ['Mild', 'Moderate', 'Severe']);
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('user_reports');
    }
};
