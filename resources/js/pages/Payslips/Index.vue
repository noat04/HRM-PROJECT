<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Pencil, Trash2, Eye, Plus, Calculator, Loader2, CheckCircle2, XCircle, FileSpreadsheet } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface PayrollPeriod {
    id: number;
    name: string;
}

interface Payslip {
    id: number;
    employee: {
        full_name: string;
        employee_code: string;
    };
    payroll_period: PayrollPeriod;
    working_days: number;
    net_salary: number;
    is_sent: boolean;
}

const props = defineProps<{ 
    payslips: { data: Payslip[]; links: any[]; total: number }; 
    payrollPeriods: any[] 
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Trang chủ', href: '/dashboard' },
    { title: 'Phiếu lương tổng', href: '/payslips' },
];

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const flashError = computed(() => (page.props as any).flash?.error); // 💡 ĐÃ BỔ SUNG: Bắt lỗi nghiệp vụ lương

const showFlashSuccess = ref(false);
const showFlashError = ref(false);

watch(flashSuccess, (msg) => { if (msg) { showFlashSuccess.value = true; setTimeout(() => showFlashSuccess.value = false, 4000); } }, { immediate: true });
watch(flashError, (msg) => { if (msg) { showFlashError.value = true; setTimeout(() => showFlashError.value = false, 5000); } }, { immediate: true });

const deleteItem = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn xóa phiếu lương này? Cột mốc snapshot quá khứ sẽ bị mất.')) {
        router.delete(`/payslips/${id}`, { preserveScroll: true });
    }
};

// ĐỂ GỌI HÀM SINH LƯƠNG TỰ ĐỘNG
const generateForm = useForm({
    payroll_period_id: ''
});

const generatePayroll = () => {
    if (!generateForm.payroll_period_id) {
        alert('Vui lòng chọn Kỳ lương cần tính toán trước trước!');
        return;
    }
    
    if (confirm('Hệ thống sẽ tự động tính toán lương và snapshot thông tin cho TẤT CẢ nhân viên thuộc kỳ này. Tiếp tục?')) {
        // 💡 ĐÃ SỬA: Chuyển URL trỏ đúng về cụm động cơ C&B: /payroll-periods/{id}/generate
        generateForm.post(`/payroll-periods/${generateForm.payroll_period_id}/generate`, {
            preserveScroll: true,
            onSuccess: () => {
                generateForm.reset();
            },
            onError: () => {
                showFlashError.value = true;
            }
        });
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
};
</script>

<template>
    <Head title="Quản lý Phiếu lương tổng" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-7xl mx-auto w-full">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg"><FileSpreadsheet class="h-6 w-6" /></div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Phiếu lương nhân viên toàn công ty</h1>
                        <p class="text-muted-foreground">Bảng tổng hợp kết toán thu nhập và snapshot chi tiết lương định kỳ.</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 bg-white p-2 rounded-lg border shadow-sm max-w-xl">
                    <select v-model="generateForm.payroll_period_id" class="px-3 py-2 text-sm border rounded-md min-w-[200px] bg-white text-gray-700 font-medium">
                        <option value="">-- Chọn Đợt Tính Lương --</option>
                        <option v-for="period in payrollPeriods" :key="period.id" :value="period.id">
                            {{ period.name }} ({{ period.code }})
                        </option>
                    </select>
                    
                    <Button @click="generatePayroll" :disabled="generateForm.processing" class="bg-indigo-600 hover:bg-indigo-700 font-bold">
                        <Loader2 v-if="generateForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                        <Calculator class="mr-2 h-4 w-4" />
                        {{ generateForm.processing ? 'Đang kết toán...' : 'Tính lương Tự động' }}
                    </Button>

                    <div class="w-px h-8 bg-gray-200 hidden md:block mx-1"></div>

                    <Link href="/payslips/create">
                        <Button variant="outline" class="font-semibold"><Plus class="mr-1.5 h-4 w-4" /> Tạo thủ công</Button>
                    </Link>
                </div>
            </div>

            <div v-if="flashSuccess && showFlashSuccess" class="flex items-center rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-200 text-emerald-800 shadow-sm">
                <CheckCircle2 class="h-4 w-4 text-emerald-600 mr-2 shrink-0" />
                <span class="font-medium">{{ flashSuccess }}</span>
            </div>
            
            <div v-if="flashError && showFlashError" class="flex items-start rounded-lg bg-rose-50 px-4 py-3 border border-rose-200 text-rose-800 shadow-sm">
                <XCircle class="h-4 w-4 text-rose-600 mr-2 shrink-0 mt-0.5" />
                <div><strong class="font-bold">Lỗi Engine: </strong><span class="font-medium">{{ flashError }}</span></div>
            </div>

            <Card class="shadow-sm">
                <CardHeader class="pb-3">
                    <CardTitle>Bảng kê chi tiết thu nhập thực tế</CardTitle>
                    <CardDescription>Hiển thị danh sách tổng số {{ payslips?.total || 0 }} bản ghi kết toán.</CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b bg-slate-50/70 text-gray-600 text-xs uppercase tracking-wider">
                                    <th class="text-left py-3 px-6 font-semibold">Mã số & Họ tên nhân viên</th>
                                    <th class="text-left py-3 px-6 font-semibold">Chu kỳ kết toán</th>
                                    <th class="text-center py-3 px-6 font-semibold">Số ngày công</th>
                                    <th class="text-right py-3 px-6 font-semibold">Thực nhận chuyển khoản (Net)</th>
                                    <th class="text-center py-3 px-6 font-semibold">Trạng thái phát hành</th>
                                    <th class="text-right py-3 px-6 font-semibold pr-6">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y text-gray-700">
                                <tr v-for="item in payslips?.data" :key="item.id" class="hover:bg-muted/40 transition-colors">
                                    <td class="py-3 px-6">
                                        <div class="font-bold text-gray-900">{{ item.employee?.full_name }}</div>
                                        <div class="text-[11px] font-mono text-primary font-bold">{{ item.employee?.employee_code || 'EMP-' + item.id }}</div>
                                    </td>
                                    <td class="py-3 px-6 text-gray-600 font-medium">
                                        {{ item.payroll_period?.name || item.payrollPeriod?.name || 'Kỳ lương hiện tại' }}
                                    </td>
                                    <td class="py-3 px-6 text-center font-mono font-bold text-gray-800">{{ item.working_days }} ngày</td>
                                    <td class="py-3 px-6 text-right font-black font-mono text-emerald-600 text-base">
                                        {{ formatCurrency(item.net_salary) }}
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        <span v-if="item.is_sent" class="px-2.5 py-0.5 rounded text-[11px] font-bold border border-emerald-200 bg-emerald-50 text-emerald-700 uppercase">Đã Mail</span>
                                        <span v-else class="px-2.5 py-0.5 rounded text-[11px] font-bold border border-amber-200 bg-amber-50 text-amber-700 uppercase">Chờ duyệt</span>
                                    </td>
                                    <td class="py-3 px-6 text-right">
                                        <div class="flex justify-end gap-1">
                                            <Link :href="`/payslips/${item.id}`"><Button variant="ghost" size="icon" class="h-8 w-8" title="Xem bảng lương snapshot chi tiết"><Eye class="h-4 w-4 text-gray-500" /></Button></Link>
                                            <Link :href="`/payslips/${item.id}/edit`"><Button variant="ghost" size="icon" class="h-8 w-8"><Pencil class="h-4 w-4 text-blue-500" /></Button></Link>
                                            <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="deleteItem(item.id)"><Trash2 class="h-4 w-4" /></Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!payslips?.data || payslips.data.length === 0">
                                    <td colspan="6" class="py-12 text-center text-muted-foreground italic bg-gray-50/30">Chưa có dữ liệu phiếu lương được tính. Hãy chọn một kỳ lương ở trên để kích hoạt máy tính tự động.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>

                <CardFooter v-if="payslips?.links?.length > 3" class="border-t px-6 py-4 flex items-center justify-end">
                    <div class="flex items-center space-x-1">
                        <template v-for="(link, idx) in payslips.links" :key="idx">
                            <Button v-if="link.url" @click="router.get(link.url, {}, { preserveState: true })" :variant="link.active ? 'default' : 'outline'" size="sm" class="min-w-8 font-bold">
                                <span v-html="link.label"></span>
                            </Button>
                            <Button v-else variant="ghost" size="sm" disabled class="min-w-8 text-gray-300">
                                <span v-html="link.label"></span>
                            </Button>
                        </template>
                    </div>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>