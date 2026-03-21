<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    role: {
        id: number;
        name: string;
        display_name: string;
        description: string;
        permissions: Array<{ id: number; name: string }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Vai trò', href: '/roles' },
    { title: props.role.name, href: `/roles/${props.role.id}` },
];
</script>

<template>
    <Head :title="`Xem vai trò: ${role.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6 max-w-4xl mx-auto w-full">
            <div class="flex items-center gap-4">
                <Link href="/roles">
                    <Button variant="outline" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ role.display_name }}</h1>
                    <p class="text-muted-foreground">Thông tin chi tiết về vai trò.</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Thông tin cơ bản</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm font-medium text-muted-foreground">Tên vai trò</span>
                            <p class="text-lg font-semibold">{{ role.name }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-muted-foreground">Hiển thị</span>
                            <p class="text-lg">{{ role.display_name }}</p>
                        </div>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-muted-foreground">Mô tả</span>
                        <p class="text-lg">{{ role.description || 'Không có mô tả' }}</p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Danh sách quyền hạn</CardTitle>
                    <CardDescription>Tổng cộng {{ role.permissions.length }} quyền hạn.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <Badge v-for="perm in role.permissions" :key="perm.id" variant="secondary">
                            {{ perm.name }}
                        </Badge>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>