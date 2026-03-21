<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\SalaryComponentType; // Sử dụng lại Enum Earning/Deduction

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslip_details', function (Blueprint $table) {
            $table->id();

            // 1. Liên kết phiếu lương (Master-Detail)
            $table->foreignId('payslip_id')->constrained()->onDelete('cascade');

            // 2. Liên kết tham chiếu (Optional)
            // Lưu ID để truy vết, nhưng cho phép NULL vì khoản lương này có thể bị xóa trong tương lai
            $table->foreignId('salary_component_id')->nullable()->constrained('salary_components')->onDelete('set null');

            // 3. Snapshot dữ liệu (Lưu cứng giá trị)
            // Tên khoản lương: Lưu String cứng. Vd: "Lương cơ bản 2024".
            // Để sau này dù Admin đổi tên thành "Lương CB", lịch sử cũ vẫn giữ nguyên.
            $table->string('component_name');
            
            // Loại: Earning (Thu nhập) hay Deduction (Khấu trừ)
            $table->string('type')->default(SalaryComponentType::EARNING->value);

            // Số tiền
            $table->decimal('amount', 15, 2);

            // 4. Thông tin phụ (Metadata)
            $table->boolean('is_taxable')->default(true)->comment('Khoản này lúc tính có chịu thuế không?');
            $table->text('description')->nullable()->comment('Diễn giải (Vd: Thưởng nóng dự án A)');

            $table->timestamps();

            // --- INDEX ---
            // Index giúp báo cáo nhanh: "Lấy tất cả các khoản Thu nhập của phiếu lương X"
            $table->index(['payslip_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslip_details');
    }
};