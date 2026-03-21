<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_shifts', function (Blueprint $table) {
            $table->id();

            // 1. Liên kết Nhân viên & Ca làm việc
            // onDelete('cascade'): Xóa nhân viên -> Xóa lịch phân ca.
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');

            // 2. Khoảng thời gian áp dụng
            $table->date('start_date')->comment('Ngày bắt đầu áp dụng ca này');
            $table->date('end_date')->nullable()->comment('Ngày kết thúc. Null = Áp dụng đến nay');

            // 3. Metadata
            $table->timestamps();

            // --- TỐI ƯU HÓA HIỆU NĂNG (PERFORMANCE) ---
            // Index quan trọng: Giúp tìm nhanh lịch làm việc của 1 nhân viên tại thời điểm cụ thể
            $table->index(['employee_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_shifts');
    }
};