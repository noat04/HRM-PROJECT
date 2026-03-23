<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';

import { Head, Link, router,usePage } from '@inertiajs/vue3';
import { computed,ref,watch } from 'vue'; // 👇 Import computed của Vue
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle, CardDescription ,CardFooter} from '@/components/ui/card';
import { Pencil, Trash2, Eye, Plus,Search } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface Department {
    id: number;
    name: string;
    parent_id: number | null;
    manager_id: number | null;
    description: string;
    level: number;
    create_at: EpochTimeStamp;
    updated_at: EpochTimeStamp;
}


// 1. Cập nhật Interface để khớp với dữ liệu phân trang của Laravel
//quy định chính xác những dữ liệu gì mà Laravel (Backend) 
// sẽ gửi sang cho Vue.js (Frontend) khi bạn dùng hàm phân trang paginate()

interface PaginatedDepartments {
    data: Department[];
    current_page: number;
    total: number;
    per_page: number;
    filters?: { search?: string }; // Nhận từ khóa tìm kiếm
    // 👇 Thêm dòng này để nhận danh sách nút bấm từ Laravel
    links: { url: string | null; label: string; active: boolean }[]; 
}

// 2. Sửa lại props

//hàm đặc biệt (compiler macro) dùng để khai báo các Props 
// (viết tắt của Properties - các biến dữ liệu được truyền từ bên ngoài vào trong Component).

//processing?: boolean;
//Biến này thường được dùng để quản lý trạng thái tải trang (Loading). 
// Ví dụ: Khi người dùng bấm nút tìm kiếm, bạn có thể truyền processing: true để giao diện hiện cái vòng xoay (Spinner)
const props = defineProps<{
    departments: PaginatedDepartments; // Thay vì Department[]
    processing?: boolean;
}>();


const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Phòng ban', href: '/departments' },
];

// ==========================================
// LOGIC TÌM KIẾM (SEARCH)
// ==========================================
// Khởi tạo biến search bằng từ khóa cũ (nếu có)
const search = ref(props.departments?.filters?.search || '');

let searchTimeout: ReturnType<typeof setTimeout>;

// Lắng nghe mỗi khi ô tìm kiếm thay đổi
watch(search, (value) => {
    clearTimeout(searchTimeout);
    
    // Đợi 300ms (0.3 giây) sau khi ngừng gõ mới bắt đầu tìm kiếm
    searchTimeout = setTimeout(() => {
        router.get('/departments', 
            { search: value }, // Gửi tham số search lên server (tự động đưa về trang 1)
            { 
                preserveState: true, // Giữ nguyên trạng thái trang (không giật lag)
                replace: true        // Không lưu vào lịch sử back của trình duyệt cho mỗi phím gõ
            }
        );
    }, 300);
});

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
        showFlash.value = true; // Bật thông báo lên

        // Hẹn giờ 3 giây (3000ms) sau thì tắt đi
        setTimeout(() => {
            showFlash.value = false;
        }, 3000); 
    }
}, { immediate: true }); // immediate: true giúp kiểm tra ngay lần đầu trang được tải


// Hàm Xóa (Giữ nguyên vì thao tác xóa thường làm trực tiếp trên bảng)
const deleteDepartment = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn xóa phòng ban này không?')) {
        router.delete(`/departments/${id}`);
    }
};
</script>

<template>
    <Head title="Danh sách Phòng ban" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
           <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Quản lý Phòng ban</h1>
                    <p class="text-muted-foreground">Danh sách các cơ quan/phòng ban trong hệ thống.</p>
                </div>
    
                <div class="flex items-center gap-4">
                        <div class="relative w-64">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input 
                                v-model="search" 
                                type="text" 
                                placeholder="Tìm kiếm tên phòng ban..." 
                                class="pl-8 bg-white" 
                            />
                        </div>

                        <Link href="/departments/create">
                            <Button class="gap-2">
                                <Plus class="h-4 w-4" /> Thêm mới
                            </Button>
                        </Link>
                </div>
            </div>
        </div>
    
            <!-- Thông báo Flash Message -->
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
                    <CardTitle>Dữ liệu phòng ban</CardTitle>
                    <CardDescription>Hiển thị tổng cộng {{ departments.total }} phòng ban.</CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-sm">
                            <thead>
                                <tr class="bg-muted/50 transition-colors">
                                    <th class="h-12 w-[80px] border-b px-6 text-left align-middle font-medium text-muted-foreground">ID</th>
                                    <th class="h-12 border-b px-6 text-left align-middle font-medium text-muted-foreground">Tên Phòng Ban</th>
                                    <th class="h-12 border-b px-6 text-left align-middle font-medium text-muted-foreground">Mô tả</th>
                                    <th class="h-12 border-b px-6 text-left align-middle font-medium text-muted-foreground">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="dept in departments.data" :key="dept.id" class="transition-colors hover:bg-muted/30">
                                    <td class="px-6 py-4 align-middle font-mono text-xs text-muted-foreground">#{{ dept.id }}</td>
                                    <td class="px-6 py-4 align-middle font-semibold text-foreground">{{ dept.name }}</td>
                                    <td class="px-6 py-4 align-middle text-muted-foreground">{{ dept.description || 'Chưa cập nhật' }}</td>
                                    
                                    <td class="px-6 py-4 align-middle">
                                        <div class="flex justify-left gap-2">
                                            <Link :href="`/departments/${dept.id}`">
                                                <Button variant="secondary" size="sm" class="h-8 gap-1">
                                                    <Eye class="h-3.5 w-3.5" />
                                                    <span class="hidden md:inline">Chi tiết</span>
                                                </Button>
                                            </Link>

                                            <Link :href="`/departments/${dept.id}/edit`">
                                                <Button variant="outline" size="sm" class="h-8 gap-1">
                                                    <Pencil class="h-3.5 w-3.5" />
                                                    <span class="hidden md:inline">Sửa</span>
                                                </Button>
                                            </Link>

                                            <Button @click="deleteDepartment(dept.id)" variant="destructive" size="sm" class="h-8 gap-1">
                                                <Trash2 class="h-3.5 w-3.5" />
                                                <span class="hidden md:inline">Xóa</span>
                                            </Button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="departments.data.length === 0">
                                    <td colspan="4" class="h-24 text-center text-muted-foreground">
                                        Không tìm thấy dữ liệu phòng ban nào.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
                <CardFooter class="border-t px-6 py-4">
                    <div class="flex w-full items-center justify-between">
                        <div class="text-xs text-muted-foreground">
                            Hiển thị {{ departments.data.length }} trên tổng số {{ departments.total }} kết quả
                        </div>
                        
                        <div class="flex items-center space-x-1 mt-4">
                            <!-- Khi bạn gọi hàm Department::paginate(3) ở Backend, Laravel rất thông minh. Nó không chỉ trả về dữ liệu bảng, 
                             mà còn tự động tính toán và sinh ra một mảng tên là links chứa toàn bộ cấu trúc các nút bấm phân trang. -->
                            <template v-for="(link, index) in departments.links" :key="index">
                               <Button
                                    v-if="link.url"
                                    @click="router.get(link.url, {}, { preserveState: true })"
                                    :variant="link.active ? 'default' : 'outline'"
                                    size="sm"
                                    class="min-w-8"
                                >
                                    <span v-html="link.label"></span>
                                </Button>
                                
                                <Button
                                    v-else
                                    variant="ghost"
                                    size="sm"
                                    disabled
                                    class="min-w-8"
                                >
                                    <span v-html="link.label"></span>
                                </Button>
                            </template>
                        </div>
                        </div>
                </CardFooter>
            </Card>
    </AppLayout>
</template>