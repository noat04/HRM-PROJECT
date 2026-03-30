<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';

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

const submit = () => form.put(`/payroll-periods/${props.payrollPeriod.id}`);

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
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Mã kỳ lương <span class="text-destructive">*</span></label>
                                <input v-model="form.code" type="text" class="w-full px-3 py-2 border rounded-md" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Từ ngày <span class="text-destructive">*</span></label>
                                <input v-model="form.start_date" type="date" class="w-full px-3 py-2 border rounded-md" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Đến ngày <span class="text-destructive">*</span></label>
                                <input v-model="form.end_date" type="date" class="w-full px-3 py-2 border rounded-md" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t pt-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Ngày dự kiến thanh toán</label>
                                <input v-model="form.payment_date" type="date" class="w-full px-3 py-2 border rounded-md" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Trạng thái <span class="text-destructive">*</span></label>
                                <select v-model="form.status" class="w-full px-3 py-2 border rounded-md" required>
                                    <option value="draft">Nháp</option>
                                    <option value="processing">Đang xử lý</option>
                                    <option value="approved">Đã duyệt</option>
                                    <option value="paid">Đã thanh toán</option>
                                </select>
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