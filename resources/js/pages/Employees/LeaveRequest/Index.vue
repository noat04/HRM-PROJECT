<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Calendar, Palmtree, FileText, CheckCircle2, XCircle, AlertCircle } from 'lucide-vue-next';

interface Balance {
    id: number;
    leave_type: { name: string; is_paid: boolean };
    total_days: number;
    used_days: number;
    remaining_days: number;
}

interface LeaveReq {
    id: number;
    start_date: string;
    end_date: string;
    total_days: number;
    reason: string;
    status: string;
    rejection_reason: string | null;
    leave_type: { name: string };
}

defineProps<{
    balances: Balance[];
    requests: { data: LeaveReq[] };
    leaveTypes: { id: number; name: string }[];
}>();

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const flashError = computed(() => (page.props as any).flash?.error);

const form = useForm({
    leave_type_id: '',
    start_date: '',
    end_date: '',
    reason: '',
});

const submitLeave = () => {
    form.post('/leaves/requests', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const getStatusClass = (status: string) => {
    switch (status) {
        case 'approved': return 'bg-emerald-100 text-emerald-800 border-emerald-200';
        case 'rejected': return 'bg-rose-100 text-rose-800 border-rose-200';
        default: return 'bg-amber-100 text-amber-800 border-amber-200';
    }
};
</script>

<template>
    <Head title="Quản lý nghỉ phép" />
    <AppLayout :breadcrumbs="[{ title: 'Nghỉ phép của tôi', href: '#' }]">
        <div class="max-w-7xl mx-auto p-6 space-y-8">
            
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 text-blue-600 rounded-lg"><Palmtree class="h-6 w-6" /></div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Quỹ phép & Đăng ký nghỉ</h1>
                    <p class="text-muted-foreground">Theo dõi số ngày phép còn lại trong năm và tạo đơn gửi quản lý trực tiếp.</p>
                </div>
            </div>

            <div v-if="flashSuccess" class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-2">
                <CheckCircle2 class="h-5 w-5 text-emerald-600 shrink-0" /> <span>{{ flashSuccess }}</span>
            </div>
            <div v-if="flashError" class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-2">
                <XCircle class="h-5 w-5 text-rose-600 shrink-0" /> <span>{{ flashError }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Card v-for="b in balances" :key="b.id" class="border-l-4 border-l-primary shadow-sm">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground uppercase">{{ b.leave_type.name }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-black font-mono text-gray-800">{{ b.remaining_days }} <span class="text-sm font-normal text-muted-foreground">ngày còn lại</span></div>
                        <div class="flex justify-between text-xs text-gray-500 mt-3 border-t pt-2">
                            <span>Tổng cấp: <strong>{{ b.total_days }}</strong></span>
                            <span>Đã nghỉ: <strong class="text-rose-600">{{ b.used_days }}</strong></span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <Card class="shadow-md">
                    <CardHeader><CardTitle class="text-lg flex items-center gap-2"><FileText class="h-5 w-5" /> Tạo đơn xin nghỉ phép</CardTitle></CardHeader>
                    <CardContent>
                        <form @submit.prevent="submitLeave" class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold">Loại hình nghỉ</label>
                                <select v-model="form.leave_type_id" class="w-full rounded-md border p-2 text-sm bg-white" required>
                                    <option value="">-- Chọn loại phép --</option>
                                    <option v-for="t in leaveTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold">Từ ngày</label>
                                    <Input v-model="form.start_date" type="date" required />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold">Đến ngày</label>
                                    <Input v-model="form.end_date" type="date" required />
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold">Lý do xin nghỉ</label>
                                <Textarea v-model="form.reason" rows="3" placeholder="Ghi rõ lý do bàn giao công việc..." required />
                            </div>
                            <Button type="submit" class="w-full font-bold" :disabled="form.processing">Gửi đơn phê duyệt</Button>
                        </form>
                    </CardContent>
                </Card>

                <Card class="lg:col-span-2 shadow-md">
                    <CardHeader><CardTitle class="text-lg">Nhật ký nghỉ phép</CardTitle></CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-700 bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-6 py-3">Loại phép</th>
                                        <th class="px-6 py-3 text-center">Thời gian</th>
                                        <th class="px-6 py-3 text-center">Số ngày</th>
                                        <th class="px-6 py-3 text-center">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="r in requests.data" :key="r.id" class="hover:bg-gray-50/50">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900">{{ r.leave_type?.name }}</div>
                                            <div class="text-xs text-gray-500 max-w-xs truncate">{{ r.reason }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center font-mono text-xs">
                                            {{ new Date(r.start_date).toLocaleDateString('vi-VN') }} - {{ new Date(r.end_date).toLocaleDateString('vi-VN') }}
                                        </td>
                                        <td class="px-6 py-4 text-center font-bold text-primary">{{ r.total_days }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2.5 py-0.5 rounded-full text-xs border font-semibold capitalize" :class="getStatusClass(r.status)">
                                                {{ r.status === 'pending' ? 'Chờ duyệt' : (r.status === 'approved' ? 'Đã duyệt' : 'Từ chối') }}
                                            </span>
                                            <div v-if="r.rejection_reason" class="text-[10px] text-rose-600 mt-1 max-w-xs mx-auto">Lý do hủy: {{ r.rejection_reason }}</div>
                                        </td>
                                    </tr>
                                    <tr v-if="requests.data.length === 0">
                                        <td colspan="4" class="py-10 text-center text-muted-foreground italic">Bạn chưa tạo đơn xin nghỉ phép nào.</td>
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