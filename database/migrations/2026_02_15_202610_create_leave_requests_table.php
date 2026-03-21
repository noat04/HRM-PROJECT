<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\LeaveStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();

            // 1. Đối tượng (Who & What)
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained();

            // 2. Snapshot Người duyệt (Manager Snapshot)
            // Lưu cứng ID sếp tại thời điểm xin.
            // Nullable vì có thể nhân viên (CEO) không có sếp, hoặc sếp chưa được gán.
            $table->foreignId('manager_id')->nullable()->constrained('employees');

            // 3. Thời gian nghỉ (Timeline)
            $table->date('start_date');
            $table->date('end_date');
            
            // Decimal(4,1) hỗ trợ số lẻ: 0.5 (nửa ngày), 1.0, 1.5, 12.0
            $table->decimal('total_days', 4, 1)->comment('Tổng số ngày nghỉ (Vd: 0.5, 1, 1.5)');

            // 4. Lý do & Workflow
            $table->text('reason')->comment('Lý do xin nghỉ');
            $table->string('status')->default(LeaveStatus::PENDING->value);
            
            // Thông tin phản hồi từ Sếp
            $table->text('rejection_reason')->nullable()->comment('Lý do từ chối (Bắt buộc nếu Reject)');
            $table->datetime('responded_at')->nullable()->comment('Thời điểm Sếp bấm duyệt/từ chối');

            $table->timestamps();
            $table->softDeletes();

            // --- INDEX ---
            // Giúp Sếp tìm nhanh các đơn cần duyệt của nhân viên mình
            $table->index(['manager_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};