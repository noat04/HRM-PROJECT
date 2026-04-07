<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Đổi từ string (varchar 255) sang text (không giới hạn độ dài cơ bản)
            $table->text('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Lệnh để rollback lại như cũ nếu cần
            $table->string('description', 255)->nullable()->change();
        });
    }
};