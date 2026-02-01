<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->string('urgency')->nullable()->after('category')->comment('low|mid|high');
        });

        // Backfill existing rows to moderate urgency
        DB::table('evaluations')->whereNull('urgency')->update(['urgency' => 'mid']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropColumn('urgency');
        });
    }
};
