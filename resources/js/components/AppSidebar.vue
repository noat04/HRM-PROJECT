<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
// 👇 Đã thêm một số Icon liên quan đến Tiền lương (Banknote, Calculator, FileText, Landmark)
import { 
    BookOpen, Folder, LayoutGrid, Building, Users, Briefcase, 
    Shield, UserCheck, Clock, CalendarDays, Banknote, Calculator, FileText, Landmark 
} from 'lucide-vue-next';
import { computed } from 'vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';

// Các routes import
// import { dashboard } from '@/routes';
import { index as departmentsIndex } from '@/routes/departments';
import { index as usersIndex } from '@/routes/users';
import { index as positionsIndex } from '@/routes/positions';
import { index as rolesIndex } from '@/routes/roles';
import { index as employeesIndex } from '@/routes/employees';
import { index as shiftsIndex } from '@/routes/shifts';
import { index as leaveTypesIndex } from '@/routes/leaves/types';
import { index as leaveBalancesIndex } from '@/routes/leaves/balances';
import { index as leaveRequestsIndex } from '@/routes/leaves/requests';
import { index as salaryComponentsIndex } from '@/routes/salary-components';
import { index as salaryStructuresIndex } from '@/routes/salary-structures';
import { index as payrollPeriodsIndex } from '@/routes/payroll-periods';
import { index as payslipsIndex } from '@/routes/payslips';

// 1. Import hàm phân quyền
import { usePermission } from '@/composables/usePermission';
import { usePage } from '@inertiajs/vue3'; // Thêm dòng này

// 2. Khởi tạo
const { hasRole } = usePermission();

// 👇 3. Bọc mảng bằng computed() và thêm thuộc tính `show` để lọc
const mainNavItems = computed<NavItem[]>(() => {
    const items = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
            show: true, // Ai cũng xem được
        },
        // ==========================================
        // NHÓM ADMIN
        // ==========================================
        {
            title: 'Người dùng',
            href: usersIndex(), 
            icon: Users,
            show: hasRole('Super Admin'), 
        },
        {
            title: 'Vai trò',
            href: rolesIndex(), 
            icon: Shield,
            show: hasRole('Super Admin'), 
        },
        {
            title: 'Nhân viên',
            href: employeesIndex(), 
            icon: UserCheck,
            show: hasRole(['Super Admin', 'HR Manager', 'Employee']), 
        },
        // ==========================================
        // NHÓM HR / KẾ TOÁN
        // ==========================================
        {
            title: 'Phòng ban',
            href: departmentsIndex(), 
            icon: Building,
            show: hasRole(['HR Manager','Employee',]), 
        },
        {
            title: 'Chức vụ',
            href: positionsIndex(), 
            icon: Briefcase,
            show: hasRole('HR Manager'), 
        },
        {
            title: 'Ca làm việc',
            href: shiftsIndex(), 
            icon: Clock,
            show: hasRole('HR Manager'), 
        },
        {
            title: 'Loại nghỉ phép',
            href: leaveTypesIndex(), 
            icon: CalendarDays,
            show: hasRole('HR Manager'), 
        },
       {
            title: 'Số dư nghỉ phép',
            href: leaveBalancesIndex(), 
            icon: CalendarDays,
            // 👇 Mở cho cả HR và Employee
            show: hasRole(['HR Manager', 'Employee']), 
        },
        {
            title: 'Yêu cầu nghỉ phép',
            href: leaveRequestsIndex(), 
            icon: CalendarDays,
            // 👇 Mở cho cả HR và Employee
            show: hasRole(['HR Manager', 'Employee']), 
        },
        {
            title: 'Cơ cấu lương',
            href: salaryStructuresIndex(), 
            icon: Calculator, 
            // 👇 Mở cho cả HR và Employee
            show: hasRole(['HR Manager', 'Employee']), 
        },
        {
            title: 'Phiếu lương',
            href: payslipsIndex(), 
            icon: FileText, 
            // 👇 Mở cho cả HR và Employee
            show: hasRole(['HR Manager', 'Employee']), 
        },
        {
            title: 'Kỳ lương',
            href: payrollPeriodsIndex(), 
            icon: Banknote, 
            show: hasRole('HR Manager'), 
        },
        {
            title: 'Phiếu lương',
            href: payslipsIndex(), 
            icon: FileText, 
            show: hasRole('HR Manager'), 
        }
    ];

    // Lọc ra những item có thuộc tính show = true
    return items.filter(item => item.show !== false) as NavItem[];
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
console.log('Danh sách Roles của user hiện tại:', usePage().props.auth.user.roles);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>