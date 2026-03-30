<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{ employees: any[]; components: any[] }>();

const form = useForm({
    employee_id: '',
    component_id: '',
    amount: 0,
    effective_date: '',
});

const submit = () => form.post('/salary-structures');

const breadcrumbs = [
    { title: 'Cơ cấu lương', href: '/salary-structures' },
    { title: 'Thêm mới', href: '/salary-structures/create' },
];
</script>

<template>
    <Head title="Thêm Cơ cấu lương" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            <h1 class="text-2xl font-bold tracking-tight">Thêm Cơ cấu lương</h1>
            <Card>
                <CardHeader><CardTitle>Thông tin chi tiết</CardTitle></CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Nhân viên <span class="text-destructive">*</span></label>
                                <select v-model="form.employee_id" class="w-full px-3 py-2 border rounded-md" required>
                                    <option value="">Chọn nhân viên</option>
                                    <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.full_name }}</option>
                                </select>
                                <div v-if="form.errors.employee_id" class="text-red-500 text-sm mt-1">{{ form.errors.employee_id }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Thành phần lương <span class="text-destructive">*</span></label>
                                <select v-model="form.component_id" class="w-full px-3 py-2 border rounded-md" required>
                                    <option value="">Chọn thành phần lương</option>
                                    <option v-for="comp in components" :key="comp.id" :value="comp.id">{{ comp.name }} ({{ comp.code }})</option>
                                </select>
                                <div v-if="form.errors.component_id" class="text-red-500 text-sm mt-1">{{ form.errors.component_id }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Số tiền (VNĐ) <span class="text-destructive">*</span></label>
                                <input v-model="form.amount" type="number" min="0" step="0.01" class="w-full px-3 py-2 border rounded-md" required />
                                <div v-if="form.errors.amount" class="text-red-500 text-sm mt-1">{{ form.errors.amount }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Ngày bắt đầu áp dụng <span class="text-destructive">*</span></label>
                                <input v-model="form.effective_date" type="date" class="w-full px-3 py-2 border rounded-md" required />
                                <div v-if="form.errors.effective_date" class="text-red-500 text-sm mt-1">{{ form.errors.effective_date }}</div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-4">
                            <Link href="/salary-structures"><Button variant="outline" type="button">Hủy</Button></Link>
                            <Button type="submit" :disabled="form.processing">Lưu dữ liệu</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>