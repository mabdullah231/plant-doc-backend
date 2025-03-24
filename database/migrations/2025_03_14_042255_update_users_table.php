<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('users', function (Blueprint $table) {
            // Add the user_type column with enum values
            $table->enum('user_type', ['0', '1', '2'])->default('2')->after('email')->comment('0: Admin, 1: Subadmin, 2: Regular User');

            // Add the code and is_active columns
            $table->string('code')->unique()->after('id')->nullable();
            $table->boolean('is_active')->default(true)->after('code');
        });
    }

    public function down() {
        Schema::table('users', function (Blueprint $table) {
            // Drop the user_type column
            $table->dropColumn('user_type');

            // Drop the code and is_active columns
            $table->dropColumn(['code', 'is_active']);
        });
    }
};