<?php

namespace Database\Seeders;

use App\Enums\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as SpatiePermission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // foreach (Permission::cases() as $permission) {
        //     SpatiePermission::findOrCreate($permission->value);
        // }

        foreach (Permission::cases() as $permission) {
            SpatiePermission::firstOrCreate([
                'name' => $permission->value,
                'guard_name' => 'web',
            ]);
        }
    }
}
