<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { ArrowLeft } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import { ref } from 'vue';
//Nhận dữ liệu từ controller
interface User {
    id: number;
    name: string;
    email: string;
    password: string;
    avatar: string ;
    status: string;
    created_at: EpochTimeStamp;
    updated_at: EpochTimeStamp;
    deleted_at: EpochTimeStamp;
}

const props = defineProps<{
    user: User;
    processing?: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Người dùng', href: '/users' },
    { title: 'Chỉnh sửa', href: `/users/${props.user.id}/edit` },
];


// 1. Tạo biến hiển thị ảnh xem trước
// Nếu user có ảnh cũ thì nối với thư mục storage, nếu không thì dùng ảnh mặc định
const avatarPreview = ref(
    props.user.avatar 
    ? `/storage/${props.user.avatar}` 
    : `https://ui-avatars.com/api/?name=${props.user.name}&background=random`
);

// 2. Khởi tạo form
const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: props.user.password,
    avatar: null as File | null, // Ban đầu để null, chỉ chứa dữ liệu khi người dùng up ảnh MỚI
    status: props.user.status,
     created_at: props.user.created_at,
    updated_at: props.user.updated_at,
    deleted_at: props.user.deleted_at,
    
    // 👇 TUYỆT CHIÊU CỦA LARAVEL: Ép dùng PUT qua đường POST để gửi được File
    _method: 'PUT', 
});

// 3. Hàm xử lý submit (Đổi thành POST)
const submitForm = () => {
    // Phải dùng POST vì Inertia/Laravel không nhận file qua PUT trực tiếp
    form.post(`/users/${props.user.id}`);
};

// 4. Hàm xử lý chọn ảnh mới
const handleAvatarChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target && target.files && target.files.length > 0) {
        const file = target.files[0];
        form.avatar = file; // Đưa file vào form để chuẩn bị gửi đi
        
        // Tạo một đường dẫn ảo (tạm thời) trên trình duyệt để hiện ảnh ngay lập tức
        avatarPreview.value = URL.createObjectURL(file);
    } else {
        // Nếu người dùng ấn "Hủy" chọn file, trả về ảnh cũ
        form.avatar = null;
        avatarPreview.value = props.user.avatar 
            ? `/storage/${props.user.avatar}` 
            : `https://ui-avatars.com/api/?name=${props.user.name}&background=random`;
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
                            <label class="text-sm font-medium">Tên Người dùng</label>
                            <input 
                                v-model="form.name" 
                                type="text" 
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                required 
                            />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Email</label>
                            <input 
                                v-model="form.email" 
                                type="email" 
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                required 
                            />
                            <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Mật khẩu</label>
                            <input 
                                v-model="form.password" 
                                type="password" 
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                            />
                            <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
                        </div>

                       <div class="space-y-2">
                            <label class="text-sm font-medium">Ảnh đại diện</label>
    
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

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Trạng thái</label>
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
            </Card>
        </div>
    </AppLayout>
</template>