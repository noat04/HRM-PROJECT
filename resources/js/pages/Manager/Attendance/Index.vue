<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Clock, Search, Calendar as CalendarIcon } from 'lucide-vue-next';

interface Employee {
    id: number;
    full_name: string;
    employee_code: string;
}

interface Attendance {
    id: number;
    date: string;
    check_in: string | null;
    check_out: string | null;
    late_minutes: number;
    early_minutes: number; // Tùy DB của bạn có trường này không, mình cứ để sẵn
    status: string;
    employee: Employee;
}

interface PaginatedAttendances {
    data: Attendance[];
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    attendances: PaginatedAttendances;
    filters: {
        start_date: string;
        end_date: string;
        search: string;
    };
}>();

const breadcrumbs = [
    { title: 'Chấm công (Team)', href: '/manager/attendance' },
    { title: 'Báo cáo chấm công', href: '/manager/attendance' },
];

// ==========================================
// BỘ LỌC (TÌM KIẾM & NGÀY THÁNG)
// ==========================================
const searchForm = ref({
    search: props.filters.search,
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
});

let filterTimeout: ReturnType<typeof setTimeout>;

// Tự động fetch data khi bộ lọc thay đổi (Debounce 500ms để tránh gọi API liên tục khi gõ)
watch(searchForm, (newValues) => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        router.get('/manager/attendance', newValues, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 500);
}, { deep: true });

// ==========================================
// FORMAT & HIỂN THỊ
// ==========================================
const formatDate = (dateStr: string) => new Intl.DateTimeFormat('vi-VN').format(new Date(dateStr));

const formatTime = (datetimeStr: string | null) => {
    if (!datetimeStr) return '-';
    // Giả sử DB lưu datetime chuẩn: "2026-05-18 08:15:00"
    const date = new Date(datetimeStr);
    return new Intl.DateTimeFormat('vi-VN', { hour: '2-digit', minute: '2-digit' }).format(date);
};

const getStatusBadge = (status: string) => {
    // Tùy theo Enum trong DB của bạn (VD: on_time, late, absent...)
    switch (status) {
        case 'on_time': return { label: 'Đúng giờ', class: 'bg-emerald-100 text-emerald-700 border-emerald-200' };
        case 'late': return { label: 'Đi muộn', class: 'bg-amber-100 text-amber-700 border-amber-200' };
        case 'absent': return { label: 'Vắng mặt', class: 'bg-rose-100 text-rose-700 border-rose-200' };
        default: return { label: status || 'Không rõ', class: 'bg-gray-100 text-gray-700 border-gray-200' };
    }
};
</script>

<template>
    <Head title="Báo cáo chấm công Đội nhóm" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-100 text-indigo-600 rounded-lg">
                    <Clock class="h-6 w-6" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Báo cáo Chấm công Nhóm</h1>
                    <p class="text-muted-foreground">Theo dõi giờ giấc đi làm của nhân viên cấp dưới trực tiếp.</p>
                </div>
            </div>

            <Card class="bg-gray-50/50">
                <CardContent class="p-4 flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-64 space-y-1.5">
                        <label class="text-sm font-medium">Tìm nhân viên</label>
                        <div class="relative">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input v-model="searchForm.search" type="text" placeholder="Tên hoặc mã NV..." class="pl-8 bg-white" />
                        </div>
                    </div>

                    <div class="w-full md:w-48 space-y-1.5">
                        <label class="text-sm font-medium">Từ ngày</label>
                        <Input v-model="searchForm.start_date" type="date" class="bg-white" />
                    </div>

                    <div class="w-full md:w-48 space-y-1.5">
                        <label class="text-sm font-medium">Đến ngày</label>
                        <Input v-model="searchForm.end_date" type="date" class="bg-white" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="px-6 py-4 border-b">
                    <CardTitle>Chi tiết chấm công</CardTitle>
                    <CardDescription>Tìm thấy tổng cộng {{ attendances.total }} bản ghi trong khoảng thời gian đã chọn.</CardDescription>
                </CardHeader>
                
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-medium">Ngày</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Nhân viên</th>
                                    <th scope="col" class="px-6 py-3 font-medium text-center">Giờ vào</th>
                                    <th scope="col" class="px-6 py-3 font-medium text-center">Giờ ra</th>
                                    <th scope="col" class="px-6 py-3 font-medium text-center">Đi muộn (Phút)</th>
                                    <th scope="col" class="px-6 py-3 font-medium text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="record in attendances.data" :key="record.id" class="bg-white hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium">{{ formatDate(record.date) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ record.employee?.full_name }}</div>
                                        <div class="text-xs text-primary font-mono">{{ record.employee?.employee_code }}</div>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center font-mono text-emerald-700">
                                        {{ formatTime(record.check_in) }}
                                    </td>
                                    <td class="px-6 py-4 text-center font-mono text-blue-700">
                                        {{ formatTime(record.check_out) }}
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        <span v-if="record.late_minutes > 0" class="font-bold text-rose-600">{{ record.late_minutes }}</span>
                                        <span v-else class="text-muted-foreground">-</span>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-xs border" :class="getStatusBadge(record.status).class">
                                            {{ getStatusBadge(record.status).label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="attendances.data.length === 0">
                                    <td colspan="6" class="h-32 text-center text-muted-foreground">
                                        Không có dữ liệu chấm công nào khớp với bộ lọc.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
                
                <CardFooter class="border-t px-6 py-4">
                    <div class="flex items-center space-x-1 justify-end w-full">
                        <template v-for="(link, index) in attendances.links" :key="index">
                            <Button v-if="link.url" @click="router.get(link.url, {}, { preserveState: true })" :variant="link.active ? 'default' : 'outline'" size="sm" class="min-w-8">
                                <span v-html="link.label"></span>
                            </Button>
                            <Button v-else variant="ghost" size="sm" disabled class="min-w-8">
                                <span v-html="link.label"></span>
                            </Button>
                        </template>
                    </div>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>