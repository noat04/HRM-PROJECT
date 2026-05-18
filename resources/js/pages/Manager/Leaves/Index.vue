<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Check, X, CalendarDays, AlertCircle, XCircle } from 'lucide-vue-next'; // Bổ sung icon báo lỗi
import { Filter } from '@/components/ui/filter';

interface LeaveRequest {
    id: number;
    start_date: string;
    end_date: string;
    reason: string;
    status: 'pending' | 'approved' | 'rejected';
    employee: { full_name: string; employee_code: string; };
    leaveType?: { name: string; };
}

interface PaginatedLeaves {
    data: LeaveRequest[];
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    leaveRequests: PaginatedLeaves;
    filters: any;
}>();

const breadcrumbs = [
    { title: 'Nghỉ phép (Team)', href: '/manager/leaves' },
    { title: 'Phê duyệt đơn phép', href: '/manager/leaves' },
];

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const flashError = computed(() => (page.props as any).flash?.error); // 💡 Bắt thêm biến lỗi từ Backend

const showFlashSuccess = ref(false);
const showFlashError = ref(false);

watch(flashSuccess, (newMessage) => {
    if (newMessage) { showFlashSuccess.value = true; setTimeout(() => { showFlashSuccess.value = false; }, 3000); }
}, { immediate: true });

watch(flashError, (newErrorMessage) => {
    if (newErrorMessage) { showFlashError.value = true; setTimeout(() => { showFlashError.value = false; }, 5000); }
}, { immediate: true });

// ==========================================
// LOGIC PHÊ DUYỆT / TỪ CHỐI (ĐÃ CHUẨN HOÁ GIÁ TRỊ)
// ==========================================
const reviewLeave = (id: number, status: 'approved' | 'rejected') => {
    let rejectionReason = null;

    if (status === 'rejected') {
        const reason = prompt('Vui lòng nhập lý do từ chối đơn nghỉ phép này:');
        if (reason === null) return; 
        if (!reason.trim()) {
            alert('Lý do từ chối không được để trống!');
            return;
        }
        rejectionReason = reason;
    } else {
        if (!confirm('Bạn có chắc chắn muốn DUYỆT đơn xin nghỉ phép này không?')) return;
    }

    router.put(`/manager/leaves/${id}/review`, { 
        status: status,
        rejection_reason: rejectionReason
    }, {
        preserveScroll: true,
        onError: () => {
            showFlashError.value = true;
        }
    });
};

// ==========================================
// HELPER TRẠNG THÁI HIỂN THỊ
// ==========================================
const getStatusData = (status: string) => {
    switch (status) {
        case 'pending': return { label: 'Chờ duyệt', class: 'bg-amber-100 text-amber-700 border-amber-200' };
        case 'approved': return { label: 'Đã duyệt', class: 'bg-emerald-100 text-emerald-700 border-emerald-200' };
        case 'rejected': return { label: 'Từ chối', class: 'bg-rose-100 text-rose-700 border-rose-200' };
        default: return { label: 'Không rõ', class: 'bg-gray-100 text-gray-700 border-gray-200' };
    }
};

const formatDate = (dateStr: string) => new Intl.DateTimeFormat('vi-VN').format(new Date(dateStr));

// ==========================================
// BỘ LỌC (FILTER)
// ==========================================
const filters = ref({
    status: props.filters?.status ? props.filters.status.split(',') : []
});

watch(filters, (newFilters) => {
    router.get('/manager/leaves', { status: newFilters.status.join(',') }, { preserveState: true, preserveScroll: true, replace: true });
}, { deep: true });

const filterConfig = [
    { 
        key: 'status', type: 'multi-checkbox' as const, placeholder: 'Trạng thái', 
        options: [
            { label: 'Chờ duyệt', value: 'pending' }, 
            { label: 'Đã duyệt', value: 'approved' }, 
            { label: 'Từ chối', value: 'rejected' }
        ] 
    }
];
</script>

<template>
    <Head title="Phê duyệt đơn nghỉ phép" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-7xl mx-auto w-full">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-100 text-purple-600 rounded-lg"><CalendarDays class="h-6 w-6" /></div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Phê duyệt đơn nghỉ phép</h1>
                    <p class="text-muted-foreground">Xử lý các yêu cầu nghỉ phép từ nhân viên trong nhóm.</p>
                </div>
            </div>

            <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-300" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                <div v-if="flashSuccess && showFlashSuccess" class="flex items-center rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-200 text-emerald-800 shadow-sm">
                    <Check class="h-5 w-5 mr-2 text-emerald-600" /><span>{{ flashSuccess }}</span>
                </div>
            </Transition>

            <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-300" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                <div v-if="flashError && showFlashError" class="flex items-start gap-2 rounded-lg bg-rose-50 px-4 py-3 border border-rose-200 text-rose-800 shadow-sm">
                    <XCircle class="h-5 w-5 text-rose-600 shrink-0 mt-0.5" />
                    <div>
                        <strong class="font-bold">Lỗi xử lý: </strong><span>{{ flashError }}</span>
                    </div>
                </div>
            </Transition>

            <Card>
                <CardHeader class="px-6 py-4 border-b bg-gray-50/50">
                    <CardTitle>Danh sách Đơn từ</CardTitle>
                    <CardDescription>Hiển thị tổng cộng {{ leaveRequests.total }} đơn nghỉ phép.</CardDescription>
                    <div class="mt-4"><Filter :config="filterConfig" v-model="filters" /></div>
                </CardHeader>
                
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-medium">Nhân viên</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Loại phép</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Thời gian</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Lý do</th>
                                    <th scope="col" class="px-6 py-3 text-center">Trạng thái</th>
                                    <th scope="col" class="px-6 py-3 text-right font-medium">Xét duyệt</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="req in leaveRequests.data" :key="req.id" class="bg-white hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ req.employee?.full_name }}</div>
                                        <div class="text-xs text-primary font-mono">{{ req.employee?.employee_code }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-muted-foreground">{{ req.leaveType?.name || 'Nghỉ phép năm' }}</td>
                                    <td class="px-6 py-4 text-muted-foreground">
                                        <div>Từ: <span class="font-medium text-gray-700">{{ formatDate(req.start_date) }}</span></div>
                                        <div>Đến: <span class="font-medium text-gray-700">{{ formatDate(req.end_date) }}</span></div>
                                    </td>
                                    <td class="px-6 py-4 text-muted-foreground max-w-xs truncate" :title="req.reason">
                                        {{ req.reason }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-xs border" :class="getStatusData(req.status).class">
                                            {{ getStatusData(req.status).label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div v-if="req.status === 'pending'" class="flex items-center justify-end gap-2">
                                            <Button @click="reviewLeave(req.id, 'approved')" size="sm" class="h-8 gap-1 bg-emerald-600 hover:bg-emerald-700">
                                                <Check class="h-3.5 w-3.5" /> Duyệt
                                            </Button>
                                            
                                            <Button @click="reviewLeave(req.id, 'rejected')" variant="destructive" size="sm" class="h-8 gap-1">
                                                <X class="h-3.5 w-3.5" /> Từ chối
                                            </Button>
                                        </div>
                                        <div v-else class="text-xs text-muted-foreground italic">
                                            Đã xử lý
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="leaveRequests.data.length === 0">
                                    <td colspan="6" class="h-32 text-center text-muted-foreground">Không có đơn nghỉ phép nào.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
                
                <CardFooter class="border-t px-6 py-4">
                    <div class="flex items-center space-x-1 justify-end w-full">
                        <template v-for="(link, index) in leaveRequests.links" :key="index">
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