<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Đánh Index để tăng tốc độ cho mọi câu query có kiểm tra Soft Delete
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Gỡ Index nếu cần rollback
            $table->dropIndex(['deleted_at']); 
        });
    }
};