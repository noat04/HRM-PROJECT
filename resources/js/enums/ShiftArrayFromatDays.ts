const ShiftArrayFromatDays = {
    MONDAY: 'monday',
    TUESDAY: 'tuesday',
    WEDNESDAY: 'wednesday',
    THURSDAY: 'thursday',
    FRIDAY: 'friday',
    SATURDAY: 'saturday',
    SUNDAY: 'sunday',

    // Dùng Record để TypeScript hiểu và không báo lỗi
    labels: {
        'monday': 'Thứ 2',
        'tuesday': 'Thứ 3',
        'wednesday': 'Thứ 4',
        'thursday': 'Thứ 5',
        'friday': 'Thứ 6',
        'saturday': 'Thứ 7',
        'sunday': 'Chủ nhật',
    } as Record<string, string>,

    label(value: string): string {
        return this.labels[value] || value;
    },

    options() {
        return [
            { value: this.MONDAY, label: 'Thứ 2' },
            { value: this.TUESDAY, label: 'Thứ 3' },
            { value: this.WEDNESDAY, label: 'Thứ 4' },
            { value: this.THURSDAY, label: 'Thứ 5' },
            { value: this.FRIDAY, label: 'Thứ 6' },
            { value: this.SATURDAY, label: 'Thứ 7' },
            { value: this.SUNDAY, label: 'Chủ nhật' },
        ];
    },

    classes: {
        'monday': 'bg-blue-100 text-blue-800 border-blue-200',
        'tuesday': 'bg-blue-100 text-blue-800 border-blue-200',
        'wednesday': 'bg-blue-100 text-blue-800 border-blue-200',
        'thursday': 'bg-blue-100 text-blue-800 border-blue-200',
        'friday': 'bg-blue-100 text-blue-800 border-blue-200',
        'saturday': 'bg-blue-100 text-blue-800 border-blue-200',
        'sunday': 'bg-red-100 text-red-800 border-red-200',
    } as Record<string, string>
};

export default ShiftArrayFromatDays;