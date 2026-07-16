<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $wd = Department::create([
            'name' => 'Web Development',
            'code' => 'WD',
        ]);

        $crd = Department::create([
            'name' => 'Creative Design',
            'code' => 'CRD',
        ]);

        $smm = Department::create([
            'name' => 'Social Media & Marketing',
            'code' => 'SMM',
        ]);

        $this->command?->info('Seeded 3 Departments WD, CRD & SMM');
    }
}
