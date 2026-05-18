<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Pencil, Trash2, Eye, Plus, XCircle, CheckCircle2 } from 'lucide-vue-next'; // Import icon báo lỗi
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{ payrollPeriods: any }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Trang chủ', href: '/dashboard' },
    { title: 'Kỳ lương', href: '/payroll-periods' },
];

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const flashError = computed(() => (page.props as any).flash?.error); // 💡 BẮT LỖI XÓA SỔ

const showFlashSuccess = ref(false);
const showFlashError = ref(false);

watch(flashSuccess, (msg) => { if (msg) { showFlashSuccess.value = true; setTimeout(() => showFlashSuccess.value = false, 4000); } }, { immediate: true });
watch(flashError, (msg) => { if (msg) { showFlashError.value = true; setTimeout(() => showFlashError.value = false, 5000); } }, { immediate: true });

const deleteItem = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn xóa kỳ lương này? Chú ý: Toàn bộ phiếu lương liên quan sẽ bị biến mất!')) {
        router.delete(`/payroll-periods/${id}`, {
            preserveScroll: true,
            onError: () => { showFlashError.value = true; }
        });
    }
};

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Chưa xác định';
    return new Date(dateString).toLocaleDateString('vi-VN');
};

const getStatusClass = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'draft': return 'bg-amber-100 text-amber-700 border-amber-200';
        case 'locked': return 'bg-blue-100 text-blue-700 border-blue-200';
        case 'paid': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        default: return 'bg-gray-100 text-gray-700 border-gray-200';
    }
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

            <div v-if="flashSuccess && showFlashSuccess" class="flex items-center rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-200 text-emerald-800 shadow-sm">
                <span class="font-medium">{{ flashSuccess }}</span>
            </div>

            <div v-if="flashError && showFlashError" class="flex items-center rounded-lg bg-rose-50 px-4 py-3 border border-rose-200 text-rose-800 shadow-sm">
                <XCircle class="h-4 w-4 text-rose-600 mr-2 shrink-0" />
                <span class="font-medium">Không thể thực hiện: {{ flashError }}</span>
            </div>

            <Card>
                <CardHeader><CardTitle>Danh sách Kỳ lương</CardTitle></CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="border-b bg-slate-50/50">
                                    <th class="py-3 px-4 font-medium">Tên / Mã</th>
                                    <th class="py-3 px-4 font-medium">Thời gian</th>
                                    <th class="text-center py-3 px-4 font-medium">Ngày công chuẩn</th>
                                    <th class="py-3 px-4 font-medium">Ngày thanh toán</th>
                                    <th class="py-3 px-4 font-medium">Trạng thái</th>
                                    <th class="text-right py-3 px-4 font-medium">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in payrollPeriods?.data" :key="item.id" class="border-b hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-primary">{{ item.name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ item.code }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ formatDate(item.start_date) }} - {{ formatDate(item.end_date) }}
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-indigo-600">
                                        {{ item.standard_working_days || 'Tính toán...' }} ngày
                                    </td>
                                    <td class="py-3 px-4">{{ formatDate(item.payment_date) }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-0.5 rounded text-xs font-bold border uppercase" :class="getStatusClass(item.status)">
                                            {{ item.status === 'draft' ? 'Nháp' : (item.status === 'locked' ? 'Đã chốt' : 'Đã trả') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right flex justify-end gap-1">
                                        <Link :href="`/payroll-periods/${item.id}`"><Button variant="ghost" size="icon"><Eye class="h-4 w-4 text-gray-500" /></Button></Link>
                                        
                                        <Link v-if="item.status === 'draft'" :href="`/payroll-periods/${item.id}/edit`"><Button variant="ghost" size="icon"><Pencil class="h-4 w-4 text-blue-500" /></Button></Link>
                                        <Button v-if="item.status === 'draft'" variant="ghost" size="icon" class="text-destructive" @click="deleteItem(item.id)"><Trash2 class="h-4 w-4" /></Button>
                                        <span v-else class="text-xs italic text-muted-foreground pr-2 self-center">Đã khóa sổ</span>
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