<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Plus, Edit, Trash2, Search, RotateCcw } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    // Khai báo chuẩn Pagination Object cho permissions
    permissions: {
        data: Array<{
            id: number;
            name: string;
            group: string;
            guard_name: string;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        total: number;
    },
    // Nhận filters từ Backend để hiển thị lại từ khóa tìm kiếm
    filters: {
        search?: string;
    },
    restore: {
        data: Array<{
            id: number;
            name: string;
            group: string;
            guard_name: string;
        }>;
    }
}>();

const breadcrumbs = [
    { title: 'Quyền hạn', href: '/permissions' },
    { title: 'Danh sách', href: '#' },
];

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);

// Logic Tìm kiếm có độ trễ (Debounce) để không gọi API liên tục
const searchQuery = ref(props.filters.search || '');
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

watch(searchQuery, (value) => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/permissions', { search: value }, {
            preserveState: true, // Giữ nguyên state hiện tại
            replace: true,       // Không tạo thêm lịch sử duyệt web dư thừa
        });
    }, 300); // Đợi người dùng gõ xong (300ms) mới gọi API
});

const deletePermission = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn xóa quyền này? Nếu xóa, các vai trò đang giữ quyền này cũng sẽ bị mất quyền.')) {
        router.delete(`/permissions/${id}`);
    }
};

const restorePermission = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn khôi phục quyền này?')) {
        router.put(`/permissions/${id}/restore`);
    }
};

const forceDeletePermission = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn xóa vĩnh viễn quyền này? Hành động này không thể hoàn tác.')) {
        router.delete(`/permissions/${id}/force-delete`);
    }
};

const viewingRestore = ref(false);

const toggleRestoreView = () => {
    viewingRestore.value = !viewingRestore.value;
};
</script>

<template>
    <Head title="Quản lý Quyền hạn" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-7xl mx-auto w-full">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Quản lý Quyền hạn</h1>
                    <p class="text-muted-foreground">Định nghĩa các quyền vi mô (VD: view_users, create_employee).</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative w-full sm:w-64">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input 
                            v-model="searchQuery" 
                            type="search" 
                            placeholder="Tìm kiếm quyền..." 
                            class="w-full pl-8"
                        />
                    </div>
                     <div class="flex gap-2">
                       <Button @click="toggleRestoreView" :variant="viewingRestore ? 'default' : 'outline'">
                                <Trash2 class="h-4 w-4" />
                                {{ viewingRestore ? 'Đóng' : 'Thùng rác' }}
                            </Button>
                        <Link href="/permissions/create">
                            <Button>
                                <Plus class="mr-2 h-4 w-4" /> Tạo Quyền
                            </Button>
                        </Link>
                     </div>
                </div>
            </div>

            <div v-if="flashSuccess" class="bg-emerald-50 text-emerald-800 p-4 rounded-md border border-emerald-200">
                {{ flashSuccess }}
            </div>

            <Card>
                <CardHeader>
                     <CardTitle>{{ viewingRestore ? 'Thùng rác quyền hạn' : 'Dữ liệu quyền hạn' }}</CardTitle>
                    <CardDescription v-if="!viewingRestore">Hiển thị tổng cộng {{ permissions.total }} quyền hạn.</CardDescription>
                    <CardDescription v-else>Hiển thị danh sách quyền hạn đã xóa mềm lưu trong thùng rác.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-3 px-4 font-medium">Tên Quyền (Mã code)</th>
                                    <th class="py-3 px-4 font-medium">Nhóm</th>
                                    <th class="py-3 px-4 font-medium text-right">Hành động</th>
                                </tr>
                            </thead>
                              <tbody v-if="!viewingRestore" class="divide-y">
                                <tr v-for="perm in (permissions.data || [])" :key="perm.id" class="border-b hover:bg-muted/50">
                                    <td class="py-3 px-4 font-mono text-primary font-medium">{{ perm.name }}</td>
                                    <td class="py-3 px-4">
                                        <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-md text-xs border">
                                            {{ perm.group }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="`/permissions/${perm.id}/edit`">
                                                <Button variant="ghost" size="icon" title="Sửa"><Edit class="h-4 w-4" /></Button>
                                            </Link>
                                            <Button variant="ghost" size="icon" class="text-destructive" @click="deletePermission(perm.id)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!permissions.data?.length">
                                    <td colspan="3" class="text-center py-8 text-muted-foreground italic">
                                        Chưa có quyền hạn nào hoặc không tìm thấy kết quả.
                                    </td>
                                </tr>
                            </tbody>
                            <tbody v-else-if="viewingRestore && restore.data.length > 0" class="divide-y">
                                <tr v-for="perm in restore.data" :key="perm.id" class="border-b hover:bg-muted/50">
                                    <td class="py-3 px-4 font-mono text-primary font-medium">{{ perm.name }}</td>
                                    <td class="py-3 px-4">
                                        <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-md text-xs border">
                                            {{ perm.group }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="ghost" size="icon" class="text-green-600" @click="restorePermission(perm.id)" title="Khôi phục">
                                                <RotateCcw class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="icon" class="text-destructive" @click="forceDeletePermission(perm.id)" title="Xóa vĩnh viễn">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!restore.data.length">
                                    <td colspan="3" class="text-center py-8 text-muted-foreground italic">
                                        Không có dữ liệu trong thùng rác.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
                
                <CardFooter class="border-t bg-gray-50/50 px-6 py-4" v-if="permissions.total > 0">
                    <div class="flex flex-col sm:flex-row items-center justify-between w-full gap-4">
                        <div class="text-sm text-gray-500">
                            Hiển thị <span class="font-medium text-gray-900">{{ permissions.data.length }}</span> trên tổng số <span class="font-medium text-gray-900">{{ permissions.total }}</span> kết quả
                        </div>
                        <div class="flex items-center space-x-1">
                            <template v-for="(link, index) in permissions.links" :key="index">
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