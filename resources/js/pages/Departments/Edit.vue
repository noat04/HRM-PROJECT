<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { ArrowLeft } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

// Nhận dữ liệu phòng ban từ Controller truyền sang
const props = defineProps<{
    department: {
        id: number;
        name: string;
        parent_id: number | null;
        manager_id: number | null;
        description: string;
        level: number;
    }
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Phòng ban', href: '/departments' },
    { title: 'Cập nhật', href: '#' },
];

// Khởi tạo Form và ĐIỀN SẴN dữ liệu cũ vào
const form = useForm({
    name: props.department.name,
    parent_id: props.department.parent_id,
    manager_id: props.department.manager_id,
    description: props.department.description || '',
    level: props.department.level,
});

// Gửi request PUT để cập nhật
const submitForm = () => {
    form.put(`/departments/${props.department.id}`);
};
</script>

<template>
    <Head :title="`Sửa - ${department.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-3xl mx-auto w-full">
            <div class="flex items-center gap-4">
                <Link href="/departments">
                    <Button variant="outline" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Cập nhật Phòng ban</h1>
                    <p class="text-muted-foreground">Chỉnh sửa thông tin cho {{ department.name }}.</p>
                </div>
            </div>

            <Card>
                <CardContent class="pt-6">
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Tên Phòng ban</label>
                            <input 
                                v-model="form.name" 
                                type="text" 
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                required 
                            />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">ID Quản lý</label>
                                <input v-model="form.manager_id" type="number" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Cấp bậc</label>
                                <input v-model="form.level" type="number" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm" required />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Mô tả</label>
                            <textarea v-model="form.description" rows="4" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <Link href="/departments">
                                <Button type="button" variant="outline">Hủy</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />
                                Lưu thay đổi
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>