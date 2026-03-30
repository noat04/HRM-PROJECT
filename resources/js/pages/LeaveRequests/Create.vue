<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types';
import LeaveRequestStatus from '@/enums/LeaveRequestStatus';

interface Employee {
    id: number;
    full_name: string;
    manager_id: number | null; // Phải có manager_id để tự động tìm
}

interface LeaveType {
    id: number;
    name: string;
}

const props = defineProps<{
    employees: Employee[];
    leaveTypes: LeaveType[];
}>();

const form = useForm({
    employee_id: '',
    manager_id: '' as number | string, // Thêm trường manager_id để gửi lên Server
    leave_type_id: '',
    start_date: '',
    end_date: '',
    total_days: 0,
    reason: '',
    status: LeaveRequestStatus.PENDING,
});

// 👇 LOGIC TỰ ĐỘNG TÌM QUẢN LÝ
watch(() => form.employee_id, (newEmployeeId) => {
    if (newEmployeeId) {
        // Tìm nhân viên đang được chọn trong danh sách
        const selectedEmployee = props.employees.find(emp => emp.id === Number(newEmployeeId));
        // Lấy ID quản lý của người đó gán vào form
        form.manager_id = selectedEmployee?.manager_id || '';
    } else {
        form.manager_id = '';
    }
});

// Hàm hiển thị tên quản lý ra màn hình cho người dùng xem
const managerName = computed(() => {
    if (!form.manager_id) return 'Chưa có quản lý';
    const manager = props.employees.find(emp => emp.id === form.manager_id);
    return manager ? manager.full_name : 'Không xác định';
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Trang chủ', href: '/dashboard' },
    { title: 'Yêu cầu nghỉ phép', href: '/leaves/requests' },
    { title: 'Tạo mới', href: '/leaves/requests/create' },
];

const submit = () => {
    form.post('/leaves/requests');
};

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const showFlash = ref(false);

watch(flashSuccess, (newMessage) => {
    if (newMessage) {
        showFlash.value = true;
        setTimeout(() => showFlash.value = false, 3000);
    }
}, { immediate: true });

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
</script>

<template>
    <Head title="Tạo mới Yêu cầu nghỉ phép" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Tạo mới Yêu cầu nghỉ phép</h1>
                    <p class="text-muted-foreground">Thêm yêu cầu nghỉ phép mới vào hệ thống.</p>
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
                <div v-if="flashSuccess && showFlash" class="flex items-center rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-200 text-emerald-800 shadow-sm">
                    <span class="font-medium">{{ flashSuccess }}</span>
                </div>
            </Transition>

            <Card>
                <CardHeader>
                    <CardTitle>Thông tin Yêu cầu nghỉ phép</CardTitle>
                    <CardDescription>Nhập đầy đủ các thông tin bắt buộc có dấu (*).</CardDescription>
                </CardHeader>
                
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Nhân viên <span class="text-destructive">*</span></label>
                                <select v-model="form.employee_id" class="w-full px-3 py-2 border rounded-md" required>
                                    <option value="">Chọn nhân viên</option>
                                    <option v-for="employee in employees" :key="employee.id" :value="employee.id">{{ employee.full_name }}</option>
                                </select>
                                <div v-if="form.errors.employee_id" class="text-red-500 text-sm mt-1">{{ form.errors.employee_id }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Người quản lý duyệt đơn</label>
                                <input :value="managerName" type="text" class="w-full px-3 py-2 border rounded-md bg-gray-50 text-gray-500" readonly />
                                <div v-if="form.errors.manager_id" class="text-red-500 text-sm mt-1">{{ form.errors.manager_id }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Loại nghỉ phép <span class="text-destructive">*</span></label>
                                <select v-model="form.leave_type_id" class="w-full px-3 py-2 border rounded-md" required>
                                    <option value="">Chọn loại nghỉ phép</option>
                                    <option v-for="leaveType in leaveTypes" :key="leaveType.id" :value="leaveType.id">{{ leaveType.name }}</option>
                                </select>
                                <div v-if="form.errors.leave_type_id" class="text-red-500 text-sm mt-1">{{ form.errors.leave_type_id }}</div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium mb-2">Tổng số ngày</label>
                                <input v-model="form.total_days" type="number" min="0" class="w-full px-3 py-2 border rounded-md bg-gray-50" required readonly />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Ngày bắt đầu <span class="text-destructive">*</span></label>
                                <input v-model="form.start_date" type="date" class="w-full px-3 py-2 border rounded-md" required @change="calculateTotalDays" />
                                <div v-if="form.errors.start_date" class="text-red-500 text-sm mt-1">{{ form.errors.start_date }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Ngày kết thúc <span class="text-destructive">*</span></label>
                                <input v-model="form.end_date" type="date" class="w-full px-3 py-2 border rounded-md" required @change="calculateTotalDays" />
                                <div v-if="form.errors.end_date" class="text-red-500 text-sm mt-1">{{ form.errors.end_date }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Lý do <span class="text-destructive">*</span></label>
                            <textarea v-model="form.reason" class="w-full px-3 py-2 border rounded-md" rows="3" required></textarea>
                            <div v-if="form.errors.reason" class="text-red-500 text-sm mt-1">{{ form.errors.reason }}</div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <Link href="/leaves/requests">
                                <Button variant="outline" type="button">Hủy</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Đang gửi...' : 'Gửi yêu cầu' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>