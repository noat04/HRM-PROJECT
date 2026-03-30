<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
// 👇 Nhớ sửa lại đường dẫn này cho đúng với thư mục chứa file Enum của bạn nhé
import PayrollStatus from '@/enums/PayrollStatus';
const form = useForm({
    name: '',
    code: '',
    start_date: '',
    end_date: '',
    payment_date: '',
    status: 'draft', // Mặc định là Nháp
});

const submit = () => form.post('/payroll-periods');

const breadcrumbs = [
    { title: 'Kỳ lương', href: '/payroll-periods' },
    { title: 'Tạo mới', href: '/payroll-periods/create' },
];
</script>

<template>
    <Head title="Tạo Kỳ lương mới" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            <h1 class="text-2xl font-bold tracking-tight">Tạo Kỳ lương mới</h1>
            <Card>
                <CardHeader>
                    <CardTitle>Thông tin Kỳ lương</CardTitle>
                    <CardDescription>Kỳ lương dùng để chốt công và tính lương cho toàn bộ nhân viên.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Tên kỳ lương <span class="text-destructive">*</span></label>
                                <input v-model="form.name" type="text" placeholder="Vd: Lương tháng 10/2025" class="w-full px-3 py-2 border rounded-md" required />
                                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Mã kỳ lương <span class="text-destructive">*</span></label>
                                <input v-model="form.code" type="text" placeholder="Vd: PR_2025_10" class="w-full px-3 py-2 border rounded-md" required />
                                <div v-if="form.errors.code" class="text-red-500 text-sm mt-1">{{ form.errors.code }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Từ ngày <span class="text-destructive">*</span></label>
                                <input v-model="form.start_date" type="date" class="w-full px-3 py-2 border rounded-md" required />
                                <div v-if="form.errors.start_date" class="text-red-500 text-sm mt-1">{{ form.errors.start_date }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Đến ngày <span class="text-destructive">*</span></label>
                                <input v-model="form.end_date" type="date" class="w-full px-3 py-2 border rounded-md" required />
                                <div v-if="form.errors.end_date" class="text-red-500 text-sm mt-1">{{ form.errors.end_date }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t pt-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Ngày dự kiến thanh toán</label>
                                <input v-model="form.payment_date" type="date" class="w-full px-3 py-2 border rounded-md" />
                                <div v-if="form.errors.payment_date" class="text-red-500 text-sm mt-1">{{ form.errors.payment_date }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Trạng thái ban đầu <span class="text-destructive">*</span></label>
                                <select v-model="form.status" class="w-full px-3 py-2 border rounded-md" required>
                                    <option :value="PayrollStatus.DRAFT">{{ PayrollStatus.labels.DRAFT }}</option>
                                    <option :value="PayrollStatus.LOCKED">{{ PayrollStatus.labels.LOCKED }}</option>
                                    <option :value="PayrollStatus.PAID">{{ PayrollStatus.labels.PAID }}</option>
                                </select>
                                <div v-if="form.errors.status" class="text-red-500 text-sm mt-1">{{ form.errors.status }}</div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-4">
                            <Link href="/payroll-periods"><Button variant="outline" type="button">Hủy</Button></Link>
                            <Button type="submit" :disabled="form.processing">Tạo Kỳ Lương</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>