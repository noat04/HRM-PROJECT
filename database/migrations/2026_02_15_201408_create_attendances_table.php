<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            // 1. Định danh (Identity)
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            
            // 2. Snapshot Ca làm việc (Snapshot Logic)
            // Lưu cứng ID ca tại thời điểm check-in.
            // Để sau này dù HR đổi lịch làm việc thì dữ liệu cũ không bị sai lệch.
            $table->foreignId('shift_id')->constrained(); 

            // 3. Thời gian thực tế (Real-time Data)
            $table->date('date')->comment('Ngày chấm công');
            $table->datetime('check_in')->nullable();
            $table->datetime('check_out')->nullable();

            // 4. Đánh giá & Tính toán (Calculation)
            // Lưu trạng thái dạng string (map với Enum)
            $table->string('status')->default('on_time')->comment('on_time, late, early_leave...');
            
            // Số phút vi phạm (Quan trọng để trừ lương)
            $table->integer('late_minutes')->default(0)->comment('Số phút đi muộn');
            $table->integer('early_minutes')->default(0)->comment('Số phút về sớm');

            // 5. Bảo mật & Audit (Security)
            $table->string('ip_address', 45)->nullable(); // Hỗ trợ cả IPv6
            $table->string('device_info')->nullable();    // User-Agent (Chrome/Mobile...)
            $table->text('note')->nullable()->comment('Ghi chú (User giải trình hoặc HR sửa tay)');

            $table->timestamps();
            $table->softDeletes();

            // --- INDEXING (TỐI ƯU HIỆU NĂNG) ---
            
            // 1. Unique: Mỗi nhân viên chỉ có 1 record chấm công trong 1 ngày
            // (Nếu công ty làm 2 ca/ngày thì bỏ unique này đi)
            $table->unique(['employee_id', 'date']); 

            // 2. Index cho báo cáo tháng: Tìm nhanh bảng công tháng 10 của toàn công ty
            $table->index('date'); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};