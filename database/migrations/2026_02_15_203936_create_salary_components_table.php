<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\SalaryComponentType;
use App\Enums\SalaryCalculationType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();

            // 1. Định danh khoản lương
            $table->string('name')->comment('Tên hiển thị: Lương cơ bản, Phụ cấp xăng...');
            $table->string('code')->unique()->comment('Mã hệ thống: BASIC_SALARY, LUNCH, SOCIAL_INSURANCE');

            // 2. Phân loại (Quan trọng để biết Cộng hay Trừ)
            $table->string('type')->default(SalaryComponentType::EARNING->value);

            // 3. Cách tính toán
            $table->string('calculation_type')->default(SalaryCalculationType::FIXED->value);
            
            // Giá trị mặc định (nếu có). Vd: Phụ cấp ăn luôn là 730.000
            $table->decimal('default_value', 15, 2)->default(0)->comment('Giá trị mặc định hoặc %');

            // 4. Cấu hình Thuế & Hiển thị
            $table->boolean('is_taxable')->default(true)->comment('Có chịu thuế TNCN không?');
            $table->boolean('is_active')->default(true)->comment('Còn sử dụng không?');
            
            $table->text('description')->nullable()->comment('Mô tả công thức tính nếu là FORMULA');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_components');
    }
};