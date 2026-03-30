<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';

const props = defineProps<{ employees: any[]; payrollPeriods: any[] }>();

const form = useForm({
    payroll_period_id: '',
    employee_id: '',
    working_days: 0,
    gross_salary: 0,
    total_deduction: 0,
    net_salary: 0,
});

// Tự động tính Net Salary khi Gross hoặc Deduction thay đổi
const calculateNet = () => {
    form.net_salary = Number(form.gross_salary) - Number(form.total_deduction);
};

const submit = () => form.post('/payslips');

const breadcrumbs = [
    { title: 'Phiếu lương', href: '/payslips' },
    { title: 'Tạo thủ công', href: '/payslips/create' },
];
</script>

<template>
    <Head title="Tạo Phiếu lương" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            <h1 class="text-2xl font-bold tracking-tight">Tạo Phiếu lương thủ công</h1>
            <Card>
                <CardHeader>
                    <CardTitle>Thông tin cơ bản</CardTitle>
                    <CardDescription>Nhập trực tiếp các con số tổng quát (Gross, Net).</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Kỳ lương <span class="text-destructive">*</span></label>
                                <select v-model="form.payroll_period_id" class="w-full px-3 py-2 border rounded-md" required>
                                    <option value="">Chọn kỳ lương</option>
                                    <option v-for="period in payrollPeriods" :key="period.id" :value="period.id">{{ period.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Nhân viên <span class="text-destructive">*</span></label>
                                <select v-model="form.employee_id" class="w-full px-3 py-2 border rounded-md" required>
                                    <option value="">Chọn nhân viên</option>
                                    <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.full_name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Số ngày công thực tế <span class="text-destructive">*</span></label>
                                <input v-model="form.working_days" type="number" step="0.5" min="0" class="w-full px-3 py-2 border rounded-md" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-t pt-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Tổng thu nhập (Gross) <span class="text-destructive">*</span></label>
                                <input v-model="form.gross_salary" type="number" min="0" @input="calculateNet" class="w-full px-3 py-2 border rounded-md text-emerald-600 font-bold" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Tổng khấu trừ <span class="text-destructive">*</span></label>
                                <input v-model="form.total_deduction" type="number" min="0" @input="calculateNet" class="w-full px-3 py-2 border rounded-md text-red-600 font-bold" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Thực nhận (Net) <span class="text-destructive">*</span></label>
                                <input v-model="form.net_salary" type="number" min="0" class="w-full px-3 py-2 border rounded-md bg-slate-50 font-bold text-blue-600" required readonly />
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-4">
                            <Link href="/payslips"><Button variant="outline" type="button">Hủy</Button></Link>
                            <Button type="submit" :disabled="form.processing">Lưu dữ liệu</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>