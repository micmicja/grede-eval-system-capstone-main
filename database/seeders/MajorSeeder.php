<?php

namespace Database\Seeders;

use App\Models\Major;
use App\Models\Department;
use Illuminate\Database\Seeder;

class MajorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get departments
        $bsed = Department::where('code', 'BSED')->first();
        $bsba = Department::where('code', 'BSBA')->first();

        // BSED Majors
        if ($bsed) {
            $bsedMajors = [
                'Social Studies',
                'Filipino',
                'English',
                'Mathematics',
            ];

            foreach ($bsedMajors as $major) {
                Major::create([
                    'department_id' => $bsed->id,
                    'name' => $major,
                ]);
            }
        }

        // BSBA Majors
        if ($bsba) {
            $bsbaMajors = [
                'Management Accounting',
                'Business Management',
            ];

            foreach ($bsbaMajors as $major) {
                Major::create([
                    'department_id' => $bsba->id,
                    'name' => $major,
                ]);
            }
        }
    }
}
