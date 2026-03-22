<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { Checkbox } from '@/components/ui/checkbox'; // 👇 Import Checkbox
import { Label } from '@/components/ui/label';       // 👇 Import Label
import { ArrowLeft } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import { ref } from 'vue';

// Nhận dữ liệu từ controller
interface Role {
    id: number;
    name: string;
    display_name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    avatar: string | null;
    status: string;
    role_ids: number[]; // 👇 Thêm mảng ID vai trò
    created_at: EpochTimeStamp;
    updated_at: EpochTimeStamp;
    deleted_at?: EpochTimeStamp;
}

const props = defineProps<{
    user: User;
    roles: Role[]; // 👇 Nhận danh sách vai trò từ backend
    processing?: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Người dùng', href: '/users' },
    { title: 'Chỉnh sửa', href: `/users/${props.user.id}/edit` },
];

// 1. Tạo biến hiển thị ảnh xem trước
const avatarPreview = ref(
    props.user.avatar 
    ? `/storage/${props.user.avatar}` 
    : `https://ui-avatars.com/api/?name=${props.user.name}&background=random`
);

// 2. Khởi tạo form
const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '', // 👇 Để trống, không lấy password cũ lên
    avatar: null as File | null,
    status: props.user.status,
    role_ids: props.user.role_ids || [], // 👇 Load sẵn các quyền user đang có
    
    // TUYỆT CHIÊU CỦA LARAVEL: Ép dùng PUT qua đường POST để gửi được File
    _method: 'PUT', 
});

// 3. Hàm xử lý submit
const submitForm = () => {
    // Dùng transform để ép mảng proxy thành array thuần cho role_ids
    form.transform((data) => ({
        ...data,
        role_ids: Array.from(data.role_ids)
    })).post(`/users/${props.user.id}`);
};

// 4. Hàm xử lý chọn ảnh mới
const handleAvatarChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target && target.files && target.files.length > 0) {
        const file = target.files[0];
        form.avatar = file; 
        avatarPreview.value = URL.createObjectURL(file);
    } else {
        form.avatar = null;
        avatarPreview.value = props.user.avatar 
            ? `/storage/${props.user.avatar}` 
            : `https://ui-avatars.com/api/?name=${props.user.name}&background=random`;
    }
};

// 5. Hàm xử lý checkbox quyền
const handleRoleChange = (checked: boolean, id: number) => {
    const numId = Number(id);
    if (checked) {
        if (!form.role_ids.includes(numId)) {
            form.role_ids = [...form.role_ids, numId];
        }
    } else {
        form.role_ids = form.role_ids.filter(rId => rId !== numId);
    }
};
</script>

<template>
    <Head :title="`Sửa - ${user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-3xl mx-auto w-full">
            <div class="flex items-center gap-4">
                <Link href="/users">
                    <Button variant="outline" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Cập nhật Người dùng</h1>
                    <p class="text-muted-foreground">Chỉnh sửa thông tin cho {{ user.name }}.</p>
                </div>
            </div>

            <Card>
                <CardContent class="pt-6">
                    <form @submit.prevent="submitForm" class="space-y-6">
                        
                        <div class="space-y-2">
                            <Label class="text-sm font-medium">Tên Người dùng <span class="text-destructive">*</span></Label>
                            <input 
                                v-model="form.name" 
                                type="text" 
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                required 
                            />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label class="text-sm font-medium">Email <span class="text-destructive">*</span></Label>
                            <input 
                                v-model="form.email" 
                                type="email" 
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                required 
                            />
                            <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label class="text-sm font-medium">Mật khẩu</Label>
                            <input 
                                v-model="form.password" 
                                type="password" 
                                placeholder="Để trống nếu không muốn thay đổi mật khẩu"
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                            />
                            <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label class="text-sm font-medium">Ảnh đại diện</Label>
                            <div class="flex items-center gap-6 mt-2">
                                <div class="shrink-0">
                                    <img 
                                        :src="avatarPreview" 
                                        alt="Avatar" 
                                        class="w-20 h-20 rounded-full object-cover border border-gray-200 shadow-sm"
                                    />
                                </div>
                                <div class="flex-1 space-y-2">
                                    <input 
                                        type="file" 
                                        @change="handleAvatarChange" 
                                        accept="image/*"
                                        class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm cursor-pointer file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20"
                                    />
                                    <p class="text-xs text-muted-foreground">
                                        Định dạng JPG, PNG hoặc GIF. Tối đa 2MB. Để trống nếu không muốn thay đổi.
                                    </p>
                                </div>
                            </div>
                            <p v-if="form.errors.avatar" class="text-sm text-destructive">{{ form.errors.avatar }}</p>
                        </div>

                        <div class="space-y-3 pt-4 border-t mt-6">
                            <Label class="text-sm font-medium text-lg">Vai trò (Chức danh)</Label>
                            <p class="text-sm text-muted-foreground mb-4">Sửa đổi các chức danh và quyền hạn của người dùng này.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 border rounded-md p-4 bg-gray-50/50">
                                <div v-for="role in (roles || [])" :key="role.id" class="flex items-center space-x-2">
                                    <Checkbox
    :id="`role-${role.id}`"
    :modelValue="form.role_ids.includes(Number(role.id))"
    @update:modelValue="(val: boolean | 'indeterminate') => handleRoleChange(val === true, role.id)"
/>
                                    <Label :for="`role-${role.id}`" class="text-sm font-medium leading-none cursor-pointer">
                                        {{ role.display_name || role.name }}
                                    </Label>
                                </div>
                                <div v-if="!roles || roles.length === 0" class="text-sm text-gray-500 italic col-span-full">
                                    Chưa có vai trò nào trong hệ thống.
                                </div>
                            </div>
                            <p v-if="form.errors.role_ids" class="text-sm text-destructive">{{ form.errors.role_ids }}</p>
                        </div>

                        <div class="space-y-2 pt-4 border-t">
                            <Label class="text-sm font-medium">Trạng thái</Label>
                            <select 
                                v-model="form.status" 
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                            >
                                <option value="active">Hoạt động</option>
                                <option value="inactive">Không hoạt động</option>
                            </select>
                            <p v-if="form.errors.status" class="text-sm text-destructive">{{ form.errors.status }}</p>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <Link href="/users">
                                <Button type="button" variant="outline">Hủy</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />
                                Lưu thay đổi
                            </Button>
                        </div>
                    </form>
                </CardContent>
                    <div class="bg-black text-green-400 p-2 rounded mt-2">
                        {{ form.role_ids }}
                    </div>
            </Card>
        </div>
    </AppLayout>
</template>