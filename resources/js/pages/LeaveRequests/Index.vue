<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Pencil, Trash2, Eye, Plus } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import LeaveRequestStatus from '@/enums/LeaveRequestStatus';

interface LeaveRequest {
    id: number;
    employee_id: number;
    leave_type_id: number;
    manager_id: number;
    start_date: string;
    end_date: string;
    total_days: number;
    reason: string;
    status: string;
    rejection_reason: string| null;
    responded_at: string | null;
}

interface PaginatedLeaveRequests {
    data: LeaveRequest[];
    current_page: number;
    total: number | 0;
    per_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const breadcrumbs:BreadcrumbItem[] = [
    { title: 'Trang chủ', href: '/dashboard' },
    { title: 'Yêu cầu nghỉ phép', href: '/leaves/requests' },
];

const props = defineProps<{
    leaveRequests: PaginatedLeaveRequests;
}>();

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const showFlash = ref(false);

watch(flashSuccess, (newMessage) => {
    if (newMessage) {
        showFlash.value = true;
        setTimeout(() => {
            showFlash.value = false;
        }, 3000);
    }
}, { immediate: true });

const deleteLeaveRequest = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn xóa yêu cầu nghỉ phép này?')) {
        router.delete(`/leaves/requests/${id}`);
    }
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

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('vi-VN');
};


</script>
<template>
    <Head title="Yêu cầu nghỉ phép" />

     <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-7xl mx-auto w-full">
            
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Quản lý Yêu cầu nghỉ phép</h1>
                    <p class="text-muted-foreground">Danh sách các yêu cầu nghỉ phép trong hệ thống.</p>
                </div>
                <Link href="/leaves/requests/create">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Tạo Yêu cầu nghỉ phép mới
                    </Button>
                </Link>
            </div>
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-300"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div 
                    v-if="flashSuccess && showFlash" 
                    class="flex items-center rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-200 text-emerald-800 shadow-sm"
                >
                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ flashSuccess }}</span>
                </div>
            </Transition>

            <Card>
                <CardHeader>
                    <CardTitle>Danh sách Yêu cầu nghỉ phép</CardTitle>
                    <CardDescription>
                        Tổng số yêu cầu nghỉ phép: {{ leaveRequests.total }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4 font-medium">Tên Nhân viên</th>
                                    <th class="text-left py-3 px-4 font-medium">Loại nghỉ phép</th>
                                    <th class="text-left py-3 px-4 font-medium">Người quản lý duyệt đơn</th>
                                    <th class="text-left py-3 px-4 font-medium">Ngày bắt đầu</th>
                                    <th class="text-left py-3 px-4 font-medium">Ngày kết thúc</th>
                                    <th class="text-left py-3 px-4 font-medium">Tổng số ngày</th>
                                    <th class="text-left py-3 px-4 font-medium">Lý do</th>
                                    <th class="text-left py-3 px-4 font-medium">Trạng thái</th>
                                    <!-- <th class="text-left py-3 px-4 font-medium">Lý do từ chối</th>
                                    <th class="text-left py-3 px-4 font-medium">Ngày phản hồi</th> -->
                                    <th class="text-right py-3 px-4 font-medium">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="leaveRequest in leaveRequests.data" :key="leaveRequest.id" class="border-b hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-primary">{{ leaveRequest.employee_id }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ leaveRequest.leave_type_id }}</td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ leaveRequest.manager_id }}</td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ formatDate(leaveRequest.start_date) }}</td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ formatDate(leaveRequest.end_date) }}</td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ leaveRequest.total_days }}</td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ leaveRequest.reason }}</td>
                                    <td class="py-3 px-4">
                                        <span 
                                            class="px-2.5 py-1 rounded-full text-xs font-medium border"
                                            :class="formatStatusClass(leaveRequest.status)"
                                        >
                                            {{ formatStatus(leaveRequest.status) }}
                                        </span>
                                    </td>
                                    <!-- <td class="py-3 px-4 text-muted-foreground">{{ leaveRequest.rejection_reason }}</td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ leaveRequest.responded_at }}</td> -->
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="`/leaves/requests/${leaveRequest.id}`">
                                                <Button variant="ghost" size="icon" class="h-8 w-8">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="`/leaves/requests/${leaveRequest.id}/edit`">
                                                <Button variant="ghost" size="icon" class="h-8 w-8">
                                                    <Pencil class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10" @click="deleteLeaveRequest(leaveRequest.id)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-muted-foreground">
                            Hiển thị {{ leaveRequests.data.length }} trên {{ leaveRequests.total }} kết quả
                        </div>
                        <!-- <div class="flex gap-1">
                            <Link v-for="link in leaveRequests.links" :key="link.url" :href="link.url" :class="['px-3 py-1 rounded-md text-sm font-medium transition-colors', link.active ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted']">
                                {{ link.label }}
                            </Link>
                        </div> -->
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>