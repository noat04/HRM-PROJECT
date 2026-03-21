<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // 1. Adjacency List (Phòng cha/con)
            // Tự tham chiếu chính bảng này.
            // Nếu parent_id = null -> Đây là Phòng/Khối cấp cao nhất (Root).
            // onDelete('set null'): Nếu phòng cha bị xóa, các phòng con sẽ thành phòng cấp 0 (mồ côi cha).
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('departments')
                  ->onDelete('set null');

            // 2. Trưởng phòng (Circular Dependency Handle)
            // Ở bước này, CHỈ TẠO CỘT, KHÔNG TẠO KHÓA NGOẠI (constrained).
            // Vì bảng 'employees' có thể chưa tồn tại lúc file này chạy.
            $table->unsignedBigInteger('manager_id')->nullable()->comment('FK to employees later');
            
            // Các cột phụ trợ
            $table->text('description')->nullable();
            $table->integer('level')->default(0)->comment('Độ sâu của cây phòng ban (Cache)');
            $table->timestamps();
            $table->softDeletes(); // Xóa mềm
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};