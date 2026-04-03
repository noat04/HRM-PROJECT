<script setup lang="ts">
import { watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { ArrowLeft } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface Department {
    id: number;
    name: string;
    level: number;
}
interface Employee {
    id: number;
    full_name: string;
}
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Phòng ban', href: '/departments' },
    { title: 'Thêm mới', href: '/departments/create' },
];
const props = defineProps<{
    departments: Department[];
    employees: Employee[];
}>();

const form = useForm({
    name: '',
    parent_id: null as number | null,
    manager_id: null as number | null,
    description: '',
    level: 1 as number,
});

watch(() => form.parent_id, (newParentId) => {
    if (newParentId) {
        const parent = props.departments.find((d) => d.id === newParentId);
        if (parent) {
            form.level = parent.level + 1;
            return;
        }
    }
    form.level = 1;
});
const submitForm = () => {
    form.post('/departments');
};
</script>

<template>
    <Head title="Thêm Phòng ban mới" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-3xl mx-auto w-full">
            <div class="flex items-center gap-4">
                <Link href="/departments">
                    <Button variant="outline" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Thêm Phòng ban mới</h1>
                    <p class="text-muted-foreground">Điền thông tin chi tiết cho phòng ban mới.</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Thông tin phòng ban</CardTitle>
                    <CardDescription>Các trường có dấu (*) là bắt buộc.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Tên Phòng ban <span class="text-destructive">*</span></label>
                            <input 
                                v-model="form.name" 
                                type="text" 
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                placeholder="Ví dụ: Phòng Kế toán"
                                required 
                            />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>

                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Phòng ban cha (Parent ID)</label>
                                <select 
                                    v-model="form.parent_id" 
                                    class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                >
                                    <option :value="null">Không có</option>
                                    <option v-for="department in departments" :value="department.id">{{ department.name }}</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">Cấp bậc (Level) <span class="text-destructive">*</span></label>
                                <input 
                                    v-model="form.level" 
                                    type="number" 
                                    class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                    required 
                                />
                            </div>
                        
                            <div class="space-y-2">
                                <label class="text-sm font-medium">ID Quản lý (Manager)</label>
                                <select 
                                    v-model="form.manager_id" 
                                    class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                >
                                    <option :value="null">Không có</option>
                                    <option v-for="employee in employees" :value="employee.id">{{ employee.full_name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Mô tả nhiệm vụ</label>
                            <textarea 
                                v-model="form.description" 
                                rows="4" 
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                placeholder="Nhập chức năng, nhiệm vụ của phòng ban..."
                            ></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <Link href="/departments">
                                <Button type="button" variant="outline">Hủy</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />
                                Lưu phòng ban
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>