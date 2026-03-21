<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Plus, Eye, Edit, Trash2 } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    permissions: Array<{
        id: number;
        name: string;
        display_name: string;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Vai trò', href: '/roles' },
    { title: 'Tạo mới', href: '#' },
];

const form = useForm({
    name: '',
    display_name: '',
    description: '',
    permission_ids: [] as number[],
});

const submitForm = () => {
    form.post('/roles');
};
</script>
<template>
<Head title="Tạo mới Vai trò" />
<AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6 max-w-7xl mx-auto w-full">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Tạo mới Vai trò</h1>
                <p class="text-muted-foreground">Thêm vai trò mới vào hệ thống.</p>
            </div>
        </div>
    </div>

    <Card>
        <CardHeader>
            <CardTitle>Tạo mới Vai trò</CardTitle>
            <CardDescription>Nhập thông tin vai trò mới.</CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent="submitForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">Tên Vai trò (Internal)</label>
                        <input v-model="form.name" type="text" class="w-full px-3 py-2 border rounded" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Hiển thị (Display Name)</label>
                        <input v-model="form.display_name" type="text" class="w-full px-3 py-2 border rounded" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Mô tả</label>
                    <textarea v-model="form.description" class="w-full px-3 py-2 border rounded"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Quyền hạn</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <label v-for="perm in permissions" :key="perm.id" class="flex items-center gap-2">
                            <input type="checkbox" :value="perm.id" v-model="form.permission_ids" />
                            {{ perm.name }}
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <Link href="/roles">
                        <Button variant="outline">Hủy</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">Lưu</Button>
                </div>
            </form>
        </CardContent>
    </Card>
</AppLayout>
    
</template>

