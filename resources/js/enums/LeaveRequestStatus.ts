const LeaveRequestStatus = {
    PENDING: 'pending',
    APPROVED: 'approved',
    REJECTED: 'rejected',
    CANCELLED: 'cancelled',

    labels: {
        'pending': 'Chờ duyệt',
        'approved': 'Đã duyệt',
        'rejected': 'Từ chối',
        'cancelled': 'Đã hủy',
    } as Record<string, string>,

    // Đã thay đổi thành các class màu có sẵn của Tailwind CSS
    classes: {
        'pending': 'bg-amber-100 text-amber-800 border-amber-200',     // Vàng
        'approved': 'bg-emerald-100 text-emerald-800 border-emerald-200', // Xanh lá
        'rejected': 'bg-red-100 text-red-800 border-red-200',           // Đỏ
        'cancelled': 'bg-gray-100 text-gray-800 border-gray-200',         // Xám
    } as Record<string, string>,
};

export default LeaveRequestStatus;