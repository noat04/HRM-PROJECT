<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Clock, LogIn, LogOut, CheckCircle2, AlertCircle, Calendar, XCircle } from 'lucide-vue-next';

interface Attendance {
    id: number;
    date: string;
    check_in: string | null;
    check_out: string | null;
    late_minutes: number;
    status: string;
}

interface PaginatedAttendances {
    data: Attendance[];
    total: number;
    links: any[];
}

const props = defineProps<{
    attendances: PaginatedAttendances;
    todayAttendance: Attendance | null;
}>();

const breadcrumbs = [
    { title: 'Chấm công cá nhân', href: '/my-attendance' },
];

// ==========================================
// ĐỒNG HỒ THỜI GIAN THỰC (LIVE CLOCK)
// ==========================================
const currentTime = ref(new Date());
let timer: any;

onMounted(() => {
    timer = setInterval(() => {
        currentTime.value = new Date();
    }, 1000);
});

onUnmounted(() => clearInterval(timer));

const displayTime = computed(() => {
    return currentTime.value.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
});

const displayDate = computed(() => {
    return currentTime.value.toLocaleDateString('vi-VN', { weekday: 'long', day: '2-digit', month: '2-digit', year: 'numeric' });
});

// ==========================================
// LẤY THÔNG BÁO TỪ BACKEND (FLASH MESSAGES)
// ==========================================
const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const flashError = computed(() => (page.props as any).flash?.error);

// ==========================================
// LOGIC XỬ LÝ NÚT BẤM (CHECK-IN/OUT)
// ==========================================
const processing = ref(false);

const handleAction = (type: 'check-in' | 'check-out') => {
    const msg = type === 'check-in' ? 'Bắt đầu ca làm việc?' : 'Kết thúc ca làm việc?';
    if (confirm(msg)) {
        processing.value = true;
        router.post(`/my-attendance/${type}`, {}, {
            preserveScroll: true,
            onFinish: () => processing.value = false,
        });
    }
};

// ==========================================
// FORMAT HIỂN THỊ
// ==========================================
const formatTime = (timeStr: string | null) => {
    if (!timeStr) return '--:--';
    return new Date(timeStr).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'on_time': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        case 'late': return 'bg-amber-100 text-amber-700 border-amber-200';
        case 'early_leave': return 'bg-rose-100 text-rose-700 border-rose-200'; // 👈 Thêm màu đỏ hồng cho Về Sớm
        default: return 'bg-gray-100 text-gray-700 border-gray-200';
    }
};
</script>

<template>
    <Head title="Chấm công cá nhân" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-5xl mx-auto p-6 space-y-6">
            
            <div v-if="flashSuccess" class="p-4 rounded-xl text-sm bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-2 animate-in fade-in duration-300">
                <CheckCircle2 class="h-5 w-5 text-emerald-600 shrink-0" />
                <span>{{ flashSuccess }}</span>
            </div>

            <div v-if="flashError" class="p-4 rounded-xl text-sm bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-2 animate-in fade-in duration-300">
                <XCircle class="h-5 w-5 text-rose-600 shrink-0" />
                <strong>Lỗi: </strong> <span>{{ flashError }}</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <Card class="lg:col-span-2 border-primary/20 shadow-lg overflow-hidden relative">
                    <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
                        <Clock :size="120" />
                    </div>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-xl font-bold flex items-center gap-2">
                            <Clock class="h-5 w-5 text-primary" /> Trạm ghi danh
                        </CardTitle>
                        <CardDescription>Vui lòng kiểm tra kỹ thời gian trước khi xác nhận.</CardDescription>
                    </CardHeader>
                    <CardContent class="flex flex-col items-center py-8">
                        <div class="text-center mb-8">
                            <div class="text-5xl font-mono font-black tracking-tighter text-gray-800 drop-shadow-sm">
                                {{ displayTime }}
                            </div>
                            <div class="text-lg font-medium text-muted-foreground mt-1 capitalize">
                                {{ displayDate }}
                            </div>
                        </div>

                        <div class="flex flex-wrap justify-center gap-4 w-full max-w-md">
                            <Button 
                                v-if="!todayAttendance"
                                @click="handleAction('check-in')"
                                :disabled="processing"
                                class="h-16 px-8 text-lg font-bold gap-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 w-full sm:w-auto shadow-lg shadow-emerald-200"
                            >
                                <LogIn /> Ghi danh vào ca
                            </Button>

                            <Button 
                                v-else-if="todayAttendance && !todayAttendance.check_out"
                                @click="handleAction('check-out')"
                                :disabled="processing"
                                class="h-16 px-8 text-lg font-bold gap-3 rounded-2xl bg-blue-600 hover:bg-blue-700 w-full sm:w-auto shadow-lg shadow-blue-200"
                            >
                                <LogOut /> Ghi danh ra ca
                            </Button>

                            <div v-else class="flex flex-col items-center p-4 bg-emerald-50 rounded-2xl border border-emerald-200 w-full">
                                <CheckCircle2 class="h-10 w-10 text-emerald-600 mb-2" />
                                <span class="font-bold text-emerald-800">Hôm nay bạn đã hoàn thành công việc!</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-gray-50/50">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">Thông tin hôm nay</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="flex justify-between items-center pb-4 border-b">
                            <span class="text-sm text-gray-500">Giờ vào:</span>
                            <span class="font-mono font-bold text-emerald-700">{{ formatTime(todayAttendance?.check_in) }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-4 border-b">
                            <span class="text-sm text-gray-500">Giờ ra:</span>
                            <span class="font-mono font-bold text-blue-700">{{ formatTime(todayAttendance?.check_out) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Trạng thái:</span>
                            <span v-if="todayAttendance" class="px-2 py-1 rounded text-xs font-bold uppercase border" :class="getStatusBadge(todayAttendance.status)">
                                {{ todayAttendance.status === 'on_time' ? 'Đúng giờ' : 'Đi muộn' }}
                            </span>
                            <span v-else class="text-xs italic text-gray-400">Chưa bắt đầu</span>
                        </div>
                        <div v-if="todayAttendance && todayAttendance.late_minutes > 0" class="p-3 bg-amber-50 border border-amber-100 rounded-lg flex gap-2 items-start">
                            <AlertCircle class="h-4 w-4 text-amber-600 shrink-0 mt-0.5" />
                            <span class="text-xs text-amber-800">Bạn đã đi muộn <strong>{{ todayAttendance.late_minutes }}</strong> phút so với quy định.</span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2"><Calendar class="h-5 w-5" /> Nhật ký làm việc tháng này</CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-y">
                                <tr>
                                    <th class="px-6 py-3 font-medium">Grid</th>
                                    <th class="px-6 py-3 font-medium text-center">Giờ vào</th>
                                    <th class="px-6 py-3 font-medium text-center">Giờ ra</th>
                                    <th class="px-6 py-3 font-medium text-center">Đi muộn (p)</th>
                                    <th class="px-6 py-3 font-medium text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="item in attendances.data" :key="item.id" class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ new Date(item.date).toLocaleDateString('vi-VN') }}</td>
                                    <td class="px-6 py-4 text-center font-mono text-emerald-700">{{ formatTime(item.check_in) }}</td>
                                    <td class="px-6 py-4 text-center font-mono text-blue-700">{{ formatTime(item.check_out) }}</td>
                                    <td class="px-6 py-4 text-center font-semibold" :class="item.late_minutes > 0 ? 'text-rose-600' : 'text-gray-400'">
                                        {{ item.late_minutes > 0 ? item.late_minutes : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase" :class="getStatusBadge(item.status)">
                                            {{ item.status === 'on_time' ? 'Đúng giờ' : 'Đi muộn' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="attendances.data.length === 0">
                                    <td colspan="5" class="px-6 py-10 text-center text-muted-foreground italic">Bạn chưa có dữ liệu chấm công trong tháng này.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>