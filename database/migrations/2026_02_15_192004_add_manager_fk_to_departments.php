<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
        Schema::table('departments', function (Blueprint $table) {
            // Bây giờ bảng employees đã có, ta mới gắn khóa ngoại an toàn
            $table->foreign('manager_id')
                ->references('id')
                ->on('employees')
                ->onDelete('set null'); // Nếu trưởng phòng bị xóa, ghế trưởng phòng trống
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });
    }
};
