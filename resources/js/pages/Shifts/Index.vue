<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription} from '@/components/ui/card';
import { Pencil, Trash2, Eye, Plus} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import ShiftStatus from '@/enums/ShiftStatus';
import ShiftArrayFromatDays from '@/enums/ShiftArrayFromatDays';

interface Shift {
    id: number;
    name: string;
    code: string;
    start_time: string;
    end_time: string;
    status: string;
    work_days: string[];
    grace_period: number;
    description: string;
    total_hours: number;
    is_overnight: boolean;
}

interface PaginatedShifts {
    data: Shift[];
    current_page: number;
    total: number;
    per_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    shifts: PaginatedShifts;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Trang chủ', href: '/dashboard' },
    { title: 'Ca làm việc', href: '/shifts' },
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

const deleteShift = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn xóa ca làm việc này?')) {
        router.delete(`/shifts/${id}`);
    }
};

const fortmatArrayWorkDays = (workDays: string[] | string): string => {
    // Đề phòng Laravel trả về string thay vì array
    let daysArray = Array.isArray(workDays) ? workDays : JSON.parse(workDays || '[]');
    // Dùng hàm .label() thay cho ngoặc vuông []
    return daysArray.map((day: string) => ShiftArrayFromatDays.label(day)).join(', ');
};
</script>

<template>
    <Head title="Ca làm việc" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-7xl mx-auto w-full">
            
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Quản lý Ca làm việc</h1>
                    <p class="text-muted-foreground">Danh sách các ca làm việc trong hệ thống.</p>
                </div>
                <Link href="/shifts/create">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Tạo Ca làm việc mới
                    </Button>
                </Link>
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
                    <CardTitle>Danh sách Ca làm việc</CardTitle>
                    <CardDescription>
                        Tổng số ca làm việc: {{ shifts.total }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4 font-medium">Tên Ca</th>
                                    <th class="text-left py-3 px-4 font-medium">Mã Ca</th>
                                    <th class="text-left py-3 px-4 font-medium">Mô tả</th>
                                    <th class="text-left py-3 px-4 font-medium">Giờ làm việc</th>
                                    <th class="text-left py-3 px-4 font-medium">Ngày làm việc</th>
                                    <th class="text-left py-3 px-4 font-medium">Trạng thái</th>
                                    <th class="text-right py-3 px-4 font-medium">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="shift in shifts.data" :key="shift.id" class="border-b hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-primary">{{ shift.name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ shift.code }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ shift.code }}</td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ shift.description }}</td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ shift.start_time }} - {{ shift.end_time }}</td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ fortmatArrayWorkDays(shift.work_days) }}</td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ ShiftStatus.label ? ShiftStatus.label(shift.status) : (ShiftStatus as any)[shift.status] }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="`/shifts/${shift.id}`">
                                                <Button variant="ghost" size="icon" title="Xem chi tiết">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="`/shifts/${shift.id}/edit`">
                                                <Button variant="ghost" size="icon" title="Sửa">
                                                    <Pencil class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button variant="ghost" size="icon" title="Xóa" @click="deleteShift(shift.id)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>