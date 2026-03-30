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

const props = defineProps<{
    leaveBalance: {
        id: number;
        employee_id: number;
        leave_type_id: number;
        year: number;
        total_days: number;
        used_days: number;
        remaining_days: number;
    };
    employees: Employee[];
    leaveTypes: LeaveType[];
}>();

console.log(props.leaveBalance);

const form = useForm({
    employee_id: props.leaveBalance.employee_id,
    leave_type_id: props.leaveBalance.leave_type_id,
    year: props.leaveBalance.year,
    total_days: props.leaveBalance.total_days,
    used_days: props.leaveBalance.used_days,
    remaining_days: props.leaveBalance.remaining_days,
});

const submitForm = () => {
    form.put(`/leaves/balances/${props.leaveBalance.id}`);
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Số dư nghỉ phép', href: '/leaves/balances' },
    { title: 'Chỉnh sửa', href: '#' },
];

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
</script>
/script>
<template>
    <Head title="Tạo mới Số dư nghỉ phép" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Cập nhật Số dư nghỉ phép</h1>
                    <p class="text-muted-foreground">Cập nhật số dư nghỉ phép vào hệ thống.</p>
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
                    <CardTitle>Thông tin Số dư nghỉ phép</CardTitle>
                    <CardDescription>Nhập đầy đủ các thông tin bắt buộc có dấu (*).</CardDescription>
                </CardHeader>
                
                <CardContent>
                    <form @submit.prevent="submitForm" class="space-y-6">
                        
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
                                <label class="block text-sm font-medium mb-2">Loại nghỉ phép <span class="text-destructive">*</span></label>
                                <select v-model="form.leave_type_id" class="w-full px-3 py-2 border rounded-md" required>
                                    <option value="">Chọn loại nghỉ phép</option>
                                    <option v-for="leaveType in leaveTypes" :key="leaveType.id" :value="leaveType.id">{{ leaveType.name }}</option>
                                </select>
                                <div v-if="form.errors.leave_type_id" class="text-red-500 text-sm mt-1">{{ form.errors.leave_type_id }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Năm <span class="text-destructive">*</span></label>
                                <input v-model="form.year" type="number" min="1900" max="2100" class="w-full px-3 py-2 border rounded-md" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Tổng số ngày <span class="text-destructive">*</span></label>
                                <input v-model="form.total_days" type="number" min="0" class="w-full px-3 py-2 border rounded-md" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Số ngày đã sử dụng <span class="text-destructive">*</span></label>
                                <input v-model="form.used_days" type="number" min="0" class="w-full px-3 py-2 border rounded-md" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 border-b pb-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Số ngày còn lại</label>
                                <input v-model="form.remaining_days" type="number" min="0" class="w-full px-3 py-2 border rounded-md" readonly />
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button type="button" variant="outline" as-child>
                                <Link href="/leaves/balances">Hủy</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Đang xử lý...' : 'Lưu thông tin' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>