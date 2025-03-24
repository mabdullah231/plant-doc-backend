<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('user_reports', function (Blueprint $table) {
            // Drop the existing symptom_details column
            $table->dropColumn('symptom_details');

            // Add new columns for question_ids and answer_ids
            $table->json('question_ids')->nullable();
            $table->json('answer_ids')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_reports', function (Blueprint $table) {
            // Re-add the symptom_details column
            $table->json('symptom_details')->nullable();

            // Drop the new columns
            $table->dropColumn(['question_ids', 'answer_ids']);
        });
    }
};
