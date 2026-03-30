<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Pencil, Trash2, Eye, Plus } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface LeaveBalance {
    id: number;
    employee_id: number;
    leave_type_id: number;
    year: number;
    total_days: number;
    used_days: number;
    remaining_days: number;
}

interface PaginatedLeaveBalances {
    data: LeaveBalance[];
    current_page: number;
    total: number | 0;
    per_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const breadcrumbs:BreadcrumbItem[] = [
    { title: 'Trang chủ', href: '/dashboard' },
    { title: 'Số dư nghỉ phép', href: '/leaves/balances' },
];

const props = defineProps<{
    leaveBalances: PaginatedLeaveBalances;
}>();

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

const deleteLeaveBalance = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn xóa số dư nghỉ phép này?')) {
        router.delete(`/leaves/balances/${id}`);
    }
};

</script>

<template>
    <Head title="Số dư nghỉ phép" />

     <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-7xl mx-auto w-full">
            
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Quản lý Số dư nghỉ phép</h1>
                    <p class="text-muted-foreground">Danh sách các số dư nghỉ phép trong hệ thống.</p>
                </div>
                <Link href="/leaves/balances/create">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Tạo Số dư nghỉ phép mới
                    </Button>
                </Link>
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
                    <CardTitle>Danh sách Loại nghỉ phép</CardTitle>
                    <CardDescription>
                        Tổng số loại nghỉ phép: {{ leaveBalances.total }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4 font-medium">Tên Nhân viên</th>
                                    <th class="text-left py-3 px-4 font-medium">Loại nghỉ phép</th>
                                    <th class="text-left py-3 px-4 font-medium">Năm</th>
                                    <th class="text-left py-3 px-4 font-medium">Tổng số ngày</th>
                                    <th class="text-left py-3 px-4 font-medium">Số ngày đã sử dụng</th>
                                    <th class="text-left py-3 px-4 font-medium">Số ngày còn lại</th>
                                    <th class="text-right py-3 px-4 font-medium">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="leaveType in leaveBalances.data" :key="leaveType.id" class="border-b hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-primary">{{ leaveType.employee_id }}</div>
                                        <div class="text-xs text-muted-foreground">{{ leaveType.leave_type_id }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ leaveType.year }}</td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ leaveType.total_days }}</td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ leaveType.used_days }}</td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ leaveType.remaining_days }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="`/leaves/balances/${leaveType.id}`">
                                                <Button variant="ghost" size="icon" title="Xem chi tiết">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="`/leaves/balances/${leaveType.id}/edit`">
                                                <Button variant="ghost" size="icon" title="Sửa">
                                                    <Pencil class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button variant="ghost" size="icon" title="Xóa" @click="deleteLeaveBalance(leaveType.id)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
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