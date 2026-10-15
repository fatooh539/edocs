<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $departments = collect([
            ['code' => 'VC', 'name' => 'مكتب مدير الجامعة'],
            ['code' => 'REG', 'name' => 'أمانة الشؤون العلمية'],
            ['code' => 'FIN', 'name' => 'الإدارة المالية'],
            ['code' => 'HR', 'name' => 'إدارة الموارد البشرية'],
        ])->map(fn (array $department) => Department::create($department));

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'department_id' => $departments->firstWhere('code', 'VC')->id,
        ]);

        User::factory()->create([
            'name' => 'Finance User',
            'email' => 'finance@example.com',
            'department_id' => $departments->firstWhere('code', 'FIN')->id,
        ]);
    }
}
