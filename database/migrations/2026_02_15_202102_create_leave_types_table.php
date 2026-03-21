<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();

            // 1. Định danh
            $table->string('name')->comment('Vd: Nghỉ phép năm, Nghỉ ốm, Nghỉ kết hôn');
            $table->string('code')->unique()->comment('Mã dùng trong code: ANNUAL, SICK, UNPAID, MATERNITY');

            // 2. Cấu hình quyền lợi (Policy)
            $table->boolean('is_paid')->default(true)->comment('True: Có hưởng lương (Tính vào công), False: Trừ lương');
            $table->integer('days_allowed')->default(12)->comment('Số ngày tối đa cho phép trong 1 năm');

            // 3. Mô tả chi tiết (Cho nhân viên đọc nội quy)
            $table->text('description')->nullable()->comment('Quy định: Phải xin trước bao nhiêu ngày...');

            // 4. Trạng thái
            $table->boolean('is_active')->default(true)->comment('Nếu False: Không cho chọn loại nghỉ này nữa');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};