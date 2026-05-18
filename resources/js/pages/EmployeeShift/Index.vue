<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { CalendarDays, Search, UserPlus, Trash2, CheckCircle2, XCircle } from 'lucide-vue-next';

interface Assignment {
    id: number;
    start_date: string;
    end_date: string | null;
    employee: { full_name: string; employee_code: string };
    shift: { name: string; start_time: string; end_time: string };
}

const props = defineProps<{
    assignments: { data: Assignment[]; total: number; links: any[] };
    employees: { id: number; full_name: string; employee_code: string }[];
    shifts: { id: number; name: string; start_time: string; end_time: string }[];
    filters: { search: string };
}>();

const breadcrumbs = [
    { title: 'Quản lý Ca làm việc', href: '#' },
    { title: 'Phân ca nhân sự', href: '/employee-shifts' },
];

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const flashError = computed(() => (page.props as any).flash?.error);

// Form State
const form = ref({
    employee_id: '',
    shift_id: '',
    start_date: new Date().toISOString().split('T')[0],
    end_date: '',
});

const processing = ref(false);
const search = ref(props.filters?.search || '');

// Submit Phân ca
const submitForm = () => {
    processing.value = true;
    router.post('/employee-shifts', form.value, {
        preserveScroll: true,
        onSuccess: () => {
            form.value.employee_id = '';
            form.value.shift_id = '';
            form.value.end_date = '';
        },
        onFinish: () => processing.value = false,
    });
};

// Hủy lệnh phân ca
const deleteAssignment = (id: number) => {
    if (confirm('Bạn có chắc muốn xóa lịch sử phân ca này không? Việc này có thể ảnh hưởng đến kết quả tính công!')) {
        router.delete(`/employee-shifts/${id}`, { preserveScroll: true });
    }
};

// Tìm kiếm nhanh trên bảng
const handleSearch = () => {
    router.get('/employee-shifts', { search: search.value }, { preserveState: true, replace: true });
};

const formatDate = (dateStr: string | null) => {
    if (!dateStr) return 'Vô thời hạn';
    return new Date(dateStr).toLocaleDateString('vi-VN');
};
</script>

<template>
    <Head title="Phân ca làm việc" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-7xl mx-auto">
            
            <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg"><CalendarDays class="h-6 w-6" /></div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Điều phối & Phân ca nhân sự</h1>
                    <p class="text-muted-foreground"> HR thiết lập lịch trình làm việc và đổi ca cho cán bộ nhân viên toàn công ty.</p>
                </div>
            </div>

            <div v-if="flashSuccess" class="p-4 rounded-xl text-sm bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-2">
                <CheckCircle2 class="h-5 w-5 text-emerald-600 shrink-0" /><span>{{ flashSuccess }}</span>
            </div>
            <div v-if="flashError" class="p-4 rounded-xl text-sm bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-2">
                <XCircle class="h-5 w-5 text-rose-600 shrink-0" /><span>{{ flashError }}</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <Card class="shadow-md">
                    <CardHeader><CardTitle class="text-lg flex items-center gap-2"><UserPlus class="h-5 w-5 text-primary" /> Lập lệnh phân ca</CardTitle></CardHeader>
                    <CardContent>
                        <form @submit.prevent="submitForm" class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold">1. Chọn nhân viên</label>
                                <select v-model="form.employee_id" class="w-full rounded-md border p-2 text-sm bg-white" required>
                                    <option value="">-- Chọn nhân sự --</option>
                                    <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                        [{{ emp.employee_code }}] - {{ emp.full_name }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold">2. Ca làm việc áp dụng</label>
                                <select v-model="form.shift_id" class="w-full rounded-md border p-2 text-sm bg-white" required>
                                    <option value="">-- Chọn ca làm việc --</option>
                                    <option v-for="s in shifts" :key="s.id" :value="s.id">
                                        {{ s.name }} ({{ s.start_time }} - {{ s.end_time }})
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold">3. Ngày bắt đầu ca mới</label>
                                <Input v-model="form.start_date" type="date" required />
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold">4. Ngày kết thúc (Nếu có)</label>
                                <Input v-model="form.end_date" type="date" placeholder="Để trống nếu làm vô thời hạn" />
                                <p class="text-[11px] text-muted-foreground italic">Nếu để trống, hệ thống coi đây là ca làm việc dài hạn cho tới khi có lệnh đổi ca mới.</p>
                            </div>

                            <Button type="submit" class="w-full font-bold mt-2" :disabled="processing">Xác nhận gán ca</Button>
                        </form>
                    </CardContent>
                </Card>

                <Card class="lg:col-span-2 shadow-md">
                    <CardHeader class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 py-4 border-b">
                        <div>
                            <CardTitle class="text-lg">Nhật ký điều phối ca</CardTitle>
                            <CardDescription>Tổng số bản ghi: {{ assignments.total }}</CardDescription>
                        </div>
                        <div class="relative w-full sm:w-64 flex gap-1">
                            <Input v-model="search" @keyup.enter="handleSearch" placeholder="Tìm tên, mã nhân viên..." class="pl-3" />
                            <Button size="sm" @click="handleSearch"><Search class="h-4 w-4" /></Button>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-700 bg-gray-50 border-b uppercase">
                                    <tr>
                                        <th class="px-6 py-3 font-medium">Nhân viên</th>
                                        <th class="px-6 py-3 font-medium">Ca làm việc</th>
                                        <th class="px-6 py-3 font-medium text-center">Từ ngày</th>
                                        <th class="px-6 py-3 font-medium text-center">Đến ngày</th>
                                        <th class="px-6 py-3 text-right font-medium">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="item in assignments.data" :key="item.id" class="hover:bg-gray-50/50">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900">{{ item.employee?.full_name }}</div>
                                            <div class="text-xs font-mono text-primary">{{ item.employee?.employee_code }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-semibold">{{ item.shift?.name }}</div>
                                            <div class="text-xs text-muted-foreground">{{ item.shift?.start_time }} - {{ item.shift?.end_time }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center text-gray-600">{{ formatDate(item.start_date) }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span :class="!item.end_date ? 'text-emerald-600 font-semibold italic' : 'text-gray-600'">
                                                {{ formatDate(item.end_date) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <Button variant="ghost" size="sm" @click="deleteAssignment(item.id)" class="text-rose-600 hover:text-rose-700 hover:bg-rose-50 p-2 rounded-lg">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </td>
                                    </tr>
                                    <tr v-if="assignments.data.length === 0">
                                        <td colspan="5" class="py-10 text-center text-muted-foreground italic">Không tìm thấy lịch sử phân ca nào.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>