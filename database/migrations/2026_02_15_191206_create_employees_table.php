<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\EmployeeStatus; // (Optional) Nếu bạn dùng Enum class

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // 1. Liên kết tài khoản đăng nhập (Identity)
            // Quan hệ 1-1: Mỗi User chỉ có 1 hồ sơ Employee
            // onDelete('cascade'): Nếu xóa User login -> Xóa luôn hồ sơ nhân viên (Tùy logic dự án)
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // 2. Thông tin định danh & Cá nhân
            $table->string('employee_code')->unique()->comment('Mã NV: NV001, EMP-2024-001');
            $table->string('full_name')->index(); // Index tên để tìm kiếm cho nhanh
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable(); // Lưu đường dẫn ảnh thẻ
            
            // 3. Cơ cấu tổ chức (Organization)
            // onDelete('set null'): Phòng ban xóa, nhân viên vẫn còn (chỉ mất link phòng ban)
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('position_id')->nullable()->constrained()->onDelete('set null');

            // 4. Quan hệ Sếp - Lính (Self-Referencing)
            // Trỏ về chính bảng này. Nullable vì CEO không có sếp.
            $table->foreignId('manager_id')->nullable()->constrained('employees')->onDelete('set null');

            // 5. Trạng thái & Hợp đồng
            // Có thể dùng string hoặc enum. Khuyên dùng string mapping với PHP Enum.
            $table->string('status')->default('probation')->comment('probation, official, resigned');
            $table->date('join_date')->nullable()->comment('Ngày vào làm -> Tính thâm niên');
            $table->date('resignation_date')->nullable()->comment('Ngày nghỉ việc');

            // 6. Thông tin phụ (Tùy chọn)
            $table->string('gender')->nullable();
            $table->date('birthday')->nullable();
            $table->text('address')->nullable();
            
            // Tài khoản ngân hàng (Để trả lương - Nhóm Payroll)
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();

            $table->timestamps();
            $table->softDeletes(); // QUAN TRỌNG: Không bao giờ xóa cứng hồ sơ nhân sự
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};