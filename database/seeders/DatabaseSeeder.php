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
        // // 1. Tạo 10 user ngẫu nhiên (tên, email random)
        // User::factory(10)->create();

    //   Schema::create('users', function (Blueprint $table) {
    //         $table->id();
    //         $table->string('name');
    //         $table->string('email')->unique();
    //         $table->timestamp('email_verified_at')->nullable();
    //         $table->string('password');
            
    //         // 👇 THÊM ĐÚNG DÒNG NÀY VÀO ĐÂY LÀ XONG
    //         $table->boolean('is_active')->default(true); 
            
    //         $table->rememberToken();
    //         $table->timestamps();
    //     });
        $this->call([
            RolesAndPermissionsSeeder::class,
            MasterDataSeeder::class,
            UserAndEmployeeSeeder::class,
        ]);
    }
}
