<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\PayrollStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();

            // 1. Thông tin định danh
            $table->string('name')->comment('Vd: Kỳ lương Tháng 10/2025');
            $table->string('code')->unique()->comment('Mã duy nhất: PR_2025_10');

            // 2. Phạm vi thời gian (Time Window)
            // Quan trọng: Dùng để query bảng Attendances
            $table->date('start_date');
            $table->date('end_date');

            // 3. Quy trình xử lý (Workflow)
            $table->string('status')->default(PayrollStatus::DRAFT->value);
            
            // Ngày thực tế chi tiền (Có thể khác ngày kết thúc kỳ công)
            $table->date('payment_date')->nullable()->comment('Ngày chuyển khoản dự kiến/thực tế');

            $table->timestamps();
            $table->softDeletes();

            // --- INDEX & CONSTRAINTS ---
            // Index ngày để tìm nhanh kỳ lương hiện tại
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }
};