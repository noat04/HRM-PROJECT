<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_salary_structures', function (Blueprint $table) {
            $table->id();

            // 1. Liên kết Nhân viên & Khoản lương
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            
            // Tên bảng salary_components phải chính xác
            $table->foreignId('component_id')->constrained('salary_components')->onDelete('cascade');

            // 2. Giá trị tiền tệ (Money Value)
            // DECIMAL(15, 2): Hỗ trợ lên tới hàng nghìn tỷ đồng, chính xác đến từng xu
            $table->decimal('amount', 15, 2)->comment('Số tiền (VND) hoặc tỷ lệ (%)');

            // 3. Hiệu lực (Timeline)
            // Quan trọng: Ngày bắt đầu áp dụng mức lương này.
            $table->date('effective_date')->comment('Ngày bắt đầu có hiệu lực');

            $table->timestamps();
            $table->softDeletes();

            // --- RÀNG BUỘC & INDEX ---
            
            // 1. Unique: Một nhân viên, với 1 khoản lương, trong 1 ngày chỉ có 1 mức giá.
            // (Tránh nhập trùng 2 mức lương cơ bản cùng ngày 01/01)
            $table->unique(['employee_id', 'component_id', 'effective_date'], 'emp_comp_date_unique');

            // 2. Index tìm kiếm nhanh lịch sử lương của nhân viên
            $table->index(['employee_id', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_salary_structures');
    }
};