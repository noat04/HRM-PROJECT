<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Edit, Trash2 } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

// Nhận dữ liệu từ Controller
const props = defineProps<{
    position: {
        id: number;
        name: string;
        code: string;
        level: number;
        salary_min: number | null;
        salary_max: number | null;
        description: string;
    }
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Chức vụ', href: '/positions' },
    { title: props.position.name, href: '#' },
];

// Hàm format tiền tệ
const formatCurrency = (value: number | null) => {
    if (!value) return 'N/A';
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};
</script>

<template>
    <Head :title="position.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-3xl mx-auto w-full">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Link href="/positions">
                        <Button variant="outline" size="icon">
                            <ArrowLeft class="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ position.name }}</h1>
                        <p class="text-muted-foreground">Thông tin chi tiết về chức vụ.</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Link :href="`/positions/${position.id}/edit`">
                        <Button variant="outline">
                            <Edit class="mr-2 h-4 w-4" />
                            Chỉnh sửa
                        </Button>
                    </Link>
                    <form @submit.prevent="$emit('delete', position.id)">
                        <Button variant="destructive">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Xóa
                        </Button>
                    </form>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Thông tin cơ bản</CardTitle>
                    <CardDescription>Xem chi tiết thông tin chức vụ.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-sm font-medium text-muted-foreground">Tên Chức vụ</span>
                            <p class="text-lg font-semibold">{{ position.name }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-sm font-medium text-muted-foreground">Mã Chức vụ</span>
                            <p class="text-lg">{{ position.code }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-sm font-medium text-muted-foreground">Cấp bậc</span>
                            <p class="text-lg">
                                <Badge variant="secondary">{{ position.level }}</Badge>
                            </p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-sm font-medium text-muted-foreground">Mức lương</span>
                            <p class="text-lg">
                                {{ formatCurrency(position.salary_min) }} - {{ formatCurrency(position.salary_max) }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <span class="text-sm font-medium text-muted-foreground">Mô tả</span>
                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                            {{ position.description || 'Không có mô tả' }}
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>