<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Plus, Eye, Edit, Trash2 } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
    // Đã cấu trúc lại thành Pagination Object
    roles: {
        data: Array<{
            id: number;
            name: string;
            display_name: string;
            description: string;
            permissions: Array<{ name: string }>;
            deleted_at?: string | null; 
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        total: number;
    },
    // restore giữ nguyên vì vẫn là Array (từ ->get() ở backend)
    restore: Array<{
        id: number;
        name: string;
        display_name: string;
        description: string;
        permissions: Array<{ name: string }>;
        deleted_at?: string | null; 
    }>,
    permissions: Array<{
        id: number;
        name: string;
    }>,
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Vai trò', href: '/roles' },
    { title: 'Danh sách', href: '#' },
];

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);

const showFlash = ref(false);

watch(flashSuccess, (newMessage) => {
    if (newMessage) {
        showFlash.value = true;
        setTimeout(() => {
            showFlash.value = false;
        }, 3000);
    }
}, { immediate: true });

const deleteRole = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn xóa vai trò này không?')) {
        router.delete(`/roles/${id}`);
    }
};

const viewingRestore = ref(false);

const toggleRestoreView = () => {
    viewingRestore.value = !viewingRestore.value;
};

const restoreRole = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn khôi phục vai trò này không?')) {
        router.put(`/roles/${id}/restore`);
    }
};

const forceDeleteRole = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn xóa vĩnh viễn vai trò này không?')) {
        router.delete(`/roles/${id}/force-delete`);
    }
};
</script>

<template>
    <Head title="Quản lý Vai trò" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-7xl mx-auto w-full">
            
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Quản lý Vai trò</h1>
                    <p class="text-muted-foreground">Danh sách các vai trò và phân quyền trong hệ thống.</p>
                </div>
                 <div class="flex gap-2">
                    <Button @click="toggleRestoreView" :variant="viewingRestore ? 'default' : 'outline'">
                        <Trash2 class="h-4 w-4 mr-2" />
                        {{ viewingRestore ? 'Đóng thùng rác' : 'Thùng rác' }}
                    </Button>
                    <Link href="/roles/create">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Tạo Vai trò mới
                        </Button>
                    </Link>
                 </div>
            </div>
            
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-300"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div 
                    v-if="flashSuccess && showFlash" 
                    class="flex items-center rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-200 text-emerald-800 shadow-sm"
                >
                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ flashSuccess }}</span>
                </div>
            </Transition>

            <Card>
                <CardHeader>
                    <CardTitle>{{ viewingRestore ? 'Thùng rác vai trò' : 'Dữ liệu vai trò' }}</CardTitle>
                    <CardDescription v-if="!viewingRestore">Hiển thị tổng cộng {{ roles?.data?.length || 0 }} vai trò.</CardDescription>
                    <CardDescription v-else>Hiển thị danh sách vai trò đã xóa mềm lưu trong thùng rác.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4 font-medium">Tên Vai trò</th>
                                    <th class="text-left py-3 px-4 font-medium">Mô tả</th>
                                    <th class="text-left py-3 px-4 font-medium">Quyền hạn</th>
                                    <th class="text-right py-3 px-4 font-medium">Hành động</th>
                                </tr>
                            </thead>

                            <tbody v-if="!viewingRestore" class="divide-y">
                                <tr v-for="role in (roles.data || [])" :key="role.id" class="border-b hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-primary">{{ role.display_name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ role.name }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ role.description || 'N/A' }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex flex-wrap gap-1">
                                            <Badge v-for="perm in (role.permissions || [])" :key="perm.name" variant="secondary">
                                                {{ perm.name }}
                                            </Badge>
                                            <span v-if="!role.permissions?.length" class="text-xs text-muted-foreground">Chưa có quyền</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="`/roles/${role.id}`">
                                                <Button variant="ghost" size="icon" title="Xem chi tiết">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="`/roles/${role.id}/edit`">
                                                <Button variant="ghost" size="icon" title="Chỉnh sửa">
                                                    <Edit class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button variant="ghost" size="icon" title="Xóa" @click="deleteRole(role.id)">
                                                <Trash2 class="h-4 w-4 text-destructive" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!roles?.data?.length">
                                    <td colspan="4" class="text-center py-6 text-muted-foreground italic">Không có dữ liệu vai trò nào.</td>
                                </tr>
                            </tbody>

                            <tbody v-else class="divide-y">
                                <tr v-for="role in (restore || [])" :key="role.id" class="border-b hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-primary">{{ role.display_name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ role.name }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ role.description || 'N/A' }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex flex-wrap gap-1">
                                            <Badge v-for="perm in (role.permissions || [])" :key="perm.name" variant="secondary">
                                                {{ perm.name }}
                                            </Badge>
                                            <span v-if="!role.permissions?.length" class="text-xs text-muted-foreground">Chưa có quyền</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button 
                                                variant="outline" 
                                                class="text-emerald-600 border-emerald-600 hover:bg-emerald-50" 
                                                @click="restoreRole(role.id)"
                                            >
                                                Khôi phục
                                            </Button>
                                            <Button 
                                                variant="destructive" 
                                                @click="forceDeleteRole(role.id)"
                                            >
                                                Xóa vĩnh viễn
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!restore?.length">
                                    <td colspan="4" class="text-center py-6 text-muted-foreground italic">Thùng rác trống.</td>
                                </tr>
                            </tbody>

                        </table>
                    </div>
                </CardContent>
                 <CardFooter class="border-t bg-gray-50/50 px-6 py-4">
                    <div class="flex items-center justify-between w-full">
                        <div class="text-sm text-gray-500">
                            Hiển thị <span class="font-medium text-gray-900">{{ roles?.data?.length || 0 }}</span> trên tổng số <span class="font-medium text-gray-900">{{ roles?.total || 0 }}</span> kết quả
                        </div>
                        <div class="flex items-center space-x-1">
                            <template v-for="(link, index) in roles?.links" :key="index">
                                <Button
                                    v-if="link.url"
                                    @click="router.get(link.url, {}, { preserveState: true })"
                                    :variant="link.active ? 'default' : 'outline'"
                                    size="sm"
                                    class="min-w-8 shadow-sm"
                                >
                                    <span v-html="link.label"></span>
                                </Button>
                                <Button
                                    v-else
                                    variant="outline"
                                    size="sm"
                                    disabled
                                    class="min-w-8 opacity-50"
                                >
                                    <span v-html="link.label"></span>
                                </Button>
                            </template>
                        </div>
                    </div>
                </CardFooter>
            </Card>

        </div> 
    </AppLayout>
</template>