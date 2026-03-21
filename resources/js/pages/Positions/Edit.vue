<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { ArrowLeft } from 'lucide-vue-next';
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
    { title: 'Cập nhật', href: '#' },
];

// Khởi tạo Form với dữ liệu cũ
const form = useForm({
    name: props.position.name,
    code: props.position.code,
    level: props.position.level,
    salary_min: props.position.salary_min,
    salary_max: props.position.salary_max,
    description: props.position.description || '',
});

// Gửi request PUT để cập nhật
const submitForm = () => {
    form.put(`/positions/${props.position.id}`);
};
</script>

<template>
    <Head :title="`Sửa - ${position.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-3xl mx-auto w-full">
            <div class="flex items-center gap-4">
                <Link href="/positions">
                    <Button variant="outline" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Cập nhật Chức vụ</h1>
                    <p class="text-muted-foreground">Chỉnh sửa thông tin cho {{ position.name }}.</p>
                </div>
            </div>

            <Card>
                <CardContent class="pt-6">
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Tên Chức vụ</label>
                            <input v-model="form.name" type="text" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm" required />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Mã Chức vụ</label>
                                <input v-model="form.code" type="text" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm" required />
                                <p v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Cấp bậc</label>
                                <input v-model="form.level" type="number" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Mức lương tối thiểu</label>
                                <input v-model="form.salary_min" type="number" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Mức lương tối đa</label>
                                <input v-model="form.salary_max" type="number" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Mô tả</label>
                            <textarea v-model="form.description" rows="4" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <Link href="/positions">
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
