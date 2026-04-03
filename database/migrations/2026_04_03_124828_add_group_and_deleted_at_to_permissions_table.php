<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            // // Thêm cột 'group' với giá trị mặc định, đặt nó ngay sau cột 'guard_name' cho đẹp Database
            // $table->string('group')->default('all')->after('guard_name');
            
            // Sinh ra cột 'deleted_at' để dùng cho SoftDeletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hàm down dùng để "quay xe" (rollback) nếu bạn muốn hủy bỏ lệnh up ở trên
        Schema::table('permissions', function (Blueprint $table) {
            // $table->dropColumn('group');
            $table->dropSoftDeletes(); // Xóa cột deleted_at
        });
    }
};