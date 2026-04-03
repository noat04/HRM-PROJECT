<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Pencil, Trash2, Eye, Plus, Search,RotateCcw } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import EmployeeStatus from '@/enums/EmployeeStatus';
import { usePermission } from '@/composables/usePermission';

const { hasRole,hasPermission } = usePermission();
// 1. KHAI BÁO ĐẦY ĐỦ CÁC INTERFACE
interface Department {
    id: number;
    name: string;
}

interface Position {
    id: number;
    name: string;
}

interface Employee {
    id: number;
    full_name: string;
    employee_code: string;
    gender: string;
    birthday: string;
    address: string;
    join_date: string;
    resignation_date: string | null;
    bank_account_number: string;
    bank_name: string;
    status: string;
    department_id: number | null;
    position_id: number | null;
    manager_id: number | null;
    user_id: number;
}

// Interface phân trang chuẩn của Laravel
interface PaginatedEmployees {
    data: Employee[];
    current_page: number;
    total: number;
    per_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

// 2. NHẬN PROPS TỪ CONTROLLER
const props = defineProps<{
    employees: PaginatedEmployees;
    departments: Department[];
    positions: Position[];
    restore: Employee[];
    filters?: { search?: string };
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Quản lý nhân viên', href: '/employees' },
]);

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
// LOGIC TÌM KIẾM BẰNG SERVER (Backend)
// ==========================================
const search = ref(props.filters?.search || '');
let searchTimeout: ReturnType<typeof setTimeout>;

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/employees', 
            { search: value },
            { preserveState: true, replace: true }
        );
    }, 300);
});

// ==========================================
// CÁC HÀM TIỆN ÍCH (HELPER)
// ==========================================
const getDepartmentName = (departmentId: number | null) => {
    if (!departmentId) return 'Chưa xếp phòng';
    const department = props.departments.find(d => d.id === departmentId);
    return department ? department.name : 'Chưa xếp phòng';
};

const getPositionName = (positionId: number | null) => {
    if (!positionId) return 'Chưa cấp bậc';
    const position = props.positions.find(p => p.id === positionId);
    return position ? position.name : 'Chưa cấp bậc';
};

const getGenderLabel = (gender: string) => {
    return gender === 'male' ? 'Nam' : 'Nữ';
};

const getStatusLabel = (status: string) => {
    return EmployeeStatus.label(status) || 'Không xác định';
};

const getStatusClass = (status: string) => {
    return EmployeeStatus.classes[status] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const handleDelete = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn xóa nhân viên này? Dữ liệu không thể khôi phục!')) {
        router.delete(`/employees/${id}`);
    }
};

const viewingRestore = ref(false);

const toggleRestoreView = () => {
    viewingRestore.value = !viewingRestore.value;
};

const restoreEmployee = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn khôi phục nhân viên này không?')) {
        router.put(`/employees/${id}/restore`);
    }
};

const forceDeleteEmployee = (id: number) => {
    if (confirm('Dữ liệu sẽ bị xóa vĩnh viễn và không thể khôi phục. Bạn có chắc chắn?')) {
        router.delete(`/employees/${id}/force-delete`);
    }
};
</script>

<template>
    <Head title="Danh sách Nhân viên" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Quản lý Nhân sự</h1>
                    <p class="text-muted-foreground">Danh sách nhân viên đang làm việc trong công ty.</p>
                </div>
    
                <div class="flex items-center gap-4">
                    <div class="relative w-64">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input 
                            v-model="search" 
                            type="text" 
                            placeholder="Tìm kiếm họ tên..." 
                            class="pl-8 bg-white" 
                        />
                    </div>
                    <div class="flex gap-2">
                        <Button @click="toggleRestoreView" :variant="viewingRestore ? 'secondary' : 'outline'" class="gap-2">
                            <Trash2 class="h-4 w-4" /> {{ viewingRestore ? 'Trở lại' : 'Thùng rác' }}
                        </Button>
                         <div v-if="hasPermission('employee.create')">
                            <Link href="/employees/create">
                                <Button class="gap-2">
                                    <Plus class="h-4 w-4" /> Thêm nhân viên
                                </Button>
                            </Link>
                        </div>
                    </div>
                   
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
                <CardHeader class="px-6 py-4 border-b bg-gray-50/50">
                    <CardTitle>{{ viewingRestore ? 'Thùng rác nhân viên' : 'Dữ liệu nhân viên' }}</CardTitle>
                    <CardDescription v-if="!viewingRestore">Hiển thị tổng cộng {{ employees.total }} nhân viên.</CardDescription>
                    <CardDescription v-else>Hiển thị danh sách nhân viên đã xóa mềm lưu trong thùng rác.</CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-medium">Mã NV</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Họ & Tên</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Phòng ban</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Chức vụ</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Giới tính</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Trạng thái</th>
                                    <th scope="col" class="px-6 py-3 text-right font-medium">Hành động</th>
                                </tr>
                            </thead>
                           <tbody v-if="!viewingRestore" class="divide-y">
                                <tr v-for="employee in employees.data" :key="employee.id" class="bg-white hover:bg-gray-50 transition-colors">
                                    
                                    <td class="px-6 py-4 font-mono font-semibold text-primary">
                                        {{ employee.employee_code }}
                                    </td>
                                    
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ employee.full_name }}
                                    </td>
                                    
                                    <td class="px-6 py-4 text-muted-foreground">
                                        {{ getDepartmentName(employee.department_id) }}
                                    </td>
                                    
                                    <td class="px-6 py-4 text-muted-foreground">
                                        {{ getPositionName(employee.position_id) }}
                                    </td>

                                    <td class="px-6 py-4 text-muted-foreground">
                                        {{ getGenderLabel(employee.gender) }}
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <span 
                                            class="px-2.5 py-1 rounded-full text-xs border"
                                            :class="getStatusClass(employee.status)"
                                        >
                                            {{ getStatusLabel(employee.status) }}
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-1">
                                            <!-- <div  v-if="hasPermission('manager_employees')"> -->
                                                <div v-if="hasPermission('view_employees')">
                                                    <Link :href="`/employees/${employee.id}`">
                                                        <Button variant="ghost" size="icon" title="Xem hồ sơ">
                                                            <Eye class="h-4 w-4 text-blue-600" />
                                                        </Button>
                                                    </Link>
                                                </div>
                                                <div v-if="hasPermission('update_employees')">
                                                    <Link :href="`/employees/${employee.id}/edit`">
                                                        <Button variant="ghost" size="icon" title="Sửa thông tin">
                                                            <Pencil class="h-4 w-4 text-amber-600" />
                                                        </Button>
                                                    </Link>
                                                </div>
                                                <div v-if="hasPermission('delete_employees')">
                                                    <Button variant="ghost" size="icon" title="Xóa nhân viên" @click="handleDelete(employee.id)">
                                                        <Trash2 class="h-4 w-4 text-red-600" />
                                                    </Button>
                                                </div>
                                            <!-- </div> -->
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr v-if="employees.data.length === 0">
                                    <td colspan="7" class="h-32 text-center text-muted-foreground">
                                        <div class="flex flex-col items-center justify-center">
                                            <Search class="h-8 w-8 mb-2 opacity-30" />
                                            <p>Không tìm thấy nhân sự nào khớp với điều kiện tìm kiếm.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody v-else class="divide-y bg-red-50/10">
                                <tr v-for="employee in restore" :key="employee.id" class="bg-white hover:bg-gray-50 transition-colors">
                                    
                                    <td class="px-6 py-4 font-mono font-semibold text-primary">
                                        {{ employee.employee_code }}
                                    </td>
                                    
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ employee.full_name }}
                                    </td>
                                    
                                    <td class="px-6 py-4 text-muted-foreground">
                                        {{ getDepartmentName(employee.department_id) }}
                                    </td>
                                    
                                    <td class="px-6 py-4 text-muted-foreground">
                                        {{ getPositionName(employee.position_id) }}
                                    </td>

                                    <td class="px-6 py-4 text-muted-foreground">
                                        {{ getGenderLabel(employee.gender) }}
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <span 
                                            class="px-2.5 py-1 rounded-full text-xs border"
                                            :class="getStatusClass(employee.status)"
                                        >
                                            {{ getStatusLabel(employee.status) }}
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- <div v-if="hasPermission('employee.update')"> -->
                                                <Button variant="ghost" size="icon" title="Khôi phục" @click="restoreEmployee(employee.id)">
                                                    <RotateCcw class="h-4 w-4 text-blue-600" />
                                                </Button>
                                            <!-- </div>
                                            <div v-if="hasPermission('employee.delete')"> -->
                                                <Button variant="ghost" size="icon" title="Xóa vĩnh viễn" @click="forceDeleteEmployee(employee.id)">
                                                    <Trash2 class="h-4 w-4 text-red-600" />
                                                </Button>
                                            <!-- </div> -->
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
                        
                        <div class="flex items-center space-x-1 mt-4">
                            <template v-for="(link, index) in employees.links" :key="index">
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
        </div>
    </AppLayout>
</template>