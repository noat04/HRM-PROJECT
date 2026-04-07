<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Pencil, Trash2, Eye, Plus, Search, RotateCcw, RefreshCw } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import { Filter } from '@/components/ui/filter'; // Import Filter

interface Position {
    id: number;
    name: string;
    description: string;
    code: string;
    level: number;
    salary_min: number;
    salary_max: number;
    created_at: EpochTimeStamp;
    updated_at: EpochTimeStamp;
}

interface PaginatedPositions {
    data: Position[];
    current_page: number;
    total: number;
    per_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

// 👇 CẬP NHẬT PROPS ĐỂ KHỚP VỚI BACKEND MỚI
const props = defineProps<{
    positions: PaginatedPositions;
    restore: { data: Position[] }; // Đã sửa lại thành Object chứa mảng data
    filters: any;
    is_trashed?: string;
    code?: any;
    level?: any;
    salary_min?: any;
    salary_max?: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Chức vụ', href: '/positions' },
];

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const showFlash = ref(false);

watch(flashSuccess, (newMessage) => {
    if (newMessage) {
        showFlash.value = true;
        setTimeout(() => { showFlash.value = false; }, 3000);
    }
}, { immediate: true });

// ==========================================
// LOGIC THÙNG RÁC & REFRESH
// ==========================================
const isRefreshing = ref(false);
const refreshData = () => {
    router.reload({
        only: ['positions', 'restore'], 
        onStart: () => { isRefreshing.value = true; },
        onFinish: () => { isRefreshing.value = false; }
    });
};

const viewingRestore = ref(props.is_trashed === 'true');

const toggleRestoreView = () => {
    filters.value.is_trashed = !filters.value.is_trashed;
    viewingRestore.value = filters.value.is_trashed; 
    selectedIds.value = [];
};

// ==========================================
// LOGIC THAO TÁC ĐƠN LẺ
// ==========================================
const deletePosition = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn xóa chức vụ này không?')) router.delete(`/positions/${id}`);
};
const restorePosition = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn khôi phục chức vụ này không?')) router.put(`/positions/${id}/restore`);
};
const forceDeletePosition = (id: number) => {
    if (confirm('Dữ liệu sẽ bị xóa vĩnh viễn. Bạn có chắc chắn?')) router.delete(`/positions/${id}/force-delete`);
};

// ==========================================
// LOGIC CHECKBOX & THAO TÁC HÀNG LOẠT
// ==========================================
const selectedIds = ref<number[]>([]);

const isAllSelected = computed(() => {
    const dataSource = viewingRestore.value ? props.restore.data : props.positions.data;
    return dataSource.length > 0 && selectedIds.value.length === dataSource.length;
});

const toggleSelectAll = (event: Event) => {
    const isChecked = (event.target as HTMLInputElement).checked;
    const dataSource = viewingRestore.value ? props.restore.data : props.positions.data;
    selectedIds.value = isChecked ? dataSource.map(item => item.id) : [];
};

const bulkDeleteSelected = () => {
    if (selectedIds.value.length === 0) return alert('Vui lòng chọn ít nhất 1 mục để xóa.');
    if (confirm(`Xóa ${selectedIds.value.length} chức vụ này?`)) {
        router.delete('/positions/bulk-delete', {
            data: { ids: selectedIds.value },
            preserveState: true,
            onSuccess: () => { selectedIds.value = []; }
        });
    }
};

const bulkRestoreSelected = () => {
    if (selectedIds.value.length === 0) return alert('Vui lòng chọn ít nhất 1 mục để khôi phục.');
    if (confirm(`Khôi phục ${selectedIds.value.length} chức vụ này?`)) {
        router.put('/positions/bulk-restore', { ids: selectedIds.value }, {
            preserveState: true,
            onSuccess: () => { selectedIds.value = []; }
        });
    }
};

const bulkForceDeleteSelected = () => {
    if (selectedIds.value.length === 0) return;
    if (confirm(`XÓA VĨNH VIỄN ${selectedIds.value.length} chức vụ này? Không thể hoàn tác!`)) {
        router.delete('/positions/bulk-force-delete', {
            data: { ids: selectedIds.value },
            preserveState: true,
            onSuccess: () => { selectedIds.value = []; }
        });
    }
};

// ==========================================
// LOGIC BỘ LỌC (FILTER)
// ==========================================
const filters = ref({
    search: props.filters?.search || '',
    code: props.filters?.code ? props.filters.code.split(',') : [],
    level: props.filters?.level ? props.filters.level.split(',') : [],
    is_trashed: props.is_trashed === 'true',
    // 👇 BỔ SUNG: 2 biến hứng dữ liệu lương
    min_salary: props.filters?.min_salary || '',
    max_salary: props.filters?.max_salary || ''
});

watch(filters, (newFilters) => {
    router.get('/positions', {
        search: newFilters.search,
        code: newFilters.code.join(','),    
        level: newFilters.level.join(','),    
        is_trashed: newFilters.is_trashed ? 'true' : 'false',
        
        // 👇 BỔ SUNG: Gửi 2 biến này lên URL
        min_salary: newFilters.min_salary,
        max_salary: newFilters.max_salary
    }, {
        preserveState: true, 
        preserveScroll: true, 
        replace: true
    });
}, { deep: true });

const formatOptions = (data: any, field: string, prefix = '') => {
    if (!data) return [];
    const safeArray = Array.isArray(data) ? data : Object.values(data);
    return safeArray.map((item: any) => ({ 
        label: `${prefix}${item[field]}`, value: item[field] 
    }));
};

const filterConfig = [
    { key: 'search', type: 'text' as const, placeholder: 'Tìm kiếm chức vụ...' },
    { key: 'code', type: 'multi-checkbox' as const, placeholder: 'Mã chức vụ', options: formatOptions(props.code, 'code') },
    { key: 'level', type: 'multi-checkbox' as const, placeholder: 'Cấp bậc', options: formatOptions(props.level, 'level', 'Cấp ') },
    
    // 👇 BỔ SUNG: 2 ô nhập khoảng lương
    { key: 'min_salary', type: 'text' as const, placeholder: 'Lương từ (VD: 5000000)' },
    { key: 'max_salary', type: 'text' as const, placeholder: 'Lương đến (VD: 20000000)' }
];
</script>

<template>      
    <Head title="Danh sách Chức vụ" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Quản lý Chức vụ</h1>
                    <p class="text-muted-foreground">Danh sách các chức vụ trong hệ thống.</p>
                </div>
    
                <div class="flex items-center gap-2">
                    <div v-if="selectedIds.length > 0" class="flex items-center gap-2 mr-2">
                        <Button v-if="!viewingRestore" variant="destructive" @click="bulkDeleteSelected" class="gap-2">
                            <Trash2 class="h-4 w-4" /> Xóa {{ selectedIds.length }} mục
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
                        <Link href="/positions/create">
                            <Button class="gap-2">
                                <Plus class="h-4 w-4" /> Thêm mới
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>
    
            <Transition
                enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-300" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="flashSuccess && showFlash" class="flex items-center rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-200 text-emerald-800 shadow-sm">
                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ flashSuccess }}</span>
                </div>
            </Transition>

            <Card>
                <CardHeader>
                    <CardTitle>{{ viewingRestore ? 'Thùng rác chức vụ' : 'Dữ liệu chức vụ' }}</CardTitle>
                    <CardDescription v-if="!viewingRestore">Hiển thị tổng cộng {{ positions.total }} chức vụ.</CardDescription>
                    <CardDescription v-else>Hiển thị danh sách chức vụ đã xóa mềm.</CardDescription>
                    
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
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-y">
                                <tr>
                                    <th scope="col" class="px-6 py-3 w-10">
                                        <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" class="rounded border-gray-300 text-blue-600" />
                                    </th>
                                    <th scope="col" class="px-6 py-3">Tên chức vụ</th>
                                    <th scope="col" class="px-6 py-3">Mã chức vụ</th>
                                    <th scope="col" class="px-6 py-3">Cấp bậc</th>
                                    <th scope="col" class="px-6 py-3">Mức lương</th>
                                    <th scope="col" class="px-6 py-3 text-right">Hành động</th>
                                </tr>
                            </thead>
                            <tbody v-if="!viewingRestore" class="divide-y">
                                <tr v-for="position in positions.data" :key="position.id" class="bg-white hover:bg-gray-50">
                                    <td class="px-6 py-4"><input type="checkbox" :value="position.id" v-model="selectedIds" class="rounded border-gray-300" /></td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ position.name }}</td>
                                    <td class="px-6 py-4 font-mono">{{ position.code || '-' }}</td>
                                    <td class="px-6 py-4">Cấp {{ position.level }}</td>
                                    <td class="px-6 py-4">{{ position.salary_min }} - {{ position.salary_max }}</td>
                                    <td class="px-6 py-4 text-right flex justify-end gap-2">
                                        <Link :href="`/positions/${position.id}`"><Button variant="ghost" size="icon"><Eye class="h-4 w-4" /></Button></Link>
                                        <Link :href="`/positions/${position.id}/edit`"><Button variant="ghost" size="icon"><Pencil class="h-4 w-4" /></Button></Link>
                                        <Button variant="ghost" size="icon" class="text-red-600" @click="deletePosition(position.id)"><Trash2 class="h-4 w-4" /></Button>
                                    </td>
                                </tr>
                                <tr v-if="positions?.data?.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-muted-foreground">Không tìm thấy dữ liệu.</td>
                                </tr>
                            </tbody>
                            <tbody v-else class="divide-y bg-red-50/10">
                                <tr v-for="position in restore.data" :key="position.id" class="hover:bg-muted/30 opacity-80">
                                    <td class="px-6 py-4"><input type="checkbox" :value="position.id" v-model="selectedIds" class="rounded border-gray-300" /></td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ position.name }}</td>
                                    <td class="px-6 py-4 font-mono">{{ position.code || '-' }}</td>
                                    <td class="px-6 py-4">Cấp {{ position.level }}</td>
                                    <td class="px-6 py-4">{{ position.salary_min }} - {{ position.salary_max }}</td>
                                    <td class="px-6 py-4 text-right flex justify-end gap-2">
                                        <Button variant="outline" size="sm" class="text-green-600 border-green-200" @click="restorePosition(position.id)">Khôi phục</Button>
                                        <Button variant="destructive" size="sm" @click="forceDeletePosition(position.id)"><Trash2 class="h-4 w-4" /></Button>
                                    </td>
                                </tr>
                                <tr v-if="!restore.data || restore.data.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-muted-foreground">Thùng rác trống.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
                <CardFooter v-if="!viewingRestore" class="border-t px-6 py-4">
                    <div class="flex items-center justify-between w-full">
                        <div class="text-sm text-muted-foreground">
                            Hiển thị {{ positions?.data?.length || 0 }} trên {{ positions?.total || 0 }} kết quả
                        </div>
                        <div class="flex gap-1">
                            <Link v-for="(link, index) in positions?.links" :key="index" :href="link.url || '#'"
                                :class="['px-3 py-1 rounded-md border text-sm', 
                                    link.active ? 'bg-primary text-primary-foreground border-primary' : 'bg-white border-gray-300 hover:bg-gray-50',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                ]">
                                <span v-html="link.label"></span>
                            </Link>
                        </div>
                    </div>
                </CardFooter>
            </Card>
        </div> 
    </AppLayout>
</template>