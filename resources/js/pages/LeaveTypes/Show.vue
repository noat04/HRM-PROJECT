<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
const props = defineProps<{
    leaveType: {
        id: number,
        name: string,
        code: string,
        days_allowed: number,
        is_paid: boolean,
        is_active: boolean,
        description: string,
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Loại nghỉ phép', href: '/leaves/types' },
    { title: props.leaveType.name, href: '#' },
];

</script>

<template>
    <Head title="Xem chi tiết Loại nghỉ phép" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ props.leaveType.name }}</h1>
                    <p class="text-muted-foreground">Xem chi tiết loại nghỉ phép.</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="`/leaves/types/${props.leaveType.id}/edit`">
                        <Button variant="outline">Chỉnh sửa</Button>
                    </Link>
                    <Link :href="`/leaves/types`">
                        <Button variant="secondary">Quay lại</Button>
                    </Link>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Thông tin chi tiết</CardTitle>
                </CardHeader>
                
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm font-medium text-muted-foreground">Tên Loại</span>
                            <p class="text-lg font-semibold">{{ props.leaveType.name }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-muted-foreground">Mã Loại</span>
                            <p class="text-lg font-semibold">{{ props.leaveType.code }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm font-medium text-muted-foreground">Số ngày cho phép</span>
                            <p class="text-lg font-semibold">{{ props.leaveType.days_allowed }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-muted-foreground">Loại nghỉ phép</span>
                            <p class="text-lg font-semibold">
                                {{ props.leaveType.is_paid ? 'Có lương' : 'Không lương' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-muted-foreground">Trạng thái</span>
                            <p class="text-lg font-semibold">
                                {{ props.leaveType.is_active ? 'Hoạt động' : 'Không hoạt động' }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <span class="text-sm font-medium text-muted-foreground">Mô tả</span>
                        <p class="text-lg mt-1">{{ props.leaveType.description || 'Không có mô tả' }}</p>
                    </div>
                </CardContent>
            </Card>

        </div>
    </AppLayout>
</template>