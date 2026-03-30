<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Printer } from 'lucide-vue-next';

const props = defineProps<{ payslip: any }>();

const breadcrumbs = [
    { title: 'Phiếu lương', href: '/payslips' },
    { title: 'Chi tiết phiếu lương', href: '#' },
];

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
};

// Lấy mảng items từ cấu trúc JSON salary_snapshot
const snapshotItems = props.payslip.salary_snapshot?.items || [];
const earnings = snapshotItems.filter((i: any) => i.type === 'earning');
const deductions = snapshotItems.filter((i: any) => i.type === 'deduction');

const printPayslip = () => {
    window.print();
};
</script>

<template>
    <Head title="Chi tiết Phiếu lương" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full print:p-0">
            <div class="flex items-center justify-between print:hidden">
                <h1 class="text-2xl font-bold tracking-tight">Chi tiết Phiếu lương</h1>
                <div class="flex gap-2">
                    <Button variant="outline" @click="printPayslip"><Printer class="mr-2 h-4 w-4"/> In phiếu</Button>
                </div>
            </div>
            
            <Card class="print:shadow-none print:border-none">
                <CardHeader class="text-center border-b pb-6">
                    <CardTitle class="text-2xl uppercase tracking-widest text-primary">Phiếu Lương Nhân Viên</CardTitle>
                    <CardDescription class="text-base font-medium mt-2 text-foreground">
                        Kỳ lương: {{ payslip.payroll_period?.name }}
                    </CardDescription>
                </CardHeader>
                
                <CardContent class="pt-6 space-y-8">
                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-lg">
                        <div>
                            <p class="text-sm text-muted-foreground">Nhân viên:</p>
                            <p class="font-bold text-lg">{{ payslip.employee?.full_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Mã nhân viên:</p>
                            <p class="font-bold">{{ payslip.employee?.employee_code || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Số ngày công chuẩn:</p>
                            <p class="font-medium">{{ payslip.payroll_period?.standard_working_days || 0 }} ngày</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Số ngày công thực tế:</p>
                            <p class="font-bold text-blue-600">{{ payslip.working_days }} ngày</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="font-bold text-emerald-700 mb-3 border-b pb-2 uppercase text-sm">Khoản Thu Nhập (+)</h3>
                            <div v-if="earnings.length > 0" class="space-y-3">
                                <div v-for="(item, idx) in earnings" :key="idx" class="flex justify-between text-sm">
                                    <span>{{ item.name }}</span>
                                    <span class="font-medium">{{ formatCurrency(item.amount) }}</span>
                                </div>
                            </div>
                            <div v-else class="text-sm text-muted-foreground italic">Không có dữ liệu chi tiết (Nhập thủ công)</div>
                        </div>

                        <div>
                            <h3 class="font-bold text-red-700 mb-3 border-b pb-2 uppercase text-sm">Khoản Khấu Trừ (-)</h3>
                            <div v-if="deductions.length > 0" class="space-y-3">
                                <div v-for="(item, idx) in deductions" :key="idx" class="flex justify-between text-sm">
                                    <span>{{ item.name }}</span>
                                    <span class="font-medium">{{ formatCurrency(item.amount) }}</span>
                                </div>
                            </div>
                            <div v-else class="text-sm text-muted-foreground italic">Không có dữ liệu chi tiết (Nhập thủ công)</div>
                        </div>
                    </div>

                    <div class="bg-primary/5 p-6 rounded-lg space-y-3 border border-primary/10">
                        <div class="flex justify-between items-center text-sm font-medium">
                            <span class="text-muted-foreground">Tổng thu nhập (Gross):</span>
                            <span class="text-emerald-700">{{ formatCurrency(payslip.gross_salary) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm font-medium border-b border-primary/10 pb-3">
                            <span class="text-muted-foreground">Tổng khấu trừ:</span>
                            <span class="text-red-700">- {{ formatCurrency(payslip.total_deduction) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xl font-bold pt-2">
                            <span>THỰC NHẬN (NET):</span>
                            <span class="text-primary">{{ formatCurrency(payslip.net_salary) }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style>
@media print {
    body * { visibility: hidden; }
    .print\:p-0, .print\:p-0 * { visibility: visible; }
    .print\:p-0 { position: absolute; left: 0; top: 0; width: 100%; }
}
</style>