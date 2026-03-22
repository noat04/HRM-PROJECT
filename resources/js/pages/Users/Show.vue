<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
// 👇 Đã thêm ArrowLeft bị thiếu ở code cũ
import { Pencil, Trash2, ArrowLeft } from 'lucide-vue-next'; 
import type { BreadcrumbItem } from '@/types';

// Bổ sung interface Role để nhận diện kiểu dữ liệu
interface Role {
    id: number;
    name: string;
    display_name: string;
}

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        avatar: string | null;
        status: string;
        roles: Role[]; // 👇 Khai báo thêm roles
        created_at: EpochTimeStamp;
        updated_at: EpochTimeStamp;
        deleted_at?: EpochTimeStamp | null;
    }
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Người dùng', href: '/users' },
    { title: 'Chi tiết', href: `/users/${props.user.id}` },
];

const deleteUser = () => {
    if (confirm('Bạn có chắc chắn muốn xóa người dùng này?')) {
        router.delete(`/users/${props.user.id}`);
    }
};

const avatarUrl = computed(() => {
    return props.user.avatar 
        ? `/storage/${props.user.avatar}` 
        : `https://ui-avatars.com/api/?name=${props.user.name}&background=random`; // Dùng ảnh placeholder tự động
});

const formatDate = (timestamp: EpochTimeStamp | null | undefined): string | null => {
    if (!timestamp) return null;
    const isSeconds = timestamp.toString().length <= 10;
    const date = new Date(isSeconds ? (timestamp as number) * 1000 : (timestamp as number));
    return new Intl.DateTimeFormat('vi-VN', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    }).format(date);
};
</script>

<template>
    <Head :title="`Chi tiết - ${user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            <div class="flex items-center gap-4">
                <Link href="/users">
                    <Button variant="outline" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Chi tiết Người dùng</h1>
                    <p class="text-muted-foreground">Hồ sơ và thông tin phân quyền của {{ user.name }}.</p>
                </div>
            </div>

            <Card>
                <CardContent class="pt-8">
                    <div class="flex flex-col items-center mb-8 pb-8 border-b">
                        <img 
                            :src="avatarUrl"
                            :alt="user.name" 
                            class="w-32 h-32 object-cover rounded-full border-4 border-white shadow-lg mb-4"
                        />
                        <h2 class="text-2xl font-bold">{{ user.name }}</h2>
                        <p class="text-muted-foreground">{{ user.email }}</p>
                        
                        <span 
                            class="mt-3 px-3 py-1 rounded-full text-xs font-medium"
                            :class="user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                        >
                            {{ user.status === 'active' ? 'Đang hoạt động' : 'Đã khóa' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <div class="space-y-6">
                            <h3 class="text-lg font-semibold border-b pb-2">Quyền hạn hệ thống</h3>
                            
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-muted-foreground">Vai trò (Chức danh)</label>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    <span v-if="!user.roles || user.roles.length === 0" class="text-sm text-gray-500 italic">
                                        Chưa được cấp quyền
                                    </span>
                                    <span 
                                        v-for="role in user.roles" 
                                        :key="role.id" 
                                        class="inline-flex items-center px-2.5 py-1 rounded-md text-sm font-medium bg-blue-100 text-blue-800"
                                    >
                                        {{ role.display_name || role.name }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <h3 class="text-lg font-semibold border-b pb-2">Lịch sử hệ thống</h3>
                            
                            <div class="space-y-4">
                                <div class="space-y-1">
                                    <label class="text-sm font-medium text-muted-foreground">Ngày tạo tài khoản</label>
                                    <p class="text-md">{{ formatDate(user.created_at) || 'Không có' }}</p>
                                </div>

                                <div class="space-y-1">
                                    <label class="text-sm font-medium text-muted-foreground">Cập nhật lần cuối</label>
                                    <p class="text-md">{{ formatDate(user.updated_at) || 'Không có' }}</p>
                                </div>

                                <div v-if="user.deleted_at" class="space-y-1">
                                    <label class="text-sm font-medium text-red-500">Ngày vô hiệu hóa</label>
                                    <p class="text-md text-red-600">{{ formatDate(user.deleted_at) }}</p>
                                </div>
                            </div>
                        </div>
                        
                    </div>

                    <div class="flex justify-end gap-3 pt-8 mt-8 border-t">
                        <Link :href="`/users/${user.id}/edit`">
                            <Button variant="outline" class="text-amber-600 border-amber-200 hover:bg-amber-50">
                                <Pencil class="mr-2 h-4 w-4" />
                                Chỉnh sửa
                            </Button>
                        </Link>
                        <Button variant="destructive" @click="deleteUser">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Xóa tài khoản
                        </Button>
                    </div>
                    
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>