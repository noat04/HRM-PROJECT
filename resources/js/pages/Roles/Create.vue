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
import { computed } from 'vue';

const props = defineProps<{
    // Cập nhật type để nhận thêm group và deleted_at từ Backend
    permissions: Array<{
        id: number;
        name: string;
        group: string;
        deleted_at?: string | null;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Vai trò', href: '/roles' },
    { title: 'Tạo mới', href: '#' },
];

const form = useForm({
    name: '',
    display_name: '',
    description: '',
    permission_ids: [] as number[],
});

// 🔥 THUẬT TOÁN GOM NHÓM QUYỀN HẠN (Giống trang Edit)
const groupedPermissions = computed(() => {
    const groups: Record<string, typeof props.permissions> = {};
    
    (props.permissions || []).forEach(perm => {
        const groupName = perm.group || 'Khác'; 
        if (!groups[groupName]) {
            groups[groupName] = [];
        }
        groups[groupName].push(perm);
    });
    
    return groups;
});

const handleCheckboxChange = (checked: boolean, id: number) => {
    const numId = Number(id);

    form.permission_ids = checked
        ? [...new Set([...form.permission_ids, numId])]
        : form.permission_ids.filter(pId => pId !== numId);
};

const submitForm = () => {
    form.transform((data) => ({
        ...data,
        permission_ids: Array.from(data.permission_ids)
    })).post('/roles');
};
</script>

<template>
    <Head title="Tạo mới Vai trò" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            <div class="flex items-center gap-4">
                <Link href="/roles">
                    <Button variant="outline" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Tạo mới Vai trò</h1>
                    <p class="text-muted-foreground">Thiết lập thông tin và phân quyền cho vai trò mới.</p>
                </div>
            </div>

            <form @submit.prevent="submitForm" class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Thông tin cơ bản</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label for="name">Tên vai trò (slug)</Label>
                                <Input id="name" v-model="form.name" required class="mt-1" placeholder="VD: hr_manager" />
                                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                            </div>
                            <div>
                                <Label for="display_name">Hiển thị (Display Name)</Label>
                                <Input id="display_name" v-model="form.display_name" required class="mt-1" placeholder="VD: Trưởng phòng NS" />
                                <div v-if="form.errors.display_name" class="text-red-500 text-sm mt-1">{{ form.errors.display_name }}</div>
                            </div>
                        </div>
                        <div>
                            <Label for="description">Mô tả</Label>
                            <Textarea id="description" v-model="form.description" class="mt-1" rows="3" placeholder="Nhập mô tả về chức năng của vai trò này..." />
                            <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Danh sách quyền hạn</CardTitle>
                        <CardDescription>Chọn các quyền cho vai trò mới. Các quyền bị khóa là do đã bị xóa trong hệ thống.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-8">
                        
                        <div v-for="(perms, groupName) in groupedPermissions" :key="groupName">
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground border-b pb-2 mb-4">
                                Phân hệ: {{ groupName }}
                            </h3>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 pl-2">
                                <div 
                                    v-for="perm in perms" 
                                    :key="perm.id" 
                                    class="flex items-start space-x-2"
                                    :class="{ 'opacity-60': perm.deleted_at }" 
                                >
                                    <Checkbox
                                        :id="`perm-${perm.id}`"
                                        :modelValue="form.permission_ids.includes(Number(perm.id))"
                                        @update:modelValue="(val: boolean | 'indeterminate') => handleCheckboxChange(val === true, perm.id)"
                                        :disabled="!!perm.deleted_at"
                                        class="mt-1"
                                    />
                                    <Label 
                                        :for="`perm-${perm.id}`" 
                                        class="text-sm font-medium leading-none cursor-pointer"
                                        :class="{ 'cursor-not-allowed': perm.deleted_at }"
                                    >
                                        {{ perm.name }}
                                        <div v-if="perm.deleted_at" class="text-xs text-red-500 italic mt-1">
                                            (Đã khóa)
                                        </div>
                                    </Label>
                                </div>
                            </div>
                        </div>

                    </CardContent>
                </Card>

                <div class="flex justify-end gap-2">
                    <Link href="/roles">
                        <Button variant="outline" type="button">Hủy</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="h-4 w-4 mr-2" />
                        Tạo Vai trò
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>