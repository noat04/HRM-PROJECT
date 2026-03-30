<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const props = defineProps<{ salaryComponent: any }>();

const breadcrumbs = [
    { title: 'Thành phần lương', href: '/salary-components' },
    { title: 'Chi tiết', href: '#' },
];
</script>

<template>
    <Head title="Chi tiết Thành phần lương" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight">Chi tiết Thành phần lương</h1>
                <Link :href="`/salary-components/${salaryComponent.id}/edit`">
                    <Button>Chỉnh sửa</Button>
                </Link>
            </div>
            
            <Card>
                <CardHeader><CardTitle>Thông tin: {{ salaryComponent.name }}</CardTitle></CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4 border-b pb-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Mã Code</p>
                            <p class="font-medium">{{ salaryComponent.code }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Loại</p>
                            <p class="font-medium">{{ salaryComponent.type === 'earning' ? 'Thu nhập' : 'Khấu trừ' }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 border-b pb-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Kiểu tính</p>
                            <p class="font-medium">{{ salaryComponent.calculation_type === 'fixed' ? 'Cố định' : 'Phần trăm' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Giá trị mặc định</p>
                            <p class="font-medium">{{ salaryComponent.default_value || 0 }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 border-b pb-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Tính thuế TNCN</p>
                            <p class="font-medium">{{ salaryComponent.is_taxable ? 'Có' : 'Không' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Trạng thái</p>
                            <p class="font-medium text-emerald-600" v-if="salaryComponent.is_active">Hoạt động</p>
                            <p class="font-medium text-red-600" v-else>Ngừng hoạt động</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Ghi chú</p>
                        <p class="mt-1">{{ salaryComponent.description || 'Không có ghi chú.' }}</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>