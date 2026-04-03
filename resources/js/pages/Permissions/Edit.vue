<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ArrowLeft, Save } from 'lucide-vue-next';

const props = defineProps<{
    permission: {
        id: number;
        name: string;
        group?: string;
    }
}>();

const form = useForm({
    name: props.permission.name,
    group: props.permission.group,
});

const submit = () => {
    form.put(`/permissions/${props.permission.id}`);
};
</script>
<template>
    <Head title="Chỉnh sửa Quyền" />
    <AppLayout>
        <div class="space-y-6 p-6 max-w-2xl mx-auto w-full">
            <div class="flex items-center gap-4">
                <Link href="/permissions"><Button variant="outline" size="icon"><ArrowLeft class="h-4 w-4" /></Button></Link>
                <h1 class="text-2xl font-bold tracking-tight">Chỉnh sửa Quyền: {{ form.name }}</h1>
            </div>

            <Card>
                <CardContent class="pt-6">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <Label for="name">Mã Quyền (Không thể thay đổi)</Label>
                            <Input id="name" v-model="form.name" disabled class="mt-1 font-mono bg-gray-50" />
                        </div>
                         <div>
                            <Label for="group">Nhóm Quyền</Label>
                            <select id="group" v-model="form.group" class="mt-1 font-mono bg-gray-50">
                                <!-- <option value="" :selected="form.group === ''">Chọn nhóm quyền</option> -->
                                <option value="user" :selected="form.group === 'user'">Người dùng</option>
                                <option value="admin" :selected="form.group === 'admin'">Quản trị viên</option>
                                <option value="manager" :selected="form.group === 'manager'">Quản lý</option>
                                <option value="staff" :selected="form.group === 'staff'">Nhân viên</option>
                            </select>
                        </div>
                        <div class="flex justify-end gap-2 pt-4">
                            <Link href="/permissions"><Button type="button" variant="outline">Hủy</Button></Link>
                            <Button type="submit" :disabled="form.processing"><Save class="h-4 w-4 mr-2" /> Lưu Thay Đổi</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>