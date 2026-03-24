const ShiftStatus = {
    HAS_SALARY: 'has_salary',
    NO_SALARY: 'no_salary',

    label(value: string): string {
        switch (value) {
            case this.HAS_SALARY: return 'Có lương';
            case this.NO_SALARY: return 'Không lương';
            default: return '';
        }
    },

    options() {
        return [
            { value: this.HAS_SALARY, label: 'Có lương' },
            { value: this.NO_SALARY, label: 'Không lương' },
        ];
    },

    classes: {
        'has_salary': 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'no_salary': 'bg-rose-100 text-rose-800 border-rose-200',
    } as Record<string, string>
};

export default ShiftStatus;