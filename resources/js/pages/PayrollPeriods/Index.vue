<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Pencil, Trash2, Eye, Plus } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{ payrollPeriods: any }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Trang chủ', href: '/dashboard' },
    { title: 'Kỳ lương', href: '/payroll-periods' },
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

const deleteItem = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn xóa kỳ lương này? Chú ý: Các dữ liệu liên quan có thể bị ảnh hưởng!')) {
        router.delete(`/payroll-periods/${id}`);
    }
};

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Chưa xác định';
    return new Date(dateString).toLocaleDateString('vi-VN');
};
</script>

<template>
    <Head title="Quản lý Kỳ lương" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-7xl mx-auto w-full">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Kỳ lương (Payroll Periods)</h1>
                    <p class="text-muted-foreground">Quản lý các chu kỳ tính lương trong năm.</p>
                </div>
                <Link href="/payroll-periods/create">
                    <Button><Plus class="mr-2 h-4 w-4" /> Tạo Kỳ lương mới</Button>
                </Link>
            </div>

            <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-300" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                <div v-if="flashSuccess && showFlash" class="flex items-center rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-200 text-emerald-800 shadow-sm">
                    <span class="font-medium">{{ flashSuccess }}</span>
                </div>
            </Transition>

            <Card>
                <CardHeader><CardTitle>Danh sách Kỳ lương</CardTitle></CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b bg-slate-50/50">
                                    <th class="text-left py-3 px-4 font-medium">Tên / Mã</th>
                                    <th class="text-left py-3 px-4 font-medium">Thời gian</th>
                                    <th class="text-center py-3 px-4 font-medium">Ngày công chuẩn</th>
                                    <th class="text-left py-3 px-4 font-medium">Ngày thanh toán</th>
                                    <th class="text-left py-3 px-4 font-medium">Trạng thái</th>
                                    <th class="text-right py-3 px-4 font-medium">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in payrollPeriods?.data" :key="item.id" class="border-b hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-primary">{{ item.name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ item.code }}</div>
                                    </td>
                                    <td class="py-3 px-4 font-medium">
                                        {{ formatDate(item.start_date) }} - {{ formatDate(item.end_date) }}
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-blue-600">{{ item.standard_working_days }}</td>
                                    <td class="py-3 px-4">{{ formatDate(item.payment_date) }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium border bg-slate-100 uppercase">{{ item.status }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right flex justify-end gap-2">
                                        <Link :href="`/payroll-periods/${item.id}`"><Button variant="ghost" size="icon"><Eye class="h-4 w-4" /></Button></Link>
                                        <Link :href="`/payroll-periods/${item.id}/edit`"><Button variant="ghost" size="icon"><Pencil class="h-4 w-4" /></Button></Link>
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