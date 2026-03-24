<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types';

const form = useForm({
    name: '',
    code: '',
    days_allowed: 0,
    is_paid: true,
    is_active: true,
    description: '',
});

const submitForm = () => {
    form.post('/leaves/types');
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Loại nghỉ phép', href: '/leaves/types' },
    { title: 'Tạo mới', href: '#' },
];

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const showFlash = ref(false);

watch(flashSuccess, (newMessage) => {
    if (newMessage) {
        showFlash.value = true;
        setTimeout(() => {
            showFlash.value = false;
        }, 3000);
    }
}, { immediate: true });

</script>

<template>
    <Head title="Tạo mới Loại nghỉ phép" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Tạo mới Loại nghỉ phép</h1>
                    <p class="text-muted-foreground">Thêm loại nghỉ phép mới vào hệ thống.</p>
                </div>
            </div>

            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-300"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div 
                    v-if="flashSuccess && showFlash" 
                    class="flex items-center rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-200 text-emerald-800 shadow-sm"
                >
                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ flashSuccess }}</span>
                </div>
            </Transition>

            <Card>
                <CardHeader>
                    <CardTitle>Thông tin Loại nghỉ phép</CardTitle>
                    <CardDescription>Nhập đầy đủ các thông tin bắt buộc có dấu (*).</CardDescription>
                </CardHeader>
                
                <CardContent>
                    <form @submit.prevent="submitForm" class="space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Tên Loại <span class="text-destructive">*</span></label>
                                <input v-model="form.name" type="text" class="w-full px-3 py-2 border rounded-md" required placeholder="VD: Nghỉ phép năm" />
                                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Mã Loại <span class="text-destructive">*</span></label>
                                <input v-model="form.code" type="text" class="w-full px-3 py-2 border rounded-md" required placeholder="VD: NP_NAM" />
                                <div v-if="form.errors.code" class="text-red-500 text-sm mt-1">{{ form.errors.code }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Số ngày cho phép <span class="text-destructive">*</span></label>
                                <input v-model="form.days_allowed" type="number" min="0" class="w-full px-3 py-2 border rounded-md" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Loại nghỉ phép</label>
                                <select v-model="form.is_paid" class="w-full px-3 py-2 border rounded-md">
                                    <option :value="true">Có lương</option>
                                    <option :value="false">Không lương</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Trạng thái</label>
                                <select v-model="form.is_active" class="w-full px-3 py-2 border rounded-md">
                                    <option :value="true">Hoạt động</option>
                                    <option :value="false">Không hoạt động</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 border-b pb-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Mô tả thêm</label>
                                <textarea v-model="form.description" class="w-full px-3 py-2 border rounded-md" rows="3" placeholder="Ghi chú về loại nghỉ phép..."></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <Link href="/leaves/types">
                                <Button variant="outline">Hủy</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Đang lưu...' : 'Lưu Loại nghỉ phép' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

        </div>
    </AppLayout>
</template>