<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { XCircle, AlertCircle } from 'lucide-vue-next'; // Import icon báo lỗi

const props = defineProps<{ payrollPeriod: any }>();

const formatDateForInput = (dateString: string | null) => {
    if (!dateString) return null;
    return dateString.split('T')[0].split(' ')[0];
};

const form = useForm({
    name: props.payrollPeriod.name,
    code: props.payrollPeriod.code,
    start_date: formatDateForInput(props.payrollPeriod.start_date),
    end_date: formatDateForInput(props.payrollPeriod.end_date),
    payment_date: formatDateForInput(props.payrollPeriod.payment_date),
    status: props.payrollPeriod.status,
});

// Bắt lỗi nghiệp vụ đóng băng từ Backend truyền sang
const page = usePage();
const flashError = computed(() => (page.props as any).flash?.error);
const showFlashError = ref(false);

watch(flashError, (msg) => {
    if (msg) { showFlashError.value = true; setTimeout(() => showFlashError.value = false, 5000); }
}, { immediate: true });

const submit = () => {
    form.put(`/payroll-periods/${props.payrollPeriod.id}`, {
        preserveScroll: true,
        onError: () => {
            showFlashError.value = true;
        }
    });
};

const breadcrumbs = [
    { title: 'Kỳ lương', href: '/payroll-periods' },
    { title: 'Chỉnh sửa', href: '#' },
];
</script>

<template>
    <Head title="Chỉnh sửa Kỳ lương" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            <h1 class="text-2xl font-bold tracking-tight">Chỉnh sửa Kỳ lương</h1>

            <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-300" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                <div v-if="flashError && showFlashError" class="flex items-start gap-2 rounded-lg bg-rose-50 px-4 py-3 border border-rose-200 text-rose-800 shadow-sm">
                    <XCircle class="h-5 w-5 text-rose-600 shrink-0 mt-0.5" />
                    <div><strong class="font-bold">Lỗi hệ thống: </strong><span class="font-medium">{{ flashError }}</span></div>
                </div>
            </Transition>

            <Card>
                <CardHeader>
                    <CardTitle>Cập nhật thông tin</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Tên kỳ lương <span class="text-destructive">*</span></label>
                                <input v-model="form.name" type="text" class="w-full px-3 py-2 border rounded-md" required />
                                <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Mã kỳ lương <span class="text-destructive">*</span></label>
                                <input v-model="form.code" type="text" class="w-full px-3 py-2 border rounded-md" required />
                                <div v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Từ ngày <span class="text-destructive">*</span></label>
                                <input v-model="form.start_date" type="date" class="w-full px-3 py-2 border rounded-md" required />
                                <div v-if="form.errors.start_date" class="text-red-500 text-xs mt-1">{{ form.errors.start_date }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Đến ngày <span class="text-destructive">*</span></label>
                                <input v-model="form.end_date" type="date" class="w-full px-3 py-2 border rounded-md" required />
                                <div v-if="form.errors.end_date" class="text-red-500 text-xs mt-1">{{ form.errors.end_date }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t pt-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Ngày dự kiến thanh toán</label>
                                <input v-model="form.payment_date" type="date" class="w-full px-3 py-2 border rounded-md" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Trạng thái <span class="text-destructive">*</span></label>
                                <select v-model="form.status" class="w-full px-3 py-2 border rounded-md bg-white" required>
                                    <option value="draft">Nháp (Draft)</option>
                                    <option value="locked">Đã chốt (Locked)</option>
                                    <option value="paid">Đã thanh toán (Paid)</option>
                                </select>
                                <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">{{ form.errors.status }}</div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-4">
                            <Link href="/payroll-periods"><Button variant="outline" type="button">Hủy</Button></Link>
                            <Button type="submit" :disabled="form.processing">Lưu thay đổi</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>