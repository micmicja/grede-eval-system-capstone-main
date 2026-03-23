<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Department;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add department_id column (nullable initially)
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('role');
        });

        // Migrate existing data: map department string to department_id
        $departmentMap = Department::all()->pluck('id', 'code');

        User::whereNotNull('department')->chunk(100, function ($users) use ($departmentMap) {
            foreach ($users as $user) {
                if (isset($departmentMap[$user->department])) {
                    $user->department_id = $departmentMap[$user->department];
                    $user->save();
                }
            }
        });

        // Add foreign key constraint
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('department_id')
                  ->references('id')
                  ->on('departments')
                  ->onDelete('set null');
        });

        // Drop old department column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore department column
        Schema::table('users', function (Blueprint $table) {
            $table->string('department')->nullable()->after('role');
        });

        // Migrate data back
        $departments = Department::all()->keyBy('id');

        User::whereNotNull('department_id')->chunk(100, function ($users) use ($departments) {
            foreach ($users as $user) {
                if (isset($departments[$user->department_id])) {
                    $user->department = $departments[$user->department_id]->code;
                    $user->save();
                }
            }
        });

        // Drop foreign key and column
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
