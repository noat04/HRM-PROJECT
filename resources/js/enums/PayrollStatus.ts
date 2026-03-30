const PayrollStatus = {
    DRAFT: 'draft',
    LOCKED: 'locked',
    PAID: 'paid',

    labels: {
        DRAFT: 'Nháp',
        LOCKED: 'Đã chốt sổ',
        PAID: 'Đã chi trả',
    } as Record<string, string>,

    colors: {
        DRAFT: 'bg-gray-500',
        LOCKED: 'bg-yellow-500',
        PAID: 'bg-green-500',
    } as Record<string, string>,
};

export default PayrollStatus;