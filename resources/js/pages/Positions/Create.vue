<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { ArrowLeft } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Chức vụ', href: '/positions' },
    { title: 'Thêm mới', href: '/positions/create' },
];

const form = useForm({
    name: '',
    description: '',
    code: '',
    level: 1,
    salary_min: null as number | null,
    salary_max: null as number | null,
});

const submitForm = () => {
    form.post('/positions');
};
</script>
<template>
    <Head title="Thêm Chức vụ mới" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-3xl mx-auto w-full">
            <div class="flex items-center gap-4">
                <Link href="/positions">
                    <Button variant="outline" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Thêm Chức vụ mới</h1>
                    <p class="text-muted-foreground">Điền thông tin chi tiết cho chức vụ mới.</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Thông tin chức vụ</CardTitle>
                    <CardDescription>Các trường có dấu (*) là bắt buộc.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Tên Chức vụ <span class="text-destructive">*</span></label>
                            <input 
                                v-model="form.name" 
                                type="text" 
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                placeholder="Ví dụ: Trưởng phòng"
                                required 
                            />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Mã Chức vụ <span class="text-destructive">*</span></label>
                                <input 
                                    v-model="form.code" 
                                    type="text" 
                                    class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                    placeholder="Ví dụ: TP"
                                    required 
                                />
                                <p v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Cấp bậc (Level) <span class="text-destructive">*</span></label>
                                <input 
                                    v-model="form.level" 
                                    type="number" 
                                    class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                    required 
                                />
                                <p v-if="form.errors.level" class="text-sm text-destructive">{{ form.errors.level }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Mức lương tối thiểu</label>
                                <input 
                                    v-model="form.salary_min" 
                                    type="number" 
                                    class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                    placeholder="Ví dụ: 10000000"
                                />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Mức lương tối đa</label>
                                <input 
                                    v-model="form.salary_max" 
                                    type="number" 
                                    class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                    placeholder="Ví dụ: 15000000"
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Mô tả nhiệm vụ</label>
                            <textarea 
                                v-model="form.description" 
                                rows="4" 
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                placeholder="Nhập chức năng, nhiệm vụ của chức vụ..."
                            ></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <Link href="/positions">
                                <Button type="button" variant="outline">Hủy</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />
                                Lưu chức vụ
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
