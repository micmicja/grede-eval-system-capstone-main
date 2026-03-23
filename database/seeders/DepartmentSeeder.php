<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['code' => 'BSCS', 'name' => 'Bachelor of Science in Computer Science'],
            ['code' => 'BAPS', 'name' => 'Bachelor of Arts in Political Science'],
            ['code' => 'BSBA', 'name' => 'Bachelor of Science in Business Administration'],
            ['code' => 'BEED', 'name' => 'Bachelor of Elementary Education'],
            ['code' => 'BSED', 'name' => 'Bachelor of Secondary Education'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }
    }
}
