<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const props = defineProps<{
    leaveBalance: {
        id: number;
        employee_id: number;
        leave_type_id: number;
        year: number;
        total_days: number;
        used_days: number;
        remaining_days: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Số dư nghỉ phép', href: '/leaves/balances' },
    { title: 'Xem chi tiết', href: '#' },
];
</script>
<template>
    <Head title="Xem chi tiết Số dư nghỉ phép" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Xem chi tiết Số dư nghỉ phép</h1>
                    <p class="text-muted-foreground">Thông tin chi tiết về số dư nghỉ phép.</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Thông tin Số dư nghỉ phép</CardTitle>
                </CardHeader>
                
                <CardContent>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-muted-foreground">Nhân viên</label>
                                <div class="text-lg font-semibold">{{ leaveBalance.employee_id }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-muted-foreground">Loại nghỉ phép</label>
                                <div class="text-lg font-semibold">{{ leaveBalance.leave_type_id }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-muted-foreground">Năm</label>
                                <div class="text-lg font-semibold">{{ leaveBalance.year }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-muted-foreground">Tổng số ngày</label>
                                <div class="text-lg font-semibold">{{ leaveBalance.total_days }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-muted-foreground">Số ngày đã sử dụng</label>
                                <div class="text-lg font-semibold">{{ leaveBalance.used_days }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 border-t pt-4">
                            <div>
                                <label class="block text-sm font-medium text-muted-foreground">Số ngày còn lại</label>
                                <div class="text-lg font-semibold">{{ leaveBalance.remaining_days }}</div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="flex justify-end gap-2">
                <Button variant="outline" as-child>
                    <Link href="/leaves/balances">Quay lại</Link>
                </Button>
            </div>
        </div>
    </AppLayout>
</template> 