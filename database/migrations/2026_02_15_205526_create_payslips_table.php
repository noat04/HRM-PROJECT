<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();

            // 1. Định danh (Identity)
            // Khóa ngoại trỏ đến Kỳ lương và Nhân viên
            $table->foreignId('payroll_period_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');

            // 2. Số liệu tổng hợp (Summary Data)
            // DECIMAL(15, 2) là chuẩn vàng cho tiền tệ
            $table->decimal('gross_salary', 15, 2)->comment('Tổng thu nhập (Trước thuế/BH)');
            $table->decimal('total_deduction', 15, 2)->comment('Tổng khấu trừ (BH, Thuế, Phạt)');
            $table->decimal('net_salary', 15, 2)->comment('Thực lĩnh (Gross - Deduction)');

            // 3. Thông tin phụ (Metadata)
            $table->decimal('working_days', 4, 1)->default(0)->comment('Số ngày công thực tế (Để tham chiếu)');
            $table->boolean('is_sent')->default(false)->comment('Đã gửi email phiếu lương chưa?');
            $table->timestamp('sent_at')->nullable()->comment('Thời điểm gửi email');

            // 4. Snapshot dữ liệu (CỰC KỲ QUAN TRỌNG)
            // Lưu toàn bộ chi tiết: Lương cơ bản, phụ cấp A, phụ cấp B, thuế... dạng JSON
            // Để sau này in phiếu lương, chỉ cần decode cột này ra là xong.
            $table->json('salary_snapshot')->comment('Lưu cứng công thức và chi tiết tại thời điểm tính');

            $table->timestamps();
            $table->softDeletes();

            // --- RÀNG BUỘC ---
            // Mỗi nhân viên chỉ có 1 phiếu lương trong 1 kỳ lương
            $table->unique(['payroll_period_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};