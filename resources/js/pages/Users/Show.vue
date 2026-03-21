<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router,usePage } from '@inertiajs/vue3';
import { computed } from 'vue'; // 👇 Import computed của Vue
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { Pencil, Trash2, Eye, Plus } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        avatar: string;
        status: string;
        created_at: EpochTimeStamp;
        updated_at: EpochTimeStamp;
        deleted_at: EpochTimeStamp;
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
        : '/images/default-avatar.png';
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
        <div class="space-y-6 p-6 max-w-3xl mx-auto w-full">
            <div class="flex items-center gap-4">
                <Link href="/users">
                    <Button variant="outline" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Chi tiết Người dùng</h1>
                    <p class="text-muted-foreground">Xem thông tin chi tiết của {{ user.name }}.</p>
                </div>
            </div>

            <Card>
                <CardContent class="pt-6">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-muted-foreground">Ảnh đại diện</label>
                            <img 
                                :src="avatarUrl"
                                :alt="user.name" 
                                class="w-32 h-32 object-cover rounded-full border border-border"
                            />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-muted-foreground">Họ tên</label>
                            <p class="text-lg font-semibold">{{ user.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-muted-foreground">Email</label>
                            <p class="text-lg">{{ user.email }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-muted-foreground">Trạng thái</label>
                            <p class="text-lg">{{ user.status === 'active' ? 'Hoạt động' : 'Không hoạt động' }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-muted-foreground">Ngày tạo</label>
                            <p class="text-lg">{{ formatDate(user.created_at) || 'Không có' }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-muted-foreground">Ngày cập nhật</label>
                            <p class="text-lg">{{ formatDate(user.updated_at) || 'Không có' }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-muted-foreground">Ngày xóa</label>
                            <p class="text-lg">{{ formatDate(user.deleted_at) || 'Không có' }}</p>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <Link :href="`/users/${user.id}/edit`">
                                <Button variant="outline">
                                    <Pencil class="mr-2 h-4 w-4" />
                                    Chỉnh sửa
                                </Button>
                            </Link>
                            <Button variant="destructive" @click="deleteUser">
                                <Trash2 class="mr-2 h-4 w-4" />
                                Xóa
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
