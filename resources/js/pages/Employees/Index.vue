<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Pencil, Trash2, Eye, Plus, Search, RotateCcw, RefreshCw } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import EmployeeStatus from '@/enums/EmployeeStatus';
import { usePermission } from '@/composables/usePermission';
import { Filter } from '@/components/ui/filter';

const { hasRole, hasPermission } = usePermission();

interface Department { id: number; name: string; }
interface Position { id: number; name: string; }
interface Employee {
    id: number; full_name: string; employee_code: string; gender: string; birthday: string;
    address: string; join_date: string; resignation_date: string | null; bank_account_number: string;
    bank_name: string; status: string; department_id: number | null; position_id: number | null;
    manager_id: number | null; user_id: number;
}
interface PaginatedEmployees {
    data: Employee[]; current_page: number; total: number; per_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    employees: PaginatedEmployees;
    departments: Department[];
    positions: Position[];
    restore: { data: Employee[] };
    filters: any;
    is_trashed?: string;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [{ title: 'Quản lý nhân viên', href: '/employees' }]);
const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const showFlash = ref(false);

watch(flashSuccess, (newMessage) => {
    if (newMessage) { showFlash.value = true; setTimeout(() => { showFlash.value = false; }, 3000); }
}, { immediate: true });

// ==========================================
// HELPER FUNCTIONS
// ==========================================
const getDepartmentName = (id: number | null) => props.departments.find(d => d.id === id)?.name || 'Chưa xếp phòng';
const getPositionName = (id: number | null) => props.positions.find(p => p.id === id)?.name || 'Chưa cấp bậc';
const getGenderLabel = (gender: string) => gender === 'male' ? 'Nam' : 'Nữ';
const getStatusLabel = (status: string) => EmployeeStatus.label(status) || 'Không xác định';
const getStatusClass = (status: string) => EmployeeStatus.classes[status] || 'bg-gray-100 text-gray-800 border-gray-200';

const handleDelete = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn xóa nhân viên này? Dữ liệu không thể khôi phục!')) router.delete(`/employees/${id}`);
};
const restoreEmployee = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn khôi phục nhân viên này không?')) router.put(`/employees/${id}/restore`);
};
const forceDeleteEmployee = (id: number) => {
    if (confirm('Dữ liệu sẽ bị xóa vĩnh viễn. Bạn có chắc chắn?')) router.delete(`/employees/${id}/force-delete`);
};

// ==========================================
// REFRESH & TRASH
// ==========================================
const isRefreshing = ref(false);
const refreshData = () => {
    router.reload({ only: ['employees', 'restore'], onStart: () => isRefreshing.value = true, onFinish: () => isRefreshing.value = false });
};
const viewingRestore = ref(props.is_trashed === 'true');
const toggleRestoreView = () => {
    filters.value.is_trashed = !filters.value.is_trashed;
    viewingRestore.value = filters.value.is_trashed; 
    selectedIds.value = []; // Reset checkbox
};

// ==========================================
// BULK ACTIONS (THAO TÁC HÀNG LOẠT)
// ==========================================
const selectedIds = ref<number[]>([]);

const isAllSelected = computed(() => {
    const dataSource = viewingRestore.value ? props.restore.data : props.employees.data;
    return dataSource.length > 0 && selectedIds.value.length === dataSource.length;
});

const toggleSelectAll = (event: Event) => {
    const isChecked = (event.target as HTMLInputElement).checked;
    const dataSource = viewingRestore.value ? props.restore.data : props.employees.data;
    selectedIds.value = isChecked ? dataSource.map(item => item.id) : [];
};

const bulkDeleteSelected = () => {
    if (selectedIds.value.length === 0) return alert('Vui lòng chọn ít nhất 1 mục để xóa.');
    if (confirm(`Xóa ${selectedIds.value.length} nhân viên này?`)) {
        router.delete('/employees/bulk-delete', { data: { ids: selectedIds.value }, preserveState: true, onSuccess: () => selectedIds.value = [] });
    }
};

const bulkRestoreSelected = () => {
    if (selectedIds.value.length === 0) return alert('Vui lòng chọn ít nhất 1 mục để khôi phục.');
    if (confirm(`Khôi phục ${selectedIds.value.length} nhân viên này?`)) {
        router.put('/employees/bulk-restore', { ids: selectedIds.value }, { preserveState: true, onSuccess: () => selectedIds.value = [] });
    }
};

const bulkForceDeleteSelected = () => {
    if (selectedIds.value.length === 0) return;
    if (confirm(`XÓA VĨNH VIỄN ${selectedIds.value.length} nhân viên này? Hành động này không thể hoàn tác!`)) {
        router.delete('/employees/bulk-force-delete', { data: { ids: selectedIds.value }, preserveState: true, onSuccess: () => selectedIds.value = [] });
    }
};

// ==========================================
// FILTER LOGIC
// ==========================================
const filters = ref({
    search: props.filters?.search || '',
    department_id: props.filters?.department_id ? props.filters.department_id.split(',') : [],
    position_id: props.filters?.position_id ? props.filters.position_id.split(',') : [],
    gender: props.filters?.gender ? props.filters.gender.split(',') : [],
    status: props.filters?.status ? props.filters.status.split(',') : [],
    is_trashed: props.is_trashed === 'true'
});

watch(filters, (newFilters) => {
    router.get('/employees', {
        search: newFilters.search, department_id: newFilters.department_id.join(','), position_id: newFilters.position_id.join(','),    
        gender: newFilters.gender.join(','), status: newFilters.status.join(','), is_trashed: newFilters.is_trashed ? 'true' : 'false'
    }, { preserveState: true, preserveScroll: true, replace: true });
}, { deep: true });

const formatOptions = (data: any) => {
    if (!data) return [];
    const safeArray = Array.isArray(data) ? data : Object.values(data);
    return safeArray.map((item: any) => ({ label: item.name, value: item.id }));
};

const filterConfig = [
    { key: 'search', type: 'text' as const, placeholder: 'Tìm kiếm tên, mã NV...' },
    { key: 'department_id', type: 'multi-checkbox' as const, placeholder: 'Phòng ban', options: formatOptions(props.departments) },
    { key: 'position_id', type: 'multi-checkbox' as const, placeholder: 'Chức vụ', options: formatOptions(props.positions) },
    { key: 'gender', type: 'multi-checkbox' as const, placeholder: 'Giới tính', options: [{ label: 'Nam', value: 'male' }, { label: 'Nữ', value: 'female' }] },
    { key: 'status', type: 'multi-checkbox' as const, placeholder: 'Trạng thái', options: [{ label: 'Thử việc', value: 'probation' }, { label: 'Chính thức', value: 'official' }, { label: 'Đã nghỉ việc', value: 'resigned' }] }
];
</script>

<template>
    <Head title="Danh sách Nhân viên" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Quản lý Nhân sự</h1>
                    <p class="text-muted-foreground">Danh sách nhân viên đang làm việc trong công ty.</p>
                </div>
    
                <div class="flex items-center gap-2">
                    <div v-if="selectedIds.length > 0" class="flex items-center gap-2 mr-2">
                        <Button v-if="!viewingRestore" variant="destructive" @click="bulkDeleteSelected" class="gap-2">
                            <Trash2 class="h-4 w-4" /> Xóa {{ selectedIds.length }} nhân viên
                        </Button>
                        <template v-else>
                            <Button variant="outline" class="gap-2 text-green-700 border-green-200 bg-green-50" @click="bulkRestoreSelected">
                                <RotateCcw class="h-4 w-4" /> Khôi phục {{ selectedIds.length }}
                            </Button>
                            <Button variant="destructive" class="gap-2" @click="bulkForceDeleteSelected">
                                <Trash2 class="h-4 w-4" /> Xóa vĩnh viễn {{ selectedIds.length }}
                            </Button>
                        </template>
                    </div>

                    <div class="flex gap-2">
                        <Button @click="toggleRestoreView" :variant="viewingRestore ? 'secondary' : 'outline'" class="gap-2">
                            <Trash2 class="h-4 w-4" /> {{ viewingRestore ? 'Trở lại' : 'Thùng rác' }}
                        </Button>
                        <div v-if="hasPermission('employee_create')">
                            <Link href="/employees/create">
                                <Button class="gap-2"><Plus class="h-4 w-4" /> Thêm nhân viên</Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
    
            <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-300" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                <div v-if="flashSuccess && showFlash" class="flex items-center rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-200 text-emerald-800 shadow-sm">
                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ flashSuccess }}</span>
                </div>
            </Transition>

            <Card>
                <CardHeader class="px-6 py-4 border-b bg-gray-50/50">
                    <CardTitle>{{ viewingRestore ? 'Thùng rác nhân viên' : 'Dữ liệu nhân viên' }}</CardTitle>
                    <CardDescription v-if="!viewingRestore">Hiển thị tổng cộng {{ employees.total }} nhân viên.</CardDescription>
                    <CardDescription v-else>Hiển thị danh sách nhân viên đã xóa mềm.</CardDescription>
                    
                    <div class="flex items-center gap-2 mt-4">
                        <Filter :config="filterConfig" v-model="filters" />
                        <Button variant="outline" size="icon" @click="refreshData" :disabled="isRefreshing" title="Làm mới dữ liệu">
                            <RefreshCw class="h-4 w-4 text-gray-600" :class="{ 'animate-spin text-blue-600': isRefreshing }" />
                        </Button>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                <tr>
                                    <th scope="col" class="px-6 py-3 w-10">
                                        <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" class="rounded border-gray-300 text-blue-600" />
                                    </th>
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
                                    <td class="px-6 py-4"><input type="checkbox" :value="employee.id" v-model="selectedIds" class="rounded border-gray-300" /></td>
                                    <td class="px-6 py-4 font-mono font-semibold text-primary">{{ employee.employee_code }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ employee.full_name }}</td>
                                    <td class="px-6 py-4 text-muted-foreground">{{ getDepartmentName(employee.department_id) }}</td>
                                    <td class="px-6 py-4 text-muted-foreground">{{ getPositionName(employee.position_id) }}</td>
                                    <td class="px-6 py-4 text-muted-foreground">{{ getGenderLabel(employee.gender) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-full text-xs border" :class="getStatusClass(employee.status)">{{ getStatusLabel(employee.status) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right flex justify-end gap-1">
                                        <Link v-if="hasPermission('employee_view_all')" :href="`/employees/${employee.id}`"><Button variant="ghost" size="icon"><Eye class="h-4 w-4 text-blue-600" /></Button></Link>
                                        <Link v-if="hasPermission('employee_update')" :href="`/employees/${employee.id}/edit`"><Button variant="ghost" size="icon"><Pencil class="h-4 w-4 text-amber-600" /></Button></Link>
                                        <Button v-if="hasPermission('employee_delete')" variant="ghost" size="icon" @click="handleDelete(employee.id)"><Trash2 class="h-4 w-4 text-red-600" /></Button>
                                    </td>
                                </tr>
                                <tr v-if="employees.data.length === 0">
                                    <td colspan="8" class="h-32 text-center text-muted-foreground">Không tìm thấy dữ liệu.</td>
                                </tr>
                            </tbody>
                            <tbody v-else class="divide-y bg-red-50/10">
                                <tr v-for="employee in restore.data" :key="employee.id" class="bg-white hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4"><input type="checkbox" :value="employee.id" v-model="selectedIds" class="rounded border-gray-300" /></td>
                                    <td class="px-6 py-4 font-mono font-semibold text-primary">{{ employee.employee_code }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ employee.full_name }}</td>
                                    <td class="px-6 py-4 text-muted-foreground">{{ getDepartmentName(employee.department_id) }}</td>
                                    <td class="px-6 py-4 text-muted-foreground">{{ getPositionName(employee.position_id) }}</td>
                                    <td class="px-6 py-4 text-muted-foreground">{{ getGenderLabel(employee.gender) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-full text-xs border" :class="getStatusClass(employee.status)">{{ getStatusLabel(employee.status) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right flex justify-end gap-2">
                                        <Button variant="outline" size="sm" class="text-green-600 border-green-200" @click="restoreEmployee(employee.id)"><RotateCcw class="h-4 w-4 mr-1"/> Khôi phục</Button>
                                        <Button variant="destructive" size="sm" @click="forceDeleteEmployee(employee.id)"><Trash2 class="h-4 w-4 mr-1" /> Xóa</Button>
                                    </td>
                                </tr>
                                <tr v-if="!restore.data || restore.data.length === 0">
                                    <td colspan="8" class="h-32 text-center text-muted-foreground">Thùng rác trống.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
                
                <CardFooter v-if="!viewingRestore" class="border-t px-6 py-4">
                    <div class="flex w-full items-center justify-between">
                        <div class="text-xs text-muted-foreground">
                            Hiển thị {{ employees.data.length }} trên tổng số {{ employees.total }} kết quả
                        </div>
                        <div class="flex items-center space-x-1 mt-4">
                            <template v-for="(link, index) in employees.links" :key="index">
                                <Button v-if="link.url" @click="router.get(link.url, {}, { preserveState: true })" :variant="link.active ? 'default' : 'outline'" size="sm" class="min-w-8">
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