LaraHRM - Hệ thống Quản trị Nhân sự & Tiền lương Enterprise
Mô tả: Hệ thống ERP mini tập trung vào quản lý Nhân sự (HRM), Chấm công (Time Tracking) và Tính lương (Payroll).

Triết lý thiết kế:

-Data Integrity (Toàn vẹn dữ liệu): Ưu tiên độ chính xác tuyệt đối, đặc biệt trong tính toán tiền lương (Decimal Precision) và lịch sử chấm công (Immutable History).

Scalability (Khả năng mở rộng): Tách biệt logic nghiệp vụ, sử dụng Design Patterns để dễ dàng thêm mới quy tắc tính lương mà không sửa core.

Performance (Hiệu năng): Tối ưu hóa cho dữ liệu lớn (Big Data) với hàng triệu bản ghi chấm công.

🏗 Kiến trúc Hệ thống (System Architecture)
Dự án được chia thành 4 module nghiệp vụ chính, áp dụng Domain-Driven Design (DDD):

1. Authentication & RBAC (Quản trị & Phân quyền)
Core Logic: Sử dụng Spatie Permission với cơ chế Polymorphic Relations.

Feature:

Phân quyền động tới từng chức năng (View Salary, Approve Leave...).

Middleware bảo mật 2 lớp: Chặn ngay lập tức User bị khóa (Banned) kể cả khi session chưa hết hạn.

Soft Deletes: Bảo toàn dữ liệu User để tham chiếu lịch sử.

2. Organization (Tổ chức & Nhân sự)
Core Logic: Quản lý sơ đồ tổ chức đa cấp (Recursive Tree) và quan hệ nhân sự.

Feature:

Self-Referencing Relationship: Quản lý quan hệ "Sếp - Lính" (Manager - Direct Reports) không giới hạn cấp bậc.

Automated Coding: Observer Pattern tự động sinh mã nhân viên (NV-2024-XXXX) đảm bảo không trùng lặp.

Loose Coupling: Tách biệt User (Identity) và Employee (Profile) để đảm bảo nghiệp vụ nhân sự không bị ảnh hưởng bởi thay đổi hệ thống đăng nhập.

3. Time & Attendance (Chấm công & Thời gian)
Core Logic: Xử lý Big Data và Logic thời gian thực.

Feature:

Plan vs. Actual Separation: Tách biệt "Lịch phân ca" (employee_shifts) và "Dữ liệu chấm công thực tế" (attendances).

Snapshot Logic: Lưu cứng shift_id tại thời điểm check-in để đảm bảo lịch sử không bị sai lệch khi HR đổi lịch làm việc.

Performance: Đánh Index (employee_id, date) giúp truy vấn bảng công tháng (30-31 ngày) dưới 50ms trên tập dữ liệu 1 triệu dòng.

4. Payroll Engine (Tính lương)
Core Logic: Hệ thống tính lương động (Dynamic Formula) và Kế toán (Accounting).

Feature:

- EAV Model Variation: Cấu trúc salary_components cho phép thêm bớt các khoản phụ cấp/khấu trừ mà không cần sửa DB Schema.

- Immutable History (Snapshot): Sử dụng bảng payslip_details lưu chết giá trị và tên khoản lương tại thời điểm tính. Đảm bảo in phiếu lương quá khứ luôn đúng 100%.

- Strategy Pattern: Tách biệt logic tính toán (Lương ngày công, Lương KPI, Thuế) thành các class riêng biệt.

- ACID Transaction: Đảm bảo quá trình chốt lương (Lock) và trừ quỹ lương diễn ra nguyên vẹn.

🗄 Database Schema (ERD Highlight)
Module: Payroll (Tính lương)
Mối quan hệ đảm bảo tính "Bất biến lịch sử":

Đoạn mã
erDiagram
    EMPLOYEES ||--o{ PAYSLIPS : "has history"
    PAYROLL_PERIODS ||--o{ PAYSLIPS : "contains"
    PAYSLIPS ||--o{ PAYSLIP_DETAILS : "snapshots"
    
    PAYSLIP_DETAILS {
        bigint id PK
        string component_name "Snapshot Name"
        decimal amount "Snapshot Value"
        enum type "Earning/Deduction"
    }
payslip_details: Lưu giá trị snapshot. Không Join trực tiếp với salary_components để tránh lỗi sai lệch lịch sử khi đổi tên/công thức lương.

Module: Time & Attendance (Chấm công)
Mối quan hệ "Tách biệt Kế hoạch và Thực tế":

Đoạn mã
erDiagram
    EMPLOYEES ||--o{ EMPLOYEE_SHIFTS : "Plan (HR Config)"
    SHIFTS ||--o{ EMPLOYEE_SHIFTS : "Plan"
    
    SHIFTS ||--o{ ATTENDANCES : "Actual (Snapshot ID)"
    EMPLOYEES ||--o{ ATTENDANCES : "Actual Check-in"
attendances: Lưu trực tiếp shift_id tại thời điểm check-in. Query báo cáo cực nhanh mà không cần tính toán lại lịch sử phân ca.

🛠 Tech Stack & Libraries
Category,Technology,Usage
Backend Framework,Laravel 10.x,Core System
Language,PHP 8.2,"Enums, Types, Readonly Classes"
Database,MySQL 8.0,"JSON Column, Partitioning support"
Frontend,Vue.js 3 + Vite,Single Page Application (SPA)
Auth,Laravel Sanctum,API Authentication
Excel,Maatwebsite/Excel,Export Report (Japanese standard format)
Date Handling,Carbon,Timezone & Diff calculation
Task Scheduling,Laravel Scheduler,"Cronjob: Auto Checkout, Salary Calculation"

Gemini said
Tuyệt vời. Đây là bản README.md hoàn chỉnh, được viết theo chuẩn Technical Specification (Tài liệu kỹ thuật) của các dự án Enterprise.

Bạn có thể copy toàn bộ nội dung dưới đây, tạo file README.md trong thư mục gốc dự án của bạn và đẩy lên GitHub/GitLab. Đây sẽ là "vũ khí" mạnh nhất trong CV của bạn.

LaraHRM - Hệ thống Quản trị Nhân sự & Tiền lương Enterprise
Mô tả: Hệ thống ERP mini tập trung vào quản lý Nhân sự (HRM), Chấm công (Time Tracking) và Tính lương (Payroll).

Triết lý thiết kế:

Data Integrity (Toàn vẹn dữ liệu): Ưu tiên độ chính xác tuyệt đối, đặc biệt trong tính toán tiền lương (Decimal Precision) và lịch sử chấm công (Immutable History).

Scalability (Khả năng mở rộng): Tách biệt logic nghiệp vụ, sử dụng Design Patterns để dễ dàng thêm mới quy tắc tính lương mà không sửa core.

Performance (Hiệu năng): Tối ưu hóa cho dữ liệu lớn (Big Data) với hàng triệu bản ghi chấm công.

🏗 Kiến trúc Hệ thống (System Architecture)
Dự án được chia thành 4 module nghiệp vụ chính, áp dụng Domain-Driven Design (DDD):

1. Authentication & RBAC (Quản trị & Phân quyền)
Core Logic: Sử dụng Spatie Permission với cơ chế Polymorphic Relations.

Feature:

Phân quyền động tới từng chức năng (View Salary, Approve Leave...).

Middleware bảo mật 2 lớp: Chặn ngay lập tức User bị khóa (Banned) kể cả khi session chưa hết hạn.

Soft Deletes: Bảo toàn dữ liệu User để tham chiếu lịch sử.

2. Organization (Tổ chức & Nhân sự)
Core Logic: Quản lý sơ đồ tổ chức đa cấp (Recursive Tree) và quan hệ nhân sự.

Feature:

Self-Referencing Relationship: Quản lý quan hệ "Sếp - Lính" (Manager - Direct Reports) không giới hạn cấp bậc.

Automated Coding: Observer Pattern tự động sinh mã nhân viên (NV-2024-XXXX) đảm bảo không trùng lặp.

Loose Coupling: Tách biệt User (Identity) và Employee (Profile) để đảm bảo nghiệp vụ nhân sự không bị ảnh hưởng bởi thay đổi hệ thống đăng nhập.

3. Time & Attendance (Chấm công & Thời gian)
Core Logic: Xử lý Big Data và Logic thời gian thực.

Feature:

Plan vs. Actual Separation: Tách biệt "Lịch phân ca" (employee_shifts) và "Dữ liệu chấm công thực tế" (attendances).

Snapshot Logic: Lưu cứng shift_id tại thời điểm check-in để đảm bảo lịch sử không bị sai lệch khi HR đổi lịch làm việc.

Performance: Đánh Index (employee_id, date) giúp truy vấn bảng công tháng (30-31 ngày) dưới 50ms trên tập dữ liệu 1 triệu dòng.

4. Payroll Engine (Tính lương)
Core Logic: Hệ thống tính lương động (Dynamic Formula) và Kế toán (Accounting).

Feature:

EAV Model Variation: Cấu trúc salary_components cho phép thêm bớt các khoản phụ cấp/khấu trừ mà không cần sửa DB Schema.

Immutable History (Snapshot): Sử dụng bảng payslip_details lưu chết giá trị và tên khoản lương tại thời điểm tính. Đảm bảo in phiếu lương quá khứ luôn đúng 100%.

Strategy Pattern: Tách biệt logic tính toán (Lương ngày công, Lương KPI, Thuế) thành các class riêng biệt.

ACID Transaction: Đảm bảo quá trình chốt lương (Lock) và trừ quỹ lương diễn ra nguyên vẹn.

🗄 Database Schema (ERD Highlight)
Module: Payroll (Tính lương)
Mối quan hệ đảm bảo tính "Bất biến lịch sử":

Đoạn mã
erDiagram
    EMPLOYEES ||--o{ PAYSLIPS : "has history"
    PAYROLL_PERIODS ||--o{ PAYSLIPS : "contains"
    PAYSLIPS ||--o{ PAYSLIP_DETAILS : "snapshots"
    
    PAYSLIP_DETAILS {
        bigint id PK
        string component_name "Snapshot Name"
        decimal amount "Snapshot Value"
        enum type "Earning/Deduction"
    }
payslip_details: Lưu giá trị snapshot. Không Join trực tiếp với salary_components để tránh lỗi sai lệch lịch sử khi đổi tên/công thức lương.

Module: Time & Attendance (Chấm công)
Mối quan hệ "Tách biệt Kế hoạch và Thực tế":

Đoạn mã
erDiagram
    EMPLOYEES ||--o{ EMPLOYEE_SHIFTS : "Plan (HR Config)"
    SHIFTS ||--o{ EMPLOYEE_SHIFTS : "Plan"
    
    SHIFTS ||--o{ ATTENDANCES : "Actual (Snapshot ID)"
    EMPLOYEES ||--o{ ATTENDANCES : "Actual Check-in"
attendances: Lưu trực tiếp shift_id tại thời điểm check-in. Query báo cáo cực nhanh mà không cần tính toán lại lịch sử phân ca.

🛠 Tech Stack & Libraries
Category	Technology	Usage
Backend Framework	Laravel 10.x	Core System
Language	PHP 8.2	Enums, Types, Readonly Classes
Database	MySQL 8.0	JSON Column, Partitioning support
Frontend	Vue.js 3 + Vite	Single Page Application (SPA)
Auth	Laravel Sanctum	API Authentication
Excel	Maatwebsite/Excel	Export Report (Japanese standard format)
Date Handling	Carbon	Timezone & Diff calculation
Task Scheduling	Laravel Scheduler	Cronjob: Auto Checkout, Salary Calculation
🚀 Key Features for Developers (Điểm nhấn kỹ thuật)
1. Design Patterns Applied
Repository Pattern: Tách biệt Business Logic và Data Access.

Strategy Pattern: Sử dụng trong SalaryCalculator để xử lý nhiều loại công thức lương khác nhau.

Factory Pattern: Sinh dữ liệu mẫu (Seeding) phức tạp để test performance.

Observer Pattern: Tự động sinh mã nhân viên, log hoạt động.

2. Advanced Database Techniques
Composite Indexing: Tối ưu query bảng attendances.

Decimal Precision: Sử dụng DECIMAL(15, 2) cho mọi cột tiền tệ.

Database Transaction & Locking: Sử dụng DB::transaction và lockForUpdate() khi xử lý duyệt đơn nghỉ phép để tránh Race Condition.

3. Security & Quality
Middleware: EnsureUserIsActive chặn user bị ban ngay lập tức.

Unit Testing: (Planned) PHPUnit cover các logic tính lương quan trọng.

Clean Code: Tuân thủ PSR-12 Standard.

📦 Installation
Clone repo:

Bash
git clone https://github.com/your-username/larahrm.git
cd larahrm
Install dependencies:

Bash
composer install
npm install
Environment setup:

Bash
cp .env.example .env
php artisan key:generate
Database & Seeding:

Bash
# Chạy Migration và tạo dữ liệu mẫu (Admin, 1000 Nhân viên, Chấm công)
php artisan migrate --seed
Run:

Bash
php artisan serve
npm run dev

📝 Contact
Developer: Nguyen Danh Minh Toan

Email: toannguyen041214@gmail.com

Portfolio: https://github.com/noat04