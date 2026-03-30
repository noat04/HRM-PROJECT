<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Pencil, Trash2, Eye, Plus, Calculator, Loader2 } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface PayrollPeriod {
    id: number;
    name: string;
}

interface Payslip {
    id: number;
    employee: {
        full_name: string;
    };
    payroll_period: PayrollPeriod;
    working_days: number;
    net_salary: number;
    is_sent: boolean;
}

const props = defineProps<{ payslips: any; payrollPeriods: any[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Trang chủ', href: '/dashboard' },
    { title: 'Phiếu lương', href: '/payslips' },
];

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const showFlash = ref(false);

watch(flashSuccess, (newMessage) => {
    if (newMessage) {
        showFlash.value = true;
        setTimeout(() => showFlash.value = false, 4000);
    }
}, { immediate: true });

const deleteItem = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn xóa phiếu lương này?')) {
        router.delete(`/payslips/${id}`);
    }
};

// 👇 LOGIC ĐỂ GỌI HÀM SINH LƯƠNG TỰ ĐỘNG
const generateForm = useForm({
    payroll_period_id: ''
});

const generatePayroll = () => {
    if (!generateForm.payroll_period_id) {
        alert('Vui lòng chọn Kỳ lương trước khi chạy tự động!');
        return;
    }
    
    if (confirm('Hệ thống sẽ tự động tính toán lương cho TẤT CẢ nhân viên trong kỳ này. Quá trình có thể mất vài giây. Tiếp tục?')) {
        generateForm.post('/payslips/generate', {
            preserveScroll: true,
            onSuccess: () => {
                generateForm.reset();
            }
        });
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
};
</script>

<template>
    <Head title="Quản lý Phiếu lương" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-7xl mx-auto w-full">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Phiếu lương nhân viên</h1>
                    <p class="text-muted-foreground">Quản lý và theo dõi phiếu lương hàng tháng.</p>
                </div>
               <div class="flex items-center gap-3 bg-white p-2 rounded-lg border shadow-sm w-full md:w-auto">
                    <select v-model="generateForm.payroll_period_id" class="px-3 py-2 text-sm border rounded-md min-w-[180px]">
                        <option value="">-- Chọn Kỳ lương --</option>
                        <option v-for="period in payrollPeriods" :key="period.id" :value="period.id">
                            {{ period.name }}
                        </option>
                    </select>
                    
                    <Button @click="generatePayroll" :disabled="generateForm.processing" class="bg-indigo-600 hover:bg-indigo-700">
                        <Loader2 v-if="generateForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                        <Calculator v-else class="mr-2 h-4 w-4" />
                        {{ generateForm.processing ? 'Đang tính...' : 'Tính lương Tự động' }}
                    </Button>

                    <div class="w-px h-8 bg-gray-200 mx-1"></div>

                    <Link href="/payslips/create">
                        <Button variant="outline"><Plus class="mr-2 h-4 w-4" /> Tạo thủ công</Button>
                    </Link>
                </div>
            </div>

            <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-300" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                <div v-if="flashSuccess && showFlash" class="flex items-center rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-200 text-emerald-800 shadow-sm">
                    <span class="font-medium">{{ flashSuccess }}</span>
                </div>
            </Transition>

            <Card>
                <CardHeader><CardTitle>Danh sách Phiếu lương</CardTitle></CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b bg-slate-50/50">
                                    <th class="text-left py-3 px-4 font-medium">Nhân viên</th>
                                    <th class="text-left py-3 px-4 font-medium">Kỳ lương</th>
                                    <th class="text-center py-3 px-4 font-medium">Ngày công</th>
                                    <th class="text-right py-3 px-4 font-medium">Thực nhận (Net)</th>
                                    <th class="text-center py-3 px-4 font-medium">Trạng thái gửi</th>
                                    <th class="text-right py-3 px-4 font-medium">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in payslips?.data" :key="item.id" class="border-b hover:bg-muted/50">
                                    <td class="py-3 px-4 font-semibold text-primary">{{ item.employee?.full_name }}</td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ item.payroll_period?.name }}</td>
                                    <td class="py-3 px-4 text-center">{{ item.working_days }}</td>
                                    <td class="py-3 px-4 text-right font-bold text-emerald-600">{{ formatCurrency(item.net_salary) }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span v-if="item.is_sent" class="px-2 py-1 rounded text-xs bg-emerald-100 text-emerald-700">Đã gửi</span>
                                        <span v-else class="px-2 py-1 rounded text-xs bg-amber-100 text-amber-700">Chưa gửi</span>
                                    </td>
                                    <td class="py-3 px-4 text-right flex justify-end gap-2">
                                        <Link :href="`/payslips/${item.id}`"><Button variant="ghost" size="icon" title="Xem chi tiết"><Eye class="h-4 w-4" /></Button></Link>
                                        <Link :href="`/payslips/${item.id}/edit`"><Button variant="ghost" size="icon"><Pencil class="h-4 w-4" /></Button></Link>
                                        <Button variant="ghost" size="icon" class="text-destructive" @click="deleteItem(item.id)"><Trash2 class="h-4 w-4" /></Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>