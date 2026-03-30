<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const props = defineProps<{ salaryStructure: any }>();

const breadcrumbs = [
    { title: 'Cơ cấu lương', href: '/salary-structures' },
    { title: 'Chi tiết', href: '#' },
];

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
};
</script>

<template>
    <Head title="Chi tiết Cơ cấu lương" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight">Chi tiết Cơ cấu lương</h1>
                <Link :href="`/salary-structures/${salaryStructure.id}/edit`">
                    <Button>Chỉnh sửa</Button>
                </Link>
            </div>
            
            <Card>
                <CardHeader><CardTitle>Thông tin Cơ cấu lương</CardTitle></CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4 border-b pb-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Nhân viên</p>
                            <p class="font-medium text-lg text-primary">{{ salaryStructure.employee?.full_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Thành phần lương</p>
                            <p class="font-medium">{{ salaryStructure.component?.name }} ({{ salaryStructure.component?.code }})</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Số tiền</p>
                            <p class="font-bold text-xl">{{ formatCurrency(salaryStructure.amount) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Ngày bắt đầu áp dụng</p>
                            <p class="font-medium">{{ new Date(salaryStructure.effective_date).toLocaleDateString('vi-VN') }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>