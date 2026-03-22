<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { Pencil, Trash2, Eye, Plus, Search } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

// ==========================================
// KHAI BÁO BIẾN
// ==========================================
interface Role {
    id: number;
    name: string;
    display_name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    password_display?: string;
    avatar: string | null;
    status: string;
    roles: Role[]; // 👇 Đã thêm mảng roles từ backend gửi sang
    created_at: EpochTimeStamp;
    updated_at: EpochTimeStamp;
    deleted_at?: EpochTimeStamp;
}

interface PaginatedUsers {
    data: User[];
    current_page: number;
    total: number;
    per_page: number;
    filters?: { search?: string };
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    users: PaginatedUsers;
    processing?: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Người dùng', href: '/users' },
];

// ==========================================
// LOGIC XỬ LÝ THÔNG BÁO FLASH MESSAGE
// ==========================================
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

// ==========================================
// LOGIC XỬ LÝ TRẠNG THÁI VÀ PASSWORD
// ==========================================
const toggleStatus = (user: User) => {
    if (confirm(`Bạn có chắc chắn muốn ${user.status === 'active' ? 'khóa' : 'kích hoạt'} tài khoản này không?`)) {
        router.put(`/users/${user.id}/status`, {
            status: user.status === 'active' ? 'inactive' : 'active'
        });
    }
};

const getStatusColor = (status: string) => {
    return status === 'active' ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200';
};

// ==========================================
// LOGIC XỬ LÝ CRUD
// ==========================================
const deleteUser = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn xóa người dùng này không?')) {
        router.delete(`/users/${id}`);
    }
};

// ==========================================
// LOGIC XỬ LÝ TÌM KIẾM
// ==========================================
const search = ref(props.users?.filters?.search || '');
let searchTimeout: ReturnType<typeof setTimeout>;

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/users', 
            { search: value },
            { 
                preserveState: true,
                replace: true
            }
        );
    }, 300);
});

// ==========================================
// LOGIC XỬ LÝ NGÀY THÁNG
// ==========================================
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
    <Head title="Danh sách người dùng"></Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Quản lý Người dùng</h1>
                    <p class="text-muted-foreground">Danh sách các người dùng trong hệ thống.</p>
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative w-64">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input 
                            v-model="search" 
                            type="text" 
                            placeholder="Tìm kiếm tên, email..." 
                            class="pl-8 bg-white" 
                        />
                    </div>

                    <Link href="/users/create">
                        <Button class="gap-2">
                            <Plus class="h-4 w-4" /> Thêm mới
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
                    <svg class="mr-2 h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="font-medium">{{ flashSuccess }}</span>
                </div>
            </Transition>

            <Card>
                <CardHeader class="px-6 py-4">
                    <CardTitle>Dữ liệu người dùng</CardTitle>
                    <CardDescription>Hiển thị tổng cộng {{ users.total }} người dùng</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                <tr>
                                    <th scope="col" class="px-6 py-3">ID</th>
                                    <th scope="col" class="px-6 py-3">Tên</th>
                                    <th scope="col" class="px-6 py-3">Email</th>
                                    <th scope="col" class="px-6 py-3">Vai trò</th> <th scope="col" class="px-6 py-3">Trạng thái</th>
                                    <th scope="col" class="px-6 py-3">Ngày tạo</th>
                                    <th scope="col" class="px-6 py-3">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users.data" :key="user.id" class="bg-white border-b hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ user.id }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div v-if="user.avatar" class="h-8 w-8 rounded-full overflow-hidden bg-gray-100 border">
                                                <img :src="`/storage/${user.avatar}`" class="h-full w-full object-cover" alt="avatar" />
                                            </div>
                                            <div v-else class="h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                                                {{ user.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <span class="font-medium">{{ user.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">{{ user.email }}</td>
                                    
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-if="!user.roles || user.roles.length === 0" class="text-xs text-gray-400 italic">
                                                Chưa cấp quyền
                                            </span>
                                            <span 
                                                v-for="role in user.roles" 
                                                :key="role.id" 
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800"
                                            >
                                                {{ role.display_name || role.name }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <button 
                                            @click="toggleStatus(user)"
                                            :class="getStatusColor(user.status)"
                                            class="px-2.5 py-1 rounded-full text-xs font-medium cursor-pointer transition-colors"
                                        >
                                            {{ user.status === 'active' ? 'Hoạt động' : 'Đã khóa' }}
                                        </button>
                                    </td>

                                    <td class="px-6 py-4">{{ formatDate(user.created_at) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <Link :href="`/users/${user.id}`">
                                                <Button variant="outline" size="icon" class="h-8 w-8 text-blue-600 border-blue-200 hover:bg-blue-50">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="`/users/${user.id}/edit`">
                                                <Button variant="outline" size="icon" class="h-8 w-8 text-amber-600 border-amber-200 hover:bg-amber-50">
                                                    <Pencil class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button variant="outline" size="icon" class="h-8 w-8 text-red-600 border-red-200 hover:bg-red-50 hover:text-red-700" @click="deleteUser(user.id)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="users.data.length === 0">
                                    <td colspan="7" class="h-32 text-center text-muted-foreground">
                                        <div class="flex flex-col items-center justify-center">
                                            <Search class="h-8 w-8 mb-2 opacity-50" />
                                            <p>Không tìm thấy dữ liệu người dùng nào.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
                <CardFooter class="border-t bg-gray-50/50 px-6 py-4">
                    <div class="flex items-center justify-between w-full">
                        <div class="text-sm text-gray-500">
                            Hiển thị <span class="font-medium text-gray-900">{{ users.data.length }}</span> trên tổng số <span class="font-medium text-gray-900">{{ users.total }}</span> kết quả
                        </div>
                        <div class="flex items-center space-x-1">
                            <template v-for="(link, index) in users.links" :key="index">
                                <Button
                                    v-if="link.url"
                                    @click="router.get(link.url, {}, { preserveState: true })"
                                    :variant="link.active ? 'default' : 'outline'"
                                    size="sm"
                                    class="min-w-8 shadow-sm"
                                    v-html="link.label"
                                />
                                <Button
                                    v-else
                                    variant="outline"
                                    size="sm"
                                    disabled
                                    class="min-w-8 opacity-50"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>