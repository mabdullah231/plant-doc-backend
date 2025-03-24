<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('user_reports', function (Blueprint $table) {
            // Drop foreign key constraint first
            
            // Now drop the column safely
            $table->dropColumn([

                'treatment_recommendations',
                'severity_level',
                'question_ids',
                'answer_ids',
            ]);
        });
    }

    public function down()
    {
        Schema::table('user_reports', function (Blueprint $table) {
            $table->text('treatment_recommendations')->nullable();
            $table->string('severity_level')->nullable();
            $table->json('question_ids')->nullable();
            $table->json('answer_ids')->nullable();
        });
    }
};
