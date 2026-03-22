<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { ArrowLeft, Save } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    role: {
        id: number;
        name: string;
        display_name: string;
        description: string;
        permissions: number[]; // Mảng ID các quyền hiện tại
    };
    permissions: Array<{ id: number; name: string }>;
}>();

const form = useForm({
    name: props.role.name,
    display_name: props.role.display_name,
    description: props.role.description,
    // Đảm bảo luôn là mảng (Array)
    permission_ids: props.role.permissions || [], 
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Vai trò', href: '/roles' },
    { title: props.role.name, href: `/roles/${props.role.id}` },
    { title: 'Chỉnh sửa', href: `/roles/${props.role.id}/edit` },
];

const handleCheckboxChange = (checked: boolean, id: number) => {
    const numId = Number(id);

    form.permission_ids = checked
        ? [...new Set([...form.permission_ids, numId])]
        : form.permission_ids.filter(pId => pId !== numId);
};
const submit = () => {
    form.transform((data) => ({
        ...data,
        // Ép dữ liệu permission_ids thành mảng thuần túy (loại bỏ proxy của Vue)
        permission_ids: Array.from(data.permission_ids)
    })).put(`/roles/${props.role.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Chỉnh sửa vai trò: ${role.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            <div class="flex items-center gap-4">
                <Link href="/roles">
                    <Button variant="outline" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Chỉnh sửa vai trò</h1>
                    <p class="text-muted-foreground">Cập nhật thông tin vai trò và quyền hạn.</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Thông tin cơ bản</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label for="name">Tên vai trò (slug)</Label>
                                <Input id="name" v-model="form.name" required class="mt-1" />
                            </div>
                            <div>
                                <Label for="display_name">Hiển thị</Label>
                                <Input id="display_name" v-model="form.display_name" required class="mt-1" />
                            </div>
                        </div>
                        <div>
                            <Label for="description">Mô tả</Label>
                            <Textarea id="description" v-model="form.description" class="mt-1" rows="3" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Danh sách quyền hạn</CardTitle>
                        <CardDescription>Chọn các quyền cho vai trò này.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            <div v-for="perm in permissions" :key="perm.id" class="flex items-center space-x-2">
                                <Checkbox
                                    :id="`perm-${perm.id}`"
                                    :modelValue="form.permission_ids.includes(Number(perm.id))"
                                    @update:modelValue="(val: boolean | 'indeterminate') => handleCheckboxChange(val === true, perm.id)"
                                />
                                <Label :for="`perm-${perm.id}`" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    {{ perm.name }}
                                </Label>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div class="p-4 mt-4 bg-gray-900 text-green-400 rounded text-sm font-mono">
                    <p>Dữ liệu chuẩn bị gửi lên Server:</p>
                    {{ form.permission_ids }}
                </div>

                <div class="flex justify-end gap-2">
                    <Link href="/roles">
                        <Button variant="outline">Hủy</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="h-4 w-4 mr-2" />
                        Lưu thay đổi
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>