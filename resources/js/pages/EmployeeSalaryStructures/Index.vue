<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Pencil, Trash2, Eye, Plus } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{ salaryStructures: any }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Trang chủ', href: '/dashboard' },
    { title: 'Cơ cấu lương', href: '/salary-structures' },
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
    if (confirm('Bạn có chắc chắn muốn xóa cơ cấu lương này?')) {
        router.delete(`/salary-structures/${id}`);
    }
};

// Hàm định dạng tiền tệ VNĐ
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
};
</script>

<template>
    <Head title="Cơ cấu lương nhân viên" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-7xl mx-auto w-full">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Cơ cấu lương</h1>
                    <p class="text-muted-foreground">Quản lý mức lương và các phụ cấp của nhân viên.</p>
                </div>
                <Link href="/salary-structures/create">
                    <Button><Plus class="mr-2 h-4 w-4" /> Thêm mới</Button>
                </Link>
            </div>

            <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-300" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                <div v-if="flashSuccess && showFlash" class="flex items-center rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-200 text-emerald-800 shadow-sm">
                    <span class="font-medium">{{ flashSuccess }}</span>
                </div>
            </Transition>

            <Card>
                <CardHeader><CardTitle>Danh sách Cơ cấu lương</CardTitle></CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4 font-medium">Nhân viên</th>
                                    <th class="text-left py-3 px-4 font-medium">Thành phần lương</th>
                                    <th class="text-right py-3 px-4 font-medium">Số tiền</th>
                                    <th class="text-left py-3 px-4 font-medium">Ngày áp dụng</th>
                                    <th class="text-right py-3 px-4 font-medium">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in salaryStructures?.data" :key="item.id" class="border-b hover:bg-muted/50">
                                    <td class="py-3 px-4 font-semibold text-primary">{{ item.employee?.full_name }}</td>
                                    <td class="py-3 px-4">{{ item.component?.name }}</td>
                                    <td class="py-3 px-4 text-right font-medium">{{ formatCurrency(item.amount) }}</td>
                                    <td class="py-3 px-4">{{ new Date(item.effective_date).toLocaleDateString('vi-VN') }}</td>
                                    <td class="py-3 px-4 text-right flex justify-end gap-2">
                                        <Link :href="`/salary-structures/${item.id}`"><Button variant="ghost" size="icon"><Eye class="h-4 w-4" /></Button></Link>
                                        <Link :href="`/salary-structures/${item.id}/edit`"><Button variant="ghost" size="icon"><Pencil class="h-4 w-4" /></Button></Link>
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