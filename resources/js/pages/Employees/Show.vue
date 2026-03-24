<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Edit, Trash2 } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface Department {
    id: number;
    name: string;
}

interface Position {
    id: number;
    name: string;
}

interface Manager {
    id: number;
    full_name: string;
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
    department?: Department;
    position?: Position;
    manager?: Manager;
}

const props = defineProps<{
    employee: Employee;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Quản lý nhân viên', href: '/employees' },
    { title: 'Thông tin chi tiết', href: '/employees/show' },
];
const formatDateForInput = (dateString: string | null) => {
    if (!dateString) return null;
    // Cắt bỏ phần chữ 'T' (nếu là chuẩn ISO) hoặc khoảng trắng (nếu là chuẩn SQL datetime)
    return dateString.split('T')[0].split(' ')[0]; 
};

</script>
<template>
    <Head :title="`Thông tin chi tiết - ${employee.full_name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
         <div class="space-y-6 p-6 max-w-3xl mx-auto w-full">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Link href="/employees">
                        <Button variant="outline" size="icon">
                            <ArrowLeft class="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ employee.full_name }}</h1>
                        <p class="text-muted-foreground">Thông tin chi tiết về nhân viên.</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Link :href="`/employees/${employee.id}/edit`">
                        <Button variant="outline">
                            <Edit class="mr-2 h-4 w-4" />
                            Chỉnh sửa
                        </Button>
                    </Link>
                    <form @submit.prevent="$emit('delete',employee.id)">
                        <Button variant="destructive">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Xóa
                        </Button>
                    </form>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Thông tin cơ bản</CardTitle>
                    <CardDescription>Xem chi tiết thông tin chức vụ.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-sm font-medium text-muted-foreground">Họ và tên</span>
                            <p class="text-lg font-semibold">{{ employee.full_name }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-sm font-medium text-muted-foreground">Mã nhân viên</span>
                            <p class="text-lg">{{ employee.employee_code }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-sm font-medium text-muted-foreground">Giới tính</span>
                            <p class="text-lg">
                                <Badge variant="secondary">{{ employee.gender === 'male' ? 'Nam' : 'Nữ' }}</Badge>
                            </p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-sm font-medium text-muted-foreground">Ngày sinh</span>
                            <p class="text-lg">{{ formatDateForInput(employee.birthday) }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-sm font-medium text-muted-foreground">Địa chỉ</span>
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ employee.address }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-sm font-medium text-muted-foreground">Ngày bắt đầu làm việc</span>
                            <p class="text-lg">{{ formatDateForInput(employee.join_date) }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-sm font-medium text-muted-foreground">Ngày nghỉ việc</span>
                            <p class="text-lg">
                                {{ employee.resignation_date ? formatDateForInput(employee.resignation_date) : 'Chưa nghỉ việc' }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-sm font-medium text-muted-foreground">Trạng thái</span>
                            <p class="text-lg">
                                <Badge :variant="employee.status === 'active' ? 'default' : 'secondary'">
                                    {{ employee.status === 'active' ? 'Đang làm việc' : 'Đã nghỉ việc' }}
                                </Badge>
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-sm font-medium text-muted-foreground">Số tài khoản ngân hàng</span>
                            <p class="text-lg">{{ employee.bank_account_number || 'Không có' }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-sm font-medium text-muted-foreground">Tên ngân hàng</span>
                            <p class="text-lg">{{ employee.bank_name || 'Không có' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-sm font-medium text-muted-foreground">Phòng ban</span>
                            <p class="text-lg">{{ employee.department?.name || 'Không có' }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-sm font-medium text-muted-foreground">Chức vụ</span>
                            <p class="text-lg">{{ employee.position?.name || 'Không có' }}</p>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <span class="text-sm font-medium text-muted-foreground">Người quản lý trực tiếp</span>
                        <p class="text-lg">{{ employee.manager?.full_name || 'Không có' }}</p>
                    </div>
                </CardContent>
            </Card>
        </div>
       

    </AppLayout>
</template>
