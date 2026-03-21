<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\EmployeeStatus; // Import Enum nếu bạn đã tạo

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    // 1. Khai báo các cột được phép gán dữ liệu
    protected $fillable = [
        'user_id',
        'employee_code',
        'full_name',
        'phone',
        'avatar',
        'department_id',
        'position_id',
        'manager_id', // Quan trọng: ID của sếp trực tiếp
        'status',     // probation, official...
        'gender',
        'birthday',
        'address',
        'join_date',
        'resignation_date',
        'bank_account_number',
        'bank_name',
    ];

    // 2. Ép kiểu dữ liệu (Casting)
    // Giúp code sạch hơn: $emp->join_date->format('d/m/Y') thay vì phải parse string
    protected $casts = [
        'join_date' => 'date',
        'resignation_date' => 'date',
        'birthday' => 'date',
        // Nếu bạn dùng PHP Enum (Khuyên dùng)
        'status' => EmployeeStatus::class, 
    ];

    // --- A. QUAN HỆ TỔ CHỨC (ORGANIZATION RELATIONSHIPS) ---

    /**
     * Link tới tài khoản đăng nhập (1-1)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Thuộc phòng ban nào?
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Giữ chức vụ gì?
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    // --- B. QUAN HỆ SẾP - LÍNH (SELF-REFERENCING) ---

    /**
     * Sếp trực tiếp của tôi là ai? (Parent)
     */
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /**
     * Tôi quản lý những ai? (Children / Direct Reports)
     * Dùng để vẽ sơ đồ tổ chức hoặc duyệt đơn cho cấp dưới
     */
    public function directReports()
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    // --- C. QUAN HỆ CHẤM CÔNG & LƯƠNG (Sẽ mở rộng sau) ---

    // public function attendances() { ... }
    // public function payslips() { ... }

    
    // --- D. ACCESSORS (HÀM XỬ LÝ DỮ LIỆU HIỂN THỊ) ---

    /**
     * Lấy URL ảnh đại diện. 
     * Nếu chưa up ảnh -> Trả về ảnh mặc định (UI Avatar) dựa theo tên.
     * Cách dùng: $employee->avatar_url
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        // Dịch vụ tạo avatar theo tên (miễn phí)
        $name = urlencode($this->full_name);
        return "https://ui-avatars.com/api/?name={$name}&background=random&color=fff";
    }
}