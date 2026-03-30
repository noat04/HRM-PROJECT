<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import LeaveRequestStatus from '@/enums/LeaveRequestStatus';
const props = defineProps<{
    leaveRequest: {
        id: number;
        employee_id: number;
        leave_type_id: number;
        manager_id: number;
        start_date: string;
        end_date: string;
        total_days: number;
        reason: string;
        status: string;
        rejection_reason: string | null;
        responded_at: string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Đơn xin nghỉ phép', href: '/leaves/requests' },
    { title: 'Chi tiết', href: '#' },
];

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('vi-VN');
};

const formatStatus = (status: string) => {
    switch (status) {
        case LeaveRequestStatus.PENDING:
            return LeaveRequestStatus.labels.pending;
        case LeaveRequestStatus.APPROVED:
            return LeaveRequestStatus.labels.approved;
        case LeaveRequestStatus.REJECTED:
            return LeaveRequestStatus.labels.rejected;
        default:
            return status;
    }
};

const formatStatusClass = (status: string) => {
    switch (status) {
        case LeaveRequestStatus.PENDING:
            return LeaveRequestStatus.classes.pending;
        case LeaveRequestStatus.APPROVED:
            return LeaveRequestStatus.classes.approved;
        case LeaveRequestStatus.REJECTED:
            return LeaveRequestStatus.classes.rejected;
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

</script>
<template>
    <Head title="Chi tiết Đơn xin nghỉ phép" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Chi tiết Đơn xin nghỉ phép</h1>
                    <p class="text-muted-foreground">Xem chi tiết đơn xin nghỉ phép.</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Thông tin Đơn xin nghỉ phép</CardTitle>
                    <CardDescription>Xem chi tiết đơn xin nghỉ phép.</CardDescription>
                </CardHeader>
                
                <CardContent>
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Nhân viên</label>
                                <p class="text-gray-900">{{ leaveRequest.employee_id }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Người quản lý duyệt đơn</label>
                                <p class="text-gray-900">{{ leaveRequest.manager_id }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Loại nghỉ phép</label>
                                <p class="text-gray-900">{{ leaveRequest.leave_type_id }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Số ngày nghỉ</label>
                                <p class="text-gray-900">{{ leaveRequest.total_days }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Ngày bắt đầu</label>
                                <p class="text-gray-900">{{ formatDate(leaveRequest.start_date) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Ngày kết thúc</label>
                                <p class="text-gray-900">{{ formatDate(leaveRequest.end_date) }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Lý do</label>
                            <p class="text-gray-900">{{ leaveRequest.reason }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Trạng thái</label>
                            <span :class="formatStatusClass(leaveRequest.status)">{{ formatStatus(leaveRequest.status) }}</span>
                        </div>

                        <div v-if="leaveRequest.rejection_reason">
                            <label class="block text-sm font-medium mb-2">Lý do từ chối</label>
                            <p class="text-gray-900">{{ leaveRequest.rejection_reason }}</p>
                        </div>

                        <div v-if="leaveRequest.responded_at">
                            <label class="block text-sm font-medium mb-2">Ngày phản hồi</label>
                            <p class="text-gray-900">{{ formatDate(leaveRequest.responded_at) }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="flex justify-end gap-2">
                <Link href="/leaves/requests">
                    <Button variant="outline">Quay lại</Button>
                </Link>
                <Link :href="`/leaves/requests/${leaveRequest.id}/edit`">
                    <Button>Chỉnh sửa</Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>