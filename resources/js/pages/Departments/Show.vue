<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router,usePage } from '@inertiajs/vue3';
import { computed } from 'vue'; // 👇 Import computed của Vue
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { Pencil, Trash2, Eye, Plus } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    department: {
        id: number;
        name: string;
        parent_id: number | null;
        manager_id: number | null;
        description: string;
        level: number;
        created_at: EpochTimeStamp;
        updated_at: EpochTimeStamp;
        deleted_at: EpochTimeStamp;
    }
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Phòng ban', href: '/departments' },
    { title: 'Chi tiết', href: '#' },
];

const deleteDepartment = () => {
    if (confirm('Bạn có chắc chắn muốn xóa phòng ban này?')) {
        router.delete(`/departments/${props.department.id}`);
    }
};

const formatDate = (timestamp: EpochTimeStamp | null | undefined): string | null => {
    if (!timestamp) return null;
    const isSeconds = timestamp.toString().length <= 10;
    const date = new Date(isSeconds ? (timestamp as number) * 1000 : (timestamp as number));
    return new Intl.DateTimeFormat('vi-VN', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    }).format(date);
};
</script>
<template>
    <Head :title="`Chi tiết - ${department.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-3xl mx-auto w-full">
            <div class="flex items-center gap-4">
                <Link href="/departments">
                    <Button variant="outline" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Chi tiết Phòng ban</h1>
                    <p class="text-muted-foreground">Xem thông tin chi tiết của {{ department.name }}.</p>
                </div>
            </div>

            <Card>
                <CardContent class="pt-6">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-muted-foreground">Tên Phòng ban</label>
                            <p class="text-lg font-semibold">{{ department.name }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-muted-foreground">ID Quản lý</label>
                                <p class="text-lg">{{ department.manager_id || 'N/A' }}</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-muted-foreground">Cấp bậc</label>
                                <p class="text-lg">{{ department.level }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-muted-foreground">Mô tả</label>
                            <p class="text-lg">{{ department.description || 'Không có mô tả' }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-muted-foreground">Ngày tạo</label>
                            <p class="text-lg">{{ formatDate(department.created_at) || 'Không có' }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-muted-foreground">Ngày cập nhật</label>
                            <p class="text-lg">{{ formatDate(department.updated_at) || 'Không có' }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-muted-foreground">Ngày xóa</label>
                            <p class="text-lg">{{ formatDate(department.deleted_at) || 'Không có' }}</p>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <Link :href="`/departments/${department.id}/edit`">
                                <Button variant="outline">
                                    <Pencil class="mr-2 h-4 w-4" />
                                    Chỉnh sửa
                                </Button>
                            </Link>
                            <Button variant="destructive" @click="deleteDepartment">
                                <Trash2 class="mr-2 h-4 w-4" />
                                Xóa
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>