<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Eye, Search, Users } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import EmployeeStatus from '@/enums/EmployeeStatus';

// 1. GIAO DIỆN CHỈ ĐỌC (READ-ONLY INTERFACE)
// Chú ý: Backend gửi sang dữ liệu đã bị ẩn lương và số tài khoản ngân hàng
interface Employee {
    id: number;
    full_name: string;
    employee_code: string;
    gender: string;
    join_date: string;
    status: string;
    // Giả sử Backend dùng Eager Loading (with('position', 'department'))
    position?: { name: string };
    department?: { name: string };
    avatar_url?: string;
}

interface PaginatedEmployees {
    data: Employee[];
    current_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    employees: PaginatedEmployees;
    filters?: { search?: string };
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Quản lý Đội nhóm', href: '/manager/team' },
    { title: 'Hồ sơ nhân sự', href: '/manager/team' },
]);

// ==========================================
// LOGIC TÌM KIẾM BẰNG SERVER
// ==========================================
const search = ref(props.filters?.search || '');
let searchTimeout: ReturnType<typeof setTimeout>;

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/manager/team', 
            { search: value },
            { preserveState: true, replace: true }
        );
    }, 300);
});

// ==========================================
// CÁC HÀM TIỆN ÍCH (HELPER)
// ==========================================
const getStatusLabel = (status: string) => EmployeeStatus.label(status) || 'Không xác định';
const getStatusClass = (status: string) => EmployeeStatus.classes[status] || 'bg-gray-100 text-gray-800 border-gray-200';
const getGenderLabel = (gender: string) => gender === 'male' ? 'Nam' : 'Nữ';

// Format ngày tháng (VD: 2026-05-18 -> 18/05/2026)
const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('vi-VN').format(date);
};
</script>

<template>
    <Head title="Hồ sơ nhân sự Đội nhóm" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                        <Users class="h-6 w-6" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Nhân sự Đội nhóm</h1>
                        <p class="text-muted-foreground">Danh sách các nhân viên cấp dưới trực tiếp của bạn.</p>
                    </div>
                </div>
    
                <div class="relative w-full sm:w-72">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm kiếm tên hoặc mã NV..." 
                        class="pl-8 bg-white" 
                    />
                </div>
            </div>

            <Card>
                <CardHeader class="px-6 py-4 border-b bg-gray-50/50">
                    <CardTitle>Danh sách thành viên</CardTitle>
                    <CardDescription>Hiển thị tổng cộng {{ employees.total }} nhân viên trong nhóm.</CardDescription>
                </CardHeader>
                
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-medium">Nhân viên</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Phòng ban</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Chức vụ</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Giới tính</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Ngày vào làm</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Trạng thái</th>
                                    <th scope="col" class="px-6 py-3 text-right font-medium">Hồ sơ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="employee in employees.data" :key="employee.id" class="bg-white hover:bg-gray-50 transition-colors">
                                    
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-full bg-gray-200 overflow-hidden flex-shrink-0">
                                                <img v-if="employee.avatar_url" :src="employee.avatar_url" alt="" class="h-full w-full object-cover" />
                                                <div v-else class="h-full w-full flex items-center justify-center font-bold text-gray-500 bg-gray-100">
                                                    {{ employee.full_name.charAt(0).toUpperCase() }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ employee.full_name }}</div>
                                                <div class="text-xs text-primary font-mono">{{ employee.employee_code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-muted-foreground">{{ employee.department?.name || '-' }}</td>
                                    <td class="px-6 py-4 text-muted-foreground">{{ employee.position?.name || '-' }}</td>
                                    <td class="px-6 py-4 text-muted-foreground">{{ getGenderLabel(employee.gender) }}</td>
                                    <td class="px-6 py-4 text-muted-foreground">{{ formatDate(employee.join_date) }}</td>
                                    
                                    <td class="px-6 py-4">
                                        <span 
                                            class="px-2.5 py-1 rounded-full text-xs border"
                                            :class="getStatusClass(employee.status)"
                                        >
                                            {{ getStatusLabel(employee.status) }}
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="`/manager/team/${employee.id}`">
                                            <Button variant="outline" size="sm" class="gap-1 border-blue-200 text-blue-700 hover:bg-blue-50">
                                                <Eye class="h-3.5 w-3.5" /> Xem
                                            </Button>
                                        </Link>
                                    </td>
                                </tr>
                                
                                <tr v-if="employees.data.length === 0">
                                    <td colspan="7" class="h-32 text-center text-muted-foreground">
                                        <div class="flex flex-col items-center justify-center">
                                            <Users class="h-8 w-8 mb-2 opacity-30" />
                                            <p>Bạn hiện tại chưa quản lý nhân viên nào hoặc không tìm thấy kết quả.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
                
                <CardFooter class="border-t px-6 py-4">
                    <div class="flex w-full items-center justify-between">
                        <div class="text-xs text-muted-foreground">
                            Hiển thị {{ employees.data.length }} trên tổng số {{ employees.total }} kết quả
                        </div>
                        <div class="flex items-center space-x-1 mt-4 sm:mt-0">
                            <template v-for="(link, index) in employees.links" :key="index">
                                <Button
                                    v-if="link.url"
                                    @click="router.get(link.url, {}, { preserveState: true })"
                                    :variant="link.active ? 'default' : 'outline'"
                                    size="sm" class="min-w-8"
                                >
                                    <span v-html="link.label"></span>
                                </Button>
                                <Button v-else variant="ghost" size="sm" disabled class="min-w-8">
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