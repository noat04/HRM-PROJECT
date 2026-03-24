<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { ArrowLeft } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import { computed } from 'vue';
import EmployeeStatus from '@/enums/EmployeeStatus';


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

interface Department {
    id: number;
    name: string;
}

interface Position {
    id: number;
    name: string;
}
    
interface User {
    id: number;
    name: string;
}

const props = defineProps<{
    employee: Employee;
    employees: Employee[];
    departments: Department[];
    positions: Position[];
    users: User[];
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Quản lý nhân viên', href: '/employees' },
    { title: 'Chỉnh sửa', href: '/employees/edit' },
]);
const formatDateForInput = (dateString: string | null) => {
    if (!dateString) return null;
    // Cắt bỏ phần chữ 'T' (nếu là chuẩn ISO) hoặc khoảng trắng (nếu là chuẩn SQL datetime)
    return dateString.split('T')[0].split(' ')[0]; 
};

const form = useForm({
    full_name: props.employee.full_name,
    employee_code: props.employee.employee_code,
    gender: props.employee.gender,
    birthday: formatDateForInput(props.employee.birthday),
    address: props.employee.address,
    join_date: formatDateForInput(props.employee.join_date),
    resignation_date: formatDateForInput(props.employee.resignation_date),
    bank_account_number: props.employee.bank_account_number,
    bank_name: props.employee.bank_name,
    status: props.employee.status,
    department_id: props.employee.department_id,
    position_id: props.employee.position_id,
    manager_id: props.employee.manager_id,
    user_id: props.employee.user_id,
});
console.log(form);
const submitForm = () => {
    form.put(`/employees/${props.employee.id}`);
};


</script>
<template>
      <Head :title="`Sửa - ${employee.full_name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
       <div class="space-y-6 p-6 max-w-3xl mx-auto w-full">
            <div class="flex items-center gap-4">
                <Link href="/employees">
                    <Button variant="outline" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Chỉnh sửa nhân viên</h1>
                    <p class="text-muted-foreground">Vui lòng điền thông tin nhân viên mới.</p>
                </div>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Thông tin nhân viên</CardTitle>
                <CardDescription>Vui lòng điền thông tin nhân viên mới.</CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submitForm" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Họ và tên <span class="text-destructive">*</span></label>
                        <input 
                            v-model="form.full_name" 
                            type="text" 
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                            placeholder="Ví dụ: Nguyễn Văn A"
                            required 
                        />
                        <p v-if="form.errors.full_name" class="text-sm text-destructive">{{ form.errors.full_name }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Mã nhân viên <span class="text-destructive">*</span></label>
                        <input 
                            v-model="form.employee_code" 
                            type="text" 
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                            placeholder="Ví dụ: NV001"
                            required 
                        />
                        <p v-if="form.errors.employee_code" class="text-sm text-destructive">{{ form.errors.employee_code }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Giới tính <span class="text-destructive">*</span></label>
                        <select 
                            v-model="form.gender" 
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                            required 
                        >
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                        </select>
                        <p v-if="form.errors.gender" class="text-sm text-destructive">{{ form.errors.gender }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Ngày sinh <span class="text-destructive">*</span></label>
                        <input 
                            v-model="form.birthday" 
                            type="date" 
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                            required 
                        />
                        <p v-if="form.errors.birthday" class="text-sm text-destructive">{{ form.errors.birthday }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Địa chỉ <span class="text-destructive">*</span></label>
                        <input 
                            v-model="form.address" 
                            type="text" 
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                            placeholder="Ví dụ: Hà Nội"
                            required 
                        />
                        <p v-if="form.errors.address" class="text-sm text-destructive">{{ form.errors.address }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Ngày bắt đầu làm việc <span class="text-destructive">*</span></label>
                        <input 
                            v-model="form.join_date" 
                            type="date" 
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                            required 
                        />
                        <p v-if="form.errors.join_date" class="text-sm text-destructive">{{ form.errors.join_date }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Ngày nghỉ việc</label>
                        <input 
                            v-model="form.resignation_date" 
                            type="date" 
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                        />
                        <p v-if="form.errors.resignation_date" class="text-sm text-destructive">{{ form.errors.resignation_date }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Số tài khoản ngân hàng</label>
                        <input 
                            v-model="form.bank_account_number" 
                            type="text" 
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                            placeholder="Ví dụ: 123456789"
                        />
                        <p v-if="form.errors.bank_account_number" class="text-sm text-destructive">{{ form.errors.bank_account_number }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Tên ngân hàng</label>
                        <input 
                            v-model="form.bank_name" 
                            type="text" 
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                            placeholder="Ví dụ: Vietcombank"
                        />
                        <p v-if="form.errors.bank_name" class="text-sm text-destructive">{{ form.errors.bank_name }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Trạng thái <span class="text-destructive">*</span></label>
                        <select 
                            v-model="form.status" 
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                            required 
                        >
                            <option v-for="option in EmployeeStatus.options()" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.status" class="text-sm text-destructive">{{ form.errors.status }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Phòng ban</label>
                        <select 
                            v-model="form.department_id" 
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                        >
                            <option :value="null">Chọn phòng ban</option>
                            <option v-for="department in departments" :key="department.id" :value="department.id">
                                {{ department.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.department_id" class="text-sm text-destructive">{{ form.errors.department_id }}</p>
                    </div>
                   <div class="space-y-2">
                        <label class="text-sm font-medium">Chức vụ</label>
                        <select 
                            v-model="form.position_id" 
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                        >
                            <option :value="null">Chọn chức vụ</option>
                            <option v-for="position in positions" :key="position.id" :value="position.id">
                                {{ position.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.position_id" class="text-sm text-destructive">{{ form.errors.position_id }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Người quản lý</label>
                        <select 
                            v-model="form.manager_id" 
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                        >
                            <option :value="null">Chọn người quản lý</option>
                            <option v-for="employee in employees" :key="employee.id" :value="employee.id">
                                {{ employee.full_name }}
                            </option>
                        </select>
                        <p v-if="form.errors.manager_id" class="text-sm text-destructive">{{ form.errors.manager_id }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Tài khoản người dùng</label>
                        <select 
                            v-model="form.user_id" 
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                        >
                            <option :value="null">Chọn tài khoản người dùng</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.user_id" class="text-sm text-destructive">{{ form.errors.user_id }}</p>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <Link href="/employees">
                            <Button type="button" variant="outline">Hủy</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />
                            Lưu thay đổi
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </AppLayout>
</template>