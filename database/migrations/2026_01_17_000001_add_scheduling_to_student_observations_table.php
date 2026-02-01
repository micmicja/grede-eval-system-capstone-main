<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_observations', function (Blueprint $table) {
            $table->timestamp('scheduled_at')->nullable()->after('referred_to_councilor');
            $table->string('counseling_status')->default('pending')->after('scheduled_at'); // pending, scheduled, ongoing, completed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_observations', function (Blueprint $table) {
            $table->dropColumn(['scheduled_at', 'counseling_status']);
        });
    }
};
