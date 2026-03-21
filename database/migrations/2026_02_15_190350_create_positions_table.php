<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();

            // 1. Thông tin định danh
            $table->string('name'); // Vd: Senior Developer
            $table->string('code')->unique()->comment('Mã định danh duy nhất (Vd: DEV_SR, DIR, HR_MGR)');

            // 2. Phân cấp quyền hạn (QUAN TRỌNG)
            // Logic: Người có Level cao hơn sẽ mặc định quản lý người Level thấp hơn trong cùng phòng ban
            $table->integer('level')->default(1)->comment('1: Intern, 5: Staff, 10: Manager, 20: Director');

            // 3. Dải lương cơ bản (Base Salary Range)
            // Dùng Decimal(15, 2) cho tiền tệ, tuyệt đối không dùng Float/Double
            $table->decimal('salary_min', 15, 2)->default(0)->comment('Mức lương sàn');
            $table->decimal('salary_max', 15, 2)->default(0)->comment('Mức lương trần');

            // 4. Mô tả công việc (Job Description)
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes(); // Xóa mềm (HRM không bao giờ xóa cứng chức vụ đã từng dùng)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};