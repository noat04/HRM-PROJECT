<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types';

// ⚠️ LƯU Ý QUAN TRỌNG: Hãy đảm bảo 2 file này thực sự tồn tại trong thư mục resources/js/enums/
import ShiftStatus from '@/enums/ShiftStatus';
import ShiftArrayFromatDays from '@/enums/ShiftArrayFromatDays';

const form = useForm({
    name: '',
    code: '',
    start_time: '',
    end_time: '',
    status: '',
    work_days: [] as string[],
    grace_period: 0,
    description: '',
    total_hours: 0,
    is_overnight: false,
});

const submitForm = () => {
    form.post('/shifts');
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Ca làm việc', href: '/shifts' },
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
    <Head title="Tạo mới Ca làm việc" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Tạo mới Ca làm việc</h1>
                    <p class="text-muted-foreground">Thêm ca làm việc mới vào hệ thống.</p>
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
                    <CardTitle>Thông tin Ca làm việc</CardTitle>
                    <CardDescription>Nhập đầy đủ các thông tin bắt buộc có dấu (*).</CardDescription>
                </CardHeader>
                
                <CardContent>
                    <form @submit.prevent="submitForm" class="space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Tên Ca <span class="text-destructive">*</span></label>
                                <input v-model="form.name" type="text" class="w-full px-3 py-2 border rounded-md" required placeholder="VD: Ca Sáng" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Mã Ca <span class="text-destructive">*</span></label>
                                <input v-model="form.code" type="text" class="w-full px-3 py-2 border rounded-md" required placeholder="VD: CA_SANG_01" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Giờ vào <span class="text-destructive">*</span></label>
                                <input v-model="form.start_time" type="time" class="w-full px-3 py-2 border rounded-md" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Giờ ra <span class="text-destructive">*</span></label>
                                <input v-model="form.end_time" type="time" class="w-full px-3 py-2 border rounded-md" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Thời gian đi trễ cho phép (phút)</label>
                                <input v-model="form.grace_period" type="number" min="0" class="w-full px-3 py-2 border rounded-md" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 border-b pb-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Mô tả thêm</label>
                                <textarea v-model="form.description" class="w-full px-3 py-2 border rounded-md" rows="3" placeholder="Ghi chú về ca làm việc..."></textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-base font-semibold mb-3">Ngày làm việc áp dụng <span class="text-destructive">*</span></label>
                                <div class="grid grid-cols-2 gap-3 bg-slate-50 p-4 rounded-lg border">
                                    <label
                                        v-for="day in ShiftArrayFromatDays.options()"
                                        :key="day.value"
                                        class="flex items-center gap-2 cursor-pointer hover:bg-slate-100 p-1 rounded"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="day.value"
                                            v-model="form.work_days"
                                            class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary"
                                        />
                                        <span class="text-sm font-medium">{{ day.label }}</span>
                                    </label>
                                </div>
                                <p v-if="form.errors.work_days" class="text-sm text-destructive mt-1">{{ form.errors.work_days }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-base font-semibold mb-3">Trạng thái <span class="text-destructive">*</span></label>
                                <div class="flex flex-col gap-3 bg-slate-50 p-4 rounded-lg border">
                                    <label
                                        v-for="perm in ShiftStatus.options()"
                                        :key="perm.value"
                                        class="flex items-center gap-2 cursor-pointer hover:bg-slate-100 p-1 rounded"
                                    >
                                        <input
                                            type="radio"
                                            :value="perm.value"
                                            v-model="form.status"
                                            required
                                            class="w-4 h-4 border-gray-300 text-primary focus:ring-primary"
                                        />
                                        <span class="text-sm font-medium">{{ perm.label }}</span>
                                    </label>
                                </div>
                                <p v-if="form.errors.status" class="text-sm text-destructive mt-1">{{ form.errors.status }}</p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-6 border-t mt-6">
                            <Link href="/shifts">
                                <Button variant="outline" type="button">Hủy</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing">Lưu Ca làm việc</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>