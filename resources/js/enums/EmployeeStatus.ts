const EmployeeStatus = {
    PROBATION: 'probation',
    OFFICIAL: 'official',
    RESIGNED: 'resigned',
    PAUSED: 'paused',

    label(value: string): string {
        switch (value) {
            case this.PROBATION: return 'Thử việc';
            case this.OFFICIAL: return 'Chính thức';
            case this.RESIGNED: return 'Đã nghỉ việc';
            case this.PAUSED: return 'Tạm hoãn';
            default: return '';
        }
    },

    options() {
        return [
            { value: this.PROBATION, label: 'Thử việc' },
            { value: this.OFFICIAL, label: 'Chính thức' },
            { value: this.RESIGNED, label: 'Đã nghỉ việc' },
            { value: this.PAUSED, label: 'Tạm hoãn' },
        ];
    },
    // 👇 Bổ sung thêm mapping classes (màu sắc) cho từng trạng thái
    classes: {
        'probation': 'bg-amber-100 text-amber-800 border-amber-200',     // Vàng (Đang thử việc)
        'official': 'bg-emerald-100 text-emerald-800 border-emerald-200', // Xanh lá (Chính thức)
        'resigned': 'bg-rose-100 text-rose-800 border-rose-200',          // Đỏ (Nghỉ việc)
        'paused': 'bg-slate-100 text-slate-800 border-slate-200',         // Xám (Tạm hoãn)
    } as Record<string, string>

};

export default EmployeeStatus;
