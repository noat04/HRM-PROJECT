<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router,usePage } from '@inertiajs/vue3';
import { computed,ref,watch } from 'vue'; // 👇 Import computed của Vue
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle, CardDescription ,CardFooter} from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { Pencil, Trash2, Eye, Plus,Search } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

// ==========================================
// KHAI BÁO BIẾN
// ==========================================
interface User {
    id: number;
    name: string;
    email: string;
    password_display: string;
    avatar: string;
    status: string;
    created_at: EpochTimeStamp;
    updated_at: EpochTimeStamp;
    deleted_at: EpochTimeStamp;
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

// Tạo một công tắc quản lý trạng thái hiển thị
const showFlash = ref(false);

// Theo dõi xem flashSuccess có thông báo mới không
watch(flashSuccess, (newMessage) => {
    if (newMessage) {
        // Bật thông báo lên
        showFlash.value = true;
        // Hẹn giờ 3 giây (3000ms) sau thì tắt đi
        setTimeout(() => {
            // Tắt thông báo
            showFlash.value = false;
        }, 3000);
    }
}, { immediate: true });


// ==========================================
// LOGIC XỬ LÝ TRẠNG THÁI VÀ PASSWORD
// ==========================================

// Hàm xử lý trạng thái (Active/Inactive)
const toggleStatus = (user: User) => {
    // Xác nhận hành động
    if (confirm(`Bạn có chắc chắn muốn ${user.status === 'active' ? 'khóa' : 'kích hoạt'} tài khoản này không?`)) {
        // Gửi yêu cầu cập nhật trạng thái
        router.put(`/users/${user.id}/status`, {
            status: user.status === 'active' ? 'inactive' : 'active'
        });
    }
};

// Hàm lấy màu sắc dựa trên trạng thái
const getStatusColor = (status: string) => {
    return status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
};

// Hàm lấy icon dựa trên trạng thái
const getStatusIcon = (status: string) => {
    return status === 'active' ? '✅' : '🚫';
};

const passwordFormat = (password: string) => {
    return password.replace(/./g, '*');
};



// ==========================================
// LOGIC XỬ LÝ CRUD
// ==========================================


// Hàm Xóa (Giữ nguyên vì thao tác xóa thường làm trực tiếp trên bảng)
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

// Lắng nghe sự thay đổi của ô tìm kiếm
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
    <Head title = "Danh sách người dùng"></Head>
    <!-- Layout chính -->
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Thanh tiêu đề và nút bấm -->
            <div class="flex items-center justify-betweens">

                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Quản lý Người dùng</h1>
                    <p class="text-muted-foreground">Danh sách các người dùng trong hệ thống.</p>
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative w-64">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <!-- Input tìm kiếm -->
                        <!-- v-model : Liên kết dữ liệu hai chiều (Two-way Data Binding). -->
                        <Input 
                            v-model="search" 
                            type="text" 
                            placeholder="Tìm kiếm tên người dùng..." 
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
        </div>
        <!-- Thông báo Flash Message -->
            <!-- Transition : Thẻ này dùng để tạo hiệu ứng chuyển động (animation) cho việc hiển thị hoặc ẩn đi một phần tử. 
                enter-active-class: Lớp CSS được áp dụng khi phần tử bắt đầu xuất hiện (transition đang diễn ra).
                enter-from-class: Lớp CSS xác định trạng thái ban đầu của phần tử trước khi animation bắt đầu.
                enter-to-class: Lớp CSS xác định trạng thái cuối cùng của phần tử khi animation kết thúc.
                leave-active-class: Lớp CSS được áp dụng khi phần tử bắt đầu biến mất (transition đang diễn ra).
                leave-from-class: Lớp CSS xác định trạng thái ban đầu của phần tử trước khi animation bắt đầu biến mất.
                leave-to-class: Lớp CSS xác định trạng thái cuối cùng của phần tử khi animation kết thúc biến mất.
            -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-300"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <!-- v-if="flashSuccess && showFlash" : Chỉ hiển thị khi có thông báo (flashSuccess) và công tắc hiển thị (showFlash) đang bật. -->
                <div 
                    v-if="flashSuccess && showFlash" 
                    class="flex items-center rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-200 text-emerald-800 shadow-sm"
                >
                    <!-- Việc sử dụng SVG (đồ họa vector) thay vì hình ảnh thông thường (.png, .jpg) giúp icon này không bao giờ bị mờ hay vỡ hạt dù bạn có phóng to nó lên cỡ nào đi nữa. -->
                    <svg class="mr-2 h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <!-- path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" : Đường dẫn của icon (hình dấu tích) -->
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <!-- {{ flashSuccess }} : Hiển thị nội dung thông báo -->
                    <span class="font-medium">{{ flashSuccess }}</span>
                </div>
            </Transition>
            <Card>
                <CardHeader class="px-6 py-4">
                    <CardTitle>Dữ liệu người dùng</CardTitle>
                    <CardDescription>Hiển thị tổng cộng {{ users.total }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">ID</th>
                                    <th scope="col" class="px-6 py-3">Tên</th>
                                    <th scope="col" class="px-6 py-3">Email</th>
                                    <!-- <th scope="col" class="px-6 py-3">Password</th> -->
                                    <th scope="col" class="px-6 py-3">Trạng thái</th>
                                    <th scope="col" class="px-6 py-3">Ngày tạo</th>
                                    <th scope="col" class="px-6 py-3">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users.data" :key="user.id" class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4">{{ user.id }}</td>
                                    <td class="px-6 py-4">{{ user.name }}</td>
                                    <td class="px-6 py-4">{{ user.email }}</td>
                                    <!-- <td class="px-6 py-4">{{ user.password_display }}</td> -->
                                    <td class="px-6 py-4">
                                        <span :class="getStatusColor(user.status)">
                                            {{ user.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">{{ formatDate(user.created_at) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <Link :href="`/users/${user.id}`">
                                                <Button variant="outline" size="icon" class="h-8 w-8">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="`/users/${user.id}/edit`">
                                                <Button variant="outline" size="icon" class="h-8 w-8">
                                                    <Pencil class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button variant="outline" size="icon" class="h-8 w-8" @click="deleteUser(user.id)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                 <tr v-if="users.data.length === 0">
                                    <td colspan="4" class="h-24 text-center text-muted-foreground">
                                        Không tìm thấy dữ liệu người dùng nào.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
                <CardFooter>
                    <div class="flex items-center justify-between w-full">
                        <div class="text-sm text-gray-500">
                            Hiển thị {{ users?.data?.length }} trên {{ users.total }} kết quả
                        </div>
                        <div class="flex items-center space-x-1 mt-4">
                            <!-- Khi bạn gọi hàm Department::paginate(3) ở Backend, Laravel rất thông minh. Nó không chỉ trả về dữ liệu bảng, 
                             mà còn tự động tính toán và sinh ra một mảng tên là links chứa toàn bộ cấu trúc các nút bấm phân trang. -->
                            <template v-for="(link, index) in users.links" :key="index">
                                <Button
                                    v-if="link.url"
                                    @click="router.get(link.url, {}, { preserveState: true })"
                                    :variant="link.active ? 'default' : 'outline'"
                                    size="sm"
                                    class="min-w-8"
                                    v-html="link.label"
                                />
                                <Button
                                    v-else
                                    variant="ghost"
                                    size="sm"
                                    disabled
                                    class="min-w-8"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </CardFooter>
            </Card>
        
    </AppLayout>
</template>