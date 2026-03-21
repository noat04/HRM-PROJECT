<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();

            // 1. Định danh Ca làm việc
            $table->string('name')->unique()->comment('Vd: Ca Hành chính, Ca Đêm');
            $table->string('code')->unique()->nullable()->comment('Mã ca: OFFICE, NIGHT...');

            // 2. Cấu hình thời gian (Time Setting)
            // Dùng kiểu time (H:i:s)
            $table->time('start_time')->comment('Giờ bắt đầu (08:00:00)');
            $table->time('end_time')->comment('Giờ kết thúc (17:00:00)');

            // 3. Logic linh động (Flexibility)
            $table->integer('grace_period')->default(0)->comment('Số phút cho phép đi muộn (Vd: 15p)');
            
            // QUAN TRỌNG: Lưu ngày làm việc dạng JSON Array
            // Vd: [1, 2, 3, 4, 5] (T2 đến T6). [0, 6] (CN, T7)
            $table->json('work_days')->comment('Mảng các ngày làm việc trong tuần (0=CN, 1=T2...)');

            // 4. Trạng thái & Mô tả
            $table->string('status')->default('active')->comment('active/inactive');
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes(); // Xóa mềm để giữ lịch sử tham chiếu
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};