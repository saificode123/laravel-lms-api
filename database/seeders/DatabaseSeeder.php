<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create the base roles
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'admin'],
            ['id' => 2, 'name' => 'instructor'],
            ['id' => 3, 'name' => 'student'],
        ]);

        // Create your dummy instructor (ID 1)
        User::factory()->create([
            'id' => 1,
            'name' => 'Instructor Saifi',
            'email' => 'instructor@lms.com',
            'role_id' => 2,
        ]);
    }
}