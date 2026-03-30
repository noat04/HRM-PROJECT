<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'; 
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Pencil, Trash2, Eye, Plus } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    salaryComponents: any; // Thay bằng Interface Pagination của bạn
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Trang chủ', href: '/dashboard' },
    { title: 'Thành phần lương', href: '/salary-components' },
];

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const showFlash = ref(false);

watch(flashSuccess, (newMessage) => {
    if (newMessage) {
        showFlash.value = true;
        setTimeout(() => showFlash.value = false, 3000);
    }
}, { immediate: true });

const deleteItem = (id: number) => {
    if (confirm('Bạn có chắc chắn muốn xóa thành phần lương này?')) {
        router.delete(`/salary-components/${id}`);
    }
};
</script>

<template>
    <Head title="Thành phần lương" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-7xl mx-auto w-full">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Thành phần lương</h1>
                    <p class="text-muted-foreground">Quản lý các khoản thu nhập và khấu trừ.</p>
                </div>
                <Link href="/salary-components/create">
                    <Button><Plus class="mr-2 h-4 w-4" /> Thêm mới</Button>
                </Link>
            </div>

            <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-300" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                <div v-if="flashSuccess && showFlash" class="flex items-center rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-200 text-emerald-800 shadow-sm">
                    <span class="font-medium">{{ flashSuccess }}</span>
                </div>
            </Transition>

            <Card>
                <CardHeader>
                    <CardTitle>Danh sách Thành phần lương</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4 font-medium">Tên</th>
                                    <th class="text-left py-3 px-4 font-medium">Mã</th>
                                    <th class="text-left py-3 px-4 font-medium">Loại</th>
                                    <th class="text-left py-3 px-4 font-medium">Hoạt động</th>
                                    <th class="text-right py-3 px-4 font-medium">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in salaryComponents?.data" :key="item.id" class="border-b hover:bg-muted/50">
                                    <td class="py-3 px-4 font-semibold text-primary">{{ item.name }}</td>
                                    <td class="py-3 px-4">{{ item.code }}</td>
                                    <td class="py-3 px-4">
                                        <span :class="item.type === 'earning' ? 'text-emerald-600' : 'text-red-600'">
                                            {{ item.type === 'earning' ? 'Thu nhập' : 'Khấu trừ' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span v-if="item.is_active" class="text-emerald-600">Có</span>
                                        <span v-else class="text-red-600">Không</span>
                                    </td>
                                    <td class="py-3 px-4 text-right flex justify-end gap-2">
                                        <Link :href="`/salary-components/${item.id}`"><Button variant="ghost" size="icon"><Eye class="h-4 w-4" /></Button></Link>
                                        <Link :href="`/salary-components/${item.id}/edit`"><Button variant="ghost" size="icon"><Pencil class="h-4 w-4" /></Button></Link>
                                        <Button variant="ghost" size="icon" class="text-destructive" @click="deleteItem(item.id)"><Trash2 class="h-4 w-4" /></Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>