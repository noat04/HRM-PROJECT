<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Tạo 10 user ngẫu nhiên (tên, email random)
        User::factory(10)->create();

        // 2. Tạo 1 user cụ thể để bạn đăng nhập test (Email cố định)
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'), // Mật khẩu là "password"
        ]);
    }
}
