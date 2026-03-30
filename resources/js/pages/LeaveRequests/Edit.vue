<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types';

interface Employee {
    id: number;
    full_name: string;
}

interface LeaveType {
    id: number;
    name: string;
}

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
    rejection_reason: string | null;
    responded_at: string | null;
}

const props = defineProps<{
    leaveRequest: LeaveRequest;
    managers: Employee[];
    leaveTypes: LeaveType[];
}>();

// 👇 SỬA LỖI 2: Hàm xử lý định dạng ngày tháng cho thẻ input
const formatDateForInput = (dateString: string | null) => {
    if (!dateString) return null;
    return dateString.split('T')[0].split(' ')[0];
};

const form = useForm({
    employee_id: props.leaveRequest.employee_id,
    leave_type_id: props.leaveRequest.leave_type_id,
    manager_id: props.leaveRequest.manager_id,
    // Bọc qua hàm format
    start_date: formatDateForInput(props.leaveRequest.start_date),
    end_date: formatDateForInput(props.leaveRequest.end_date),
    total_days: props.leaveRequest.total_days,
    reason: props.leaveRequest.reason,
    status: props.leaveRequest.status,
    rejection_reason: props.leaveRequest.rejection_reason,
    // Bọc qua hàm format
    responded_at: formatDateForInput(props.leaveRequest.responded_at),
});

// 👇 SỬA LỖI 1: Bổ sung hàm tính tổng số ngày
const calculateTotalDays = () => {
    if (form.start_date && form.end_date) {
        const start = new Date(form.start_date);
        const end = new Date(form.end_date);
        if (end >= start) {
            const diffTime = Math.abs(end.getTime() - start.getTime());
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            form.total_days = diffDays;
        } else {
            form.total_days = 0;
        }
    }
};

const submitForm = () => {
    form.put(`/leaves/requests/${props.leaveRequest.id}`);
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Đơn xin nghỉ phép', href: '/leaves/requests' },
    { title: 'Chỉnh sửa', href: '#' },
];

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const showFlash = ref(false);

watch(flashSuccess, (newMessage) => {
    if (newMessage) {
        showFlash.value = true;
        setTimeout(() => showFlash.value = false, 3000);
    }
}, { immediate: true });

// Hàm hiển thị tên quản lý ra màn hình cho người dùng xem
const managerName = computed(() => {
    if (!form.manager_id) return 'Chưa có quản lý';
    const manager = props.managers.find(emp => emp.id === form.manager_id);
    return manager ? manager.full_name : 'Không xác định';
});
</script>

<template>
    <Head title="Chỉnh sửa Đơn xin nghỉ phép" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Cập nhật Đơn xin nghỉ phép</h1>
                    <p class="text-muted-foreground">Cập nhật đơn xin nghỉ phép vào hệ thống.</p>
                </div>
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
                    <CardTitle>Thông tin Đơn xin nghỉ phép</CardTitle>
                    <CardDescription>Nhập đầy đủ các thông tin bắt buộc có dấu (*).</CardDescription>
                </CardHeader>
                
                <CardContent>
                    <form @submit.prevent="submitForm" class="space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Nhân viên <span class="text-destructive">*</span></label>
                                <select v-model="form.employee_id" class="w-full px-3 py-2 border rounded-md" required>
                                    <option value="">Chọn nhân viên</option>
                                    <option v-for="employee in managers" :key="employee.id" :value="employee.id">{{ employee.full_name }}</option>
                                </select>
                                <div v-if="form.errors.employee_id" class="text-red-500 text-sm mt-1">{{ form.errors.employee_id }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Loại nghỉ phép <span class="text-destructive">*</span></label>
                                <select v-model="form.leave_type_id" class="w-full px-3 py-2 border rounded-md" required>
                                    <option value="">Chọn loại nghỉ phép</option>
                                    <option v-for="leaveType in leaveTypes" :key="leaveType.id" :value="leaveType.id">{{ leaveType.name }}</option>
                                </select>
                                <div v-if="form.errors.leave_type_id" class="text-red-500 text-sm mt-1">{{ form.errors.leave_type_id }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Ngày bắt đầu <span class="text-destructive">*</span></label>
                                <input v-model="form.start_date" type="date" class="w-full px-3 py-2 border rounded-md" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Ngày kết thúc <span class="text-destructive">*</span></label>
                                <input v-model="form.end_date" type="date" class="w-full px-3 py-2 border rounded-md" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Tổng số ngày <span class="text-destructive">*</span></label>
                                <input v-model="form.total_days" type="number" min="1" class="w-full px-3 py-2 border rounded-md" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Người quản lý duyệt đơn</label>
                                <input :value="managerName" type="text" class="w-full px-3 py-2 border rounded-md bg-gray-50 text-gray-500" readonly />
                                <div v-if="form.errors.manager_id" class="text-red-500 text-sm mt-1">{{ form.errors.manager_id }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Lý do <span class="text-destructive">*</span></label>
                            <textarea v-model="form.reason" class="w-full px-3 py-2 border rounded-md" rows="4" required></textarea>
                            <div v-if="form.errors.reason" class="text-red-500 text-sm mt-1">{{ form.errors.reason }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Trạng thái <span class="text-destructive">*</span></label>
                            <select v-model="form.status" class="w-full px-3 py-2 border rounded-md" required>
                                <option value="pending">Chờ duyệt</option>
                                <option value="approved">Đã duyệt</option>
                                <option value="rejected">Từ chối</option>
                            </select>
                            <div v-if="form.errors.status" class="text-red-500 text-sm mt-1">{{ form.errors.status }}</div>
                        </div>
<div v-if="form.status === 'rejected'">
                            <label class="block text-sm font-medium mb-2">Lý do từ chối <span class="text-destructive">*</span></label>
                            <textarea v-model="form.rejection_reason" class="w-full px-3 py-2 border rounded-md" rows="4" required></textarea>
                            <div v-if="form.errors.rejection_reason" class="text-red-500 text-sm mt-1">{{ form.errors.rejection_reason }}</div>
                        </div>

                        <div v-if="form.status === 'approved' || form.status === 'rejected'">
                            <label class="block text-sm font-medium mb-2">Ngày phản hồi <span class="text-destructive">*</span></label>
                            <input v-model="form.responded_at" type="date" class="w-full px-3 py-2 border rounded-md" required />
                            <div v-if="form.errors.responded_at" class="text-red-500 text-sm mt-1">{{ form.errors.responded_at }}</div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <Link href="/leaves/requests">
                                <Button variant="outline" type="button">Hủy</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing">Lưu thay đổi</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

        </div>
    </AppLayout>
</template>