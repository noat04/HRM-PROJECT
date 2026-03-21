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

// Nhận dữ liệu từ Controller
const props = defineProps<{
    roles: Array<{
        id: number;
        name: string;
        display_name: string;
        description: string;
        permissions: Array<{
            name: string;
        }>;
    }>;
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
                <Link href="/roles/create">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Tạo Vai trò mới
                    </Button>
                </Link>
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
                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ flashSuccess }}</span>
                </div>
            </Transition>

            <Card>
                <CardHeader>
                    <CardTitle>Danh sách Vai trò</CardTitle>
                    <CardDescription>
                        Tổng số vai trò: {{ roles.length }}
                    </CardDescription>
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
                            <tbody>
                                <tr v-for="role in roles" :key="role.id" class="border-b hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-primary">{{ role.display_name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ role.name }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ role.description || 'N/A' }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex flex-wrap gap-1">
                                            <Badge v-for="perm in role.permissions" :key="perm.name" variant="secondary">
                                                {{ perm.name }}
                                            </Badge>
                                            <span v-if="role.permissions.length === 0" class="text-xs text-muted-foreground">Chưa có quyền</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="`/roles/${role.id}`">
                                                <Button variant="ghost" size="icon">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="`/roles/${role.id}/edit`">
                                                <Button variant="ghost" size="icon">
                                                    <Edit class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button variant="ghost" size="icon" @click="deleteRole(role.id)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
    </AppLayout>
</template>