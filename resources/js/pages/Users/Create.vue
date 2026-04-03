<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { ArrowLeft } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

// Nhận danh sách vai trò từ Controller
defineProps<{
    roles: Array<{ id: number; name: string; display_name: string; deleted_at: string | null }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Người dùng', href: '/users' },
    { title: 'Thêm mới', href: '/users/create' },
];

const form = useForm({
    name: '',
    email: '',
    password: '',
    avatar: null as File | null,
    role_ids: [] as number[],
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        role_ids: Array.from(data.role_ids)
    })).post('/users', {
        onSuccess: () => {
            form.reset();
        },
    });
};

const handleAvatarChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target && target.files && target.files.length > 0) {
        form.avatar = target.files[0];
    } else {
        form.avatar = null;
    }
};

const handleRoleChange = (checked: boolean, id: number) => {
    const numId = Number(id);
    if (checked) {
        if (!form.role_ids.includes(numId)) {
            form.role_ids = [...form.role_ids, numId];
        }
    } else {
        form.role_ids = form.role_ids.filter(rId => rId !== numId);
    }
};
</script>

<template>
    <Head title="Thêm Người dùng mới" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-3xl mx-auto w-full">
            <div class="flex items-center gap-4">
                <Link href="/users">
                    <Button variant="outline" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Thêm Người dùng mới</h1>
                    <p class="text-muted-foreground">Điền thông tin chi tiết và cấp quyền cho người dùng mới.</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Thông tin người dùng</CardTitle>
                    <CardDescription>Các trường có dấu (*) là bắt buộc.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div class="space-y-2">
                            <Label class="text-sm font-medium">Ảnh đại diện</Label>
                            <input 
                                type="file" 
                                @change="handleAvatarChange" 
                                accept="image/*"
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm cursor-pointer file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20"
                            />
                            <p v-if="form.errors.avatar" class="text-sm text-destructive">{{ form.errors.avatar }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label class="text-sm font-medium">Họ tên <span class="text-destructive">*</span></Label>
                            <input 
                                v-model="form.name" 
                                type="text" 
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                placeholder="Ví dụ: Nguyễn Văn A"
                                required 
                            />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label class="text-sm font-medium">Email <span class="text-destructive">*</span></Label>
                            <input 
                                v-model="form.email" 
                                type="email" 
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                placeholder="nguyenvana@example.com"
                                required 
                            />
                            <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label class="text-sm font-medium">Mật khẩu <span class="text-destructive">*</span></Label>
                            <input 
                                v-model="form.password" 
                                type="password" 
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                placeholder="Nhập ít nhất 8 ký tự"
                                required 
                            />
                            <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
                        </div>

                        <div class="space-y-3 pt-4 border-t mt-6">
                            <Label class="text-sm font-medium text-lg">Vai trò (Chức danh)</Label>
                            <p class="text-sm text-muted-foreground mb-4">Chọn các vai trò để cấp quyền cho người dùng này.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 border rounded-md p-4 bg-gray-50/50">
                                <div 
                                    v-for="role in roles" 
                                    :key="role.id" 
                                    class="flex items-start space-x-2"
                                    :class="{ 'opacity-60': role.deleted_at }" 
                                >
                                    <Checkbox
                                        :id="`role-${role.id}`"
                                        :modelValue="form.role_ids.includes(Number(role.id))"
                                        @update:modelValue="(val: boolean | 'indeterminate') => handleRoleChange(val === true, role.id)"
                                        :disabled="!!role.deleted_at"
                                        class="mt-1"
                                    />
                                    <Label 
                                        :for="`role-${role.id}`" 
                                        class="text-sm font-medium leading-none cursor-pointer"
                                        :class="{ 'cursor-not-allowed': role.deleted_at }"
                                    >
                                        {{ role.display_name || role.name }}
                                        <div v-if="role.deleted_at" class="text-xs text-red-500 italic mt-1">
                                            (Đã khóa)
                                        </div>
                                    </Label>
                                </div>
                                <div v-if="roles.length === 0" class="text-sm text-gray-500 italic col-span-full">
                                    Chưa có vai trò nào trong hệ thống. Hãy tạo vai trò trước.
                                </div>
                            </div>
                            <p v-if="form.errors.role_ids" class="text-sm text-destructive">{{ form.errors.role_ids }}</p>
                        </div>

                        <div class="flex justify-end gap-3 pt-6">
                            <Link href="/users">
                                <Button type="button" variant="outline">Hủy</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />
                                Lưu người dùng
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>