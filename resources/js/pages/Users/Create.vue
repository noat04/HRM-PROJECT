<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { ArrowLeft } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Người dùng', href: '/users' },
    { title: 'Thêm mới', href: '/users/create' },
];

const form = useForm({
    name: '',
    email: '',
    password: '',
    avatar: null as File | null,
});

const submit = () => {
    form.post('/users', {
        onSuccess: () => {
            form.reset();
        },
    });
};

// Hàm xử lý khi người dùng chọn ảnh
const handleAvatarChange = (event: Event) => {
    // Ép kiểu event target thành HTMLInputElement để TypeScript nhận diện được thẻ input file
    const target = event.target as HTMLInputElement;
    if (target && target.files && target.files.length > 0) {
        form.avatar = target.files[0];
    } else {
        form.avatar = null;
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
                    <p class="text-muted-foreground">Điền thông tin chi tiết cho người dùng mới.</p>
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
                            <label class="text-sm font-medium">Ảnh đại diện</label>
                            <input 
    type="file" 
    @change="handleAvatarChange" 
    accept="image/*"
    class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
/>
                            <p v-if="form.errors.avatar" class="text-sm text-destructive">{{ form.errors.avatar }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Họ tên <span class="text-destructive">*</span></label>
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
                            <label class="text-sm font-medium">Email <span class="text-destructive">*</span></label>
                            <input 
                                v-model="form.email" 
                                type="email" 
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                placeholder="[EMAIL_ADDRESS]"
                                required 
                            />
                            <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Mật khẩu <span class="text-destructive">*</span></label>
                            <input 
                                v-model="form.password" 
                                type="password" 
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                placeholder="Nhập mật khẩu"
                                required 
                            />
                            <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
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