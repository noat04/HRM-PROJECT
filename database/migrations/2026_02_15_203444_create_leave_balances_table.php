<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();

            // 1. Định danh Quỹ phép
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained()->onDelete('cascade');
            $table->integer('year')->comment('Năm tài chính (Vd: 2024)');

            // 2. Số liệu (Dùng Decimal để hỗ trợ nghỉ nửa ngày 0.5)
            $table->decimal('total_days', 5, 1)->default(12.0)->comment('Tổng phép được cấp (Mặc định 12)');
            $table->decimal('used_days', 5, 1)->default(0.0)->comment('Đã sử dụng');
            
            // Cột này được tính toán: remaining = total - used
            // Lưu cứng để query cho nhanh (Index/Sort)
            $table->decimal('remaining_days', 5, 1)->default(12.0)->comment('Số dư hiện tại (Cache)');

            $table->timestamps();

            // 3. Ràng buộc dữ liệu (Data Integrity)
            // Mỗi nhân viên chỉ có 1 quỹ phép cho 1 loại phép trong 1 năm
            $table->unique(['employee_id', 'leave_type_id', 'year'], 'emp_leave_year_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};