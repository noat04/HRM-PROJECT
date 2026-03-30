<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const props = defineProps<{ payrollPeriod: any }>();

const breadcrumbs = [
    { title: 'Kỳ lương', href: '/payroll-periods' },
    { title: 'Chi tiết', href: '#' },
];

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Chưa có';
    return new Date(dateString).toLocaleDateString('vi-VN');
};
</script>

<template>
    <Head title="Chi tiết Kỳ lương" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight">Chi tiết Kỳ lương</h1>
                <Link :href="`/payroll-periods/${payrollPeriod.id}/edit`">
                    <Button>Chỉnh sửa</Button>
                </Link>
            </div>
            
            <Card>
                <CardHeader><CardTitle>{{ payrollPeriod.name }} ({{ payrollPeriod.code }})</CardTitle></CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4 border-b pb-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Chu kỳ tính lương</p>
                            <p class="font-bold text-primary">{{ formatDate(payrollPeriod.start_date) }} - {{ formatDate(payrollPeriod.end_date) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Ngày công chuẩn (Không tính T7, CN)</p>
                            <p class="font-bold text-xl text-blue-600">{{ payrollPeriod.standard_working_days }} ngày</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Ngày thanh toán dự kiến</p>
                            <p class="font-medium">{{ formatDate(payrollPeriod.payment_date) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Trạng thái hiện tại</p>
                            <p class="font-medium uppercase">{{ payrollPeriod.status }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>