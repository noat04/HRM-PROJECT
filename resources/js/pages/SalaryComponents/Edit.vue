<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const props = defineProps<{ salaryComponent: any }>();

const form = useForm({
    name: props.salaryComponent.name,
    code: props.salaryComponent.code,
    type: props.salaryComponent.type,
    calculation_type: props.salaryComponent.calculation_type,
    default_value: props.salaryComponent.default_value,
    // Ép kiểu boolean tránh lỗi tickbox trắng
    is_taxable: !!props.salaryComponent.is_taxable, 
    is_active: !!props.salaryComponent.is_active,
    description: props.salaryComponent.description,
});

const submit = () => form.put(`/salary-components/${props.salaryComponent.id}`);

const breadcrumbs = [
    { title: 'Thành phần lương', href: '/salary-components' },
    { title: 'Chỉnh sửa', href: '#' },
];
</script>

<template>
    <Head title="Chỉnh sửa Thành phần lương" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            <h1 class="text-2xl font-bold tracking-tight">Chỉnh sửa Thành phần lương</h1>
            <Card>
                <CardHeader><CardTitle>Cập nhật thông tin</CardTitle></CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Tên <span class="text-destructive">*</span></label>
                                <input v-model="form.name" type="text" class="w-full px-3 py-2 border rounded-md" required />
                                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Mã Code <span class="text-destructive">*</span></label>
                                <input v-model="form.code" type="text" class="w-full px-3 py-2 border rounded-md" required />
                                <div v-if="form.errors.code" class="text-red-500 text-sm mt-1">{{ form.errors.code }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Phân loại <span class="text-destructive">*</span></label>
                                <select v-model="form.type" class="w-full px-3 py-2 border rounded-md" required>
                                    <option value="earning">Thu nhập (Cộng vào lương)</option>
                                    <option value="deduction">Khấu trừ (Trừ vào lương)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Kiểu tính <span class="text-destructive">*</span></label>
                                <select v-model="form.calculation_type" class="w-full px-3 py-2 border rounded-md" required>
                                    <option value="fixed">Cố định</option>
                                    <option value="percentage">Phần trăm</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Giá trị mặc định</label>
                                <input v-model="form.default_value" type="number" step="0.01" class="w-full px-3 py-2 border rounded-md" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-slate-50 border rounded-lg">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="form.is_taxable" type="checkbox" class="w-4 h-4" />
                                <span class="font-medium">Chịu thuế TNCN</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="form.is_active" type="checkbox" class="w-4 h-4" />
                                <span class="font-medium">Đang hoạt động</span>
                            </label>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Ghi chú</label>
                            <textarea v-model="form.description" class="w-full px-3 py-2 border rounded-md" rows="3"></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-4">
                            <Link href="/salary-components"><Button variant="outline" type="button">Hủy</Button></Link>
                            <Button type="submit" :disabled="form.processing">Lưu thay đổi</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>