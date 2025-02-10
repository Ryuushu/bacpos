<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::create([
            'email' => 'adminnw@gmail.com',
            'password' => Hash::make('addmin233@g'),
            'role' => 'admin',
        ]);

        Admin::create([
            'nama_admin' => 'Admin Utama',
            'id_user' => $user->id_user,
        ]);
    }
}
