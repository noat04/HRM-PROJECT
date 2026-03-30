<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const props = defineProps<{ payslip: any; employees: any[]; payrollPeriods: any[] }>();

const form = useForm({
    payroll_period_id: props.payslip.payroll_period_id,
    employee_id: props.payslip.employee_id,
    working_days: props.payslip.working_days,
    gross_salary: props.payslip.gross_salary,
    total_deduction: props.payslip.total_deduction,
    net_salary: props.payslip.net_salary,
});

const calculateNet = () => {
    form.net_salary = Number(form.gross_salary) - Number(form.total_deduction);
};

const submit = () => form.put(`/payslips/${props.payslip.id}`);

const breadcrumbs = [
    { title: 'Phiếu lương', href: '/payslips' },
    { title: 'Chỉnh sửa', href: '#' },
];
</script>

<template>
    <Head title="Chỉnh sửa Phiếu lương" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            <h1 class="text-2xl font-bold tracking-tight">Chỉnh sửa Phiếu lương</h1>
            <Card>
                <CardHeader><CardTitle>Cập nhật số liệu</CardTitle></CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Kỳ lương</label>
                                <select v-model="form.payroll_period_id" class="w-full px-3 py-2 border rounded-md" required>
                                    <option v-for="period in payrollPeriods" :key="period.id" :value="period.id">{{ period.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Nhân viên</label>
                                <select v-model="form.employee_id" class="w-full px-3 py-2 border rounded-md" required>
                                    <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.full_name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Số ngày công thực tế</label>
                                <input v-model="form.working_days" type="number" step="0.5" min="0" class="w-full px-3 py-2 border rounded-md" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-t pt-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Tổng thu nhập (Gross)</label>
                                <input v-model="form.gross_salary" type="number" min="0" @input="calculateNet" class="w-full px-3 py-2 border rounded-md text-emerald-600 font-bold" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Tổng khấu trừ</label>
                                <input v-model="form.total_deduction" type="number" min="0" @input="calculateNet" class="w-full px-3 py-2 border rounded-md text-red-600 font-bold" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Thực nhận (Net)</label>
                                <input v-model="form.net_salary" type="number" min="0" class="w-full px-3 py-2 border rounded-md bg-slate-50 font-bold text-blue-600" required readonly />
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-4">
                            <Link href="/payslips"><Button variant="outline" type="button">Hủy</Button></Link>
                            <Button type="submit" :disabled="form.processing">Lưu thay đổi</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>