<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const props = defineProps<{ salaryStructure: any; employees: any[]; components: any[] }>();

const formatDateForInput = (dateString: string | null) => {
    if (!dateString) return null;
    return dateString.split('T')[0].split(' ')[0];
};

const form = useForm({
    employee_id: props.salaryStructure.employee_id,
    component_id: props.salaryStructure.component_id,
    amount: props.salaryStructure.amount,
    effective_date: formatDateForInput(props.salaryStructure.effective_date),
});

const submit = () => form.put(`/salary-structures/${props.salaryStructure.id}`);

const breadcrumbs = [
    { title: 'Cơ cấu lương', href: '/salary-structures' },
    { title: 'Chỉnh sửa', href: '#' },
];
</script>

<template>
    <Head title="Chỉnh sửa Cơ cấu lương" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            <h1 class="text-2xl font-bold tracking-tight">Chỉnh sửa Cơ cấu lương</h1>
            <Card>
                <CardHeader><CardTitle>Cập nhật thông tin</CardTitle></CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Nhân viên <span class="text-destructive">*</span></label>
                                <select v-model="form.employee_id" class="w-full px-3 py-2 border rounded-md" required>
                                    <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.full_name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Thành phần lương <span class="text-destructive">*</span></label>
                                <select v-model="form.component_id" class="w-full px-3 py-2 border rounded-md" required>
                                    <option v-for="comp in components" :key="comp.id" :value="comp.id">{{ comp.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Số tiền (VNĐ) <span class="text-destructive">*</span></label>
                                <input v-model="form.amount" type="number" min="0" step="0.01" class="w-full px-3 py-2 border rounded-md" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Ngày áp dụng <span class="text-destructive">*</span></label>
                                <input v-model="form.effective_date" type="date" class="w-full px-3 py-2 border rounded-md" required />
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-4">
                            <Link href="/salary-structures"><Button variant="outline" type="button">Hủy</Button></Link>
                            <Button type="submit" :disabled="form.processing">Lưu thay đổi</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>