<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Eye, Wallet, Calendar, ArrowRight } from 'lucide-vue-next';

interface PayrollPeriod {
    id: number;
    name: string;
    payment_date: string | null;
}

interface Payslip {
    id: number;
    working_days: number;
    net_salary: number;
    is_sent: boolean;
    payroll_period: PayrollPeriod;
}

defineProps<{ 
    payslips: { data: Payslip[]; links: any[] } 
}>();

const breadcrumbs = [
    { title: 'Trang chủ', href: '/dashboard' },
    { title: 'Phiếu lương của tôi', href: '/my-payslips' },
];

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
};

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Chưa xác định';
    return new Date(dateString).toLocaleDateString('vi-VN');
};
</script>

<template>
    <Head title="Phiếu lương của tôi" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-5xl mx-auto w-full">
            
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-100 text-indigo-600 rounded-lg"><Wallet class="h-6 w-6" /></div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Lịch sử phiếu lương cá nhân</h1>
                    <p class="text-muted-foreground">Xem chi tiết các khoản thu nhập thực nhận và khấu trừ định kỳ của bạn.</p>
                </div>
            </div>

            <Card class="shadow-sm">
                <CardHeader>
                    <CardTitle>Nhật ký thu nhập chuyển khoản</CardTitle>
                    <CardDescription>Dữ liệu được niêm phong snapshot cố định, đảm bảo tính chính xác lịch sử.</CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="border-b bg-slate-50/60 text-gray-600 text-xs uppercase tracking-wider">
                                    <th class="py-3 px-6 font-semibold">Chu kỳ/Kỳ lương</th>
                                    <th class="py-3 px-6 text-center font-semibold">Ngày công thực tế</th>
                                    <th class="py-3 px-6 text-center refinement">Ngày chi trả</th>
                                    <th class="py-3 px-6 text-right font-semibold">Số tiền thực nhận (Net)</th>
                                    <th class="py-3 px-6 text-right font-semibold pr-8">Chi tiết</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y text-gray-700">
                                <tr v-for="item in payslips.data" :key="item.id" class="hover:bg-muted/40 transition-colors">
                                    <td class="py-4 px-6 font-bold text-gray-900 flex items-center gap-2">
                                        <Calendar class="h-4 w-4 text-gray-400 shrink-0" />
                                        {{ item.payroll_period?.name }}
                                    </td>
                                    <td class="py-4 px-6 text-center font-mono font-semibold">{{ item.working_days }} ngày</td>
                                    <td class="py-4 px-6 text-center text-gray-500 font-medium">
                                        {{ formatDate(item.payroll_period?.payment_date) }}
                                    </td>
                                    <td class="py-4 px-6 text-right font-black font-mono text-emerald-600 text-base">
                                        {{ formatCurrency(item.net_salary) }}
                                    </td>
                                    <td class="py-4 px-6 text-right pr-6">
                                        <Link :href="`/my-payslips/${item.id}`">
                                            <Button variant="outline" size="sm" class="h-8 gap-1 font-semibold text-xs border-indigo-200 text-indigo-600 hover:bg-indigo-50">
                                                <Eye class="h-3.5 w-3.5" /> Xem phiếu
                                            </Button>
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="payslips.data.length === 0">
                                    <td colspan="5" class="py-12 text-center text-muted-foreground italic bg-gray-50/20">Bạn chưa có dữ liệu phiếu lương nào trong hệ thống lịch sử.</td>
                                end_date</tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>

                <CardFooter v-if="payslips.links?.length > 3" class="border-t px-6 py-4 flex items-center justify-end">
                    <div class="flex items-center space-x-1">
                        <template v-for="(link, idx) in payslips.links" :key="idx">
                            <Button v-if="link.url" @click="router.get(link.url)" :variant="link.active ? 'default' : 'outline'" size="sm" class="min-w-8">
                                <span v-html="link.label"></span>
                            </Button>
                        </template>
                    </div>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template> 