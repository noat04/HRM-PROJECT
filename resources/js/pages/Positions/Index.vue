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
    filters?: { search?: string };
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    positions: PaginatedPositions;
    processing?: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Chức vụ', href: '/positions' },
];

const search = ref(props.positions?.filters?.search || '');

let searchTimeout: ReturnType<typeof setTimeout>;

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/positions', 
            { search: value },
            { 
                preserveState: true,
                replace: true
            }
        );
    }, 300);
});

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

const deletePosition = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn xóa chức vụ này không?')) {
        router.delete(`/positions/${id}`);
    }
};
</script>

<template>      
    <Head title="Danh sách Chức vụ" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Quản lý Chức vụ</h1>
                    <p class="text-muted-foreground">Danh sách các chức vụ trong hệ thống.</p>
                </div>
    
                <div class="flex items-center gap-4">
                        <div class="relative w-64">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input 
                                v-model="search" 
                                type="text" 
                                placeholder="Tìm kiếm tên chức vụ..." 
                                class="pl-8 bg-white" 
                            />
                        </div>

                        <Link href="/positions/create">
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
                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ flashSuccess }}</span>
                </div>
            </Transition>

            <Card>
                <CardHeader>
                    <CardTitle>Danh sách Chức vụ</CardTitle>
                    <CardDescription>
                        Hiển thị tổng cộng {{ positions.total }} chức vụ.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Tên chức vụ</th>
                                    <th scope="col" class="px-6 py-3">Mã chức vụ</th>
                                    <th scope="col" class="px-6 py-3">Cấp bậc</th>
                                    <th scope="col" class="px-6 py-3">Mức lương</th>
                                    <th scope="col" class="px-6 py-3">Mô tả</th>
                                    <th scope="col" class="px-6 py-3 text-right">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="position in positions.data" :key="position.id" class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ position.name }}</td>
                                    <td class="px-6 py-4">{{ position.code || '-' }}</td>
                                    <td class="px-6 py-4">{{ position.level }}</td>
                                    <td class="px-6 py-4">{{ position.salary_min }} - {{ position.salary_max }}</td>
                                    <td class="px-6 py-4">{{ position.description || '-' }}</td>
                                    <td class="px-6 py-4 text-right flex justify-end gap-2">
                                        <Link :href="`/positions/${position.id}`">
                                            <Button variant="ghost" size="icon" title="Xem chi tiết">
                                                <Eye class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Link :href="`/positions/${position.id}/edit`">
                                            <Button variant="ghost" size="icon" title="Chỉnh sửa">
                                                <Pencil class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Button variant="ghost" size="icon" title="Xóa" @click="deletePosition(position.id)">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="positions.data.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center text-muted-foreground">Không tìm thấy chức vụ nào.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
                <CardFooter>
                    <div class="flex items-center justify-between w-full">
                        <div class="text-sm text-muted-foreground">
                            Hiển thị {{ positions.data.length }} trên {{ positions.total }} kết quả
                        </div>
                        <div class="flex gap-2">
                            <Link 
                                v-for="link in positions.links" 
                                :key="link.label" 
                                :href="link.url || '#'"
                                :class="['px-4 py-2 rounded-md border text-sm', 
                                    link.active ? 'bg-primary text-primary-foreground border-primary' : 'bg-white border-gray-300 hover:bg-gray-50',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                            >
                                <span v-html="link.label"></span>
                            </Link>
                        </div>
                    </div>
                </CardFooter>
            </Card>
            
        </div> </AppLayout>
</template>