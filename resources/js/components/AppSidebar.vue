<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
// 👇 Thêm icon ChevronRight cho menu xổ xuống
import { 
    BookOpen, Folder, LayoutGrid, Building, Users, Briefcase, 
    Shield, UserCheck, Clock, CalendarDays, Banknote, Calculator, FileText, ChevronRight
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
import AppLogo from './AppLogo.vue';

// 1. Lấy hàm kiểm tra quyền và vai trò
import { usePermission } from '@/composables/usePermission';
const { hasPermission, hasRole } = usePermission();

// 2. Cấu trúc Menu dạng Nhóm (Collapsible)
const mainNavItems = computed(() => {
    const groups = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
            show: true, // Ai đăng nhập cũng thấy Dashboard
        },
        {
            title: 'Hệ thống',
            icon: Shield,
            // Chỉ hiện thư mục này nếu có ít nhất 1 trong các quyền dưới
            show: hasPermission('manager_users') || hasPermission('manager_roles') || hasPermission('manager_permissions'),
            items: [
                { title: 'Người dùng', href: '/users', show: hasPermission('manager_users') },
                { title: 'Vai trò', href: '/roles', show: hasPermission('manager_roles') },
                { title: 'Quyền hạn', href: '/permissions', show: hasPermission('manager_permissions') },
            ]
        },
        {
            title: 'Tổ chức & Nhân sự',
            icon: Users,
            show: hasPermission('manager_departments') || hasPermission('manager_positions') || hasPermission('manager_employees'),
            items: [
                { title: 'Phòng ban', href: '/departments', show: hasPermission('manager_departments') },
                { title: 'Chức vụ', href: '/positions', show: hasPermission('manager_positions') },
                { title: 'Hồ sơ nhân viên', href: '/employees', show: hasPermission('manager_employees') },
            ]
        },
        {
            title: 'Thời gian & Nghỉ phép',
            icon: CalendarDays,
            show: hasPermission('manager_shifts') || hasPermission('manager_leave_types') || hasPermission('manager_leave_balances') || hasPermission('manager_leave_requests'),
            items: [
                { title: 'Ca làm việc', href: '/shifts', show: hasPermission('manager_shifts') },
                { title: 'Loại phép', href: '/leaves/types', show: hasPermission('manager_leave_types') },
                { title: 'Số dư phép', href: '/leaves/balances', show: hasPermission('manager_leave_balances') },
                { title: 'Đơn xin nghỉ', href: '/leaves/requests', show: hasPermission('manager_leave_requests') },
            ]
        },
        {
            title: 'Tiền lương (C&B)',
            icon: Banknote,
            show: hasPermission('view_salary_structures') || hasPermission('view_payslips') || hasPermission('view_payroll_periods'),
            items: [
                { title: 'Phiếu lương', href: '/payslips', show: hasPermission('view_payslips') },
                { title: 'Kỳ lương', href: '/payroll-periods', show: hasPermission('view_payroll_periods') },
                { title: 'Cơ cấu lương', href: '/salary-structures', show: hasPermission('view_salary_structures') },
                { title: 'Thành phần lương', href: '/salary-components', show: hasPermission('view_salary_components') },
            ]
        }
    ];

    // 🔥 THUẬT TOÁN LỌC KÉP: Lọc thư mục cha -> Sau đó lọc các menu con bên trong
    return groups
        .filter(group => group.show !== false)
        .map(group => {
            // Nếu có sub-menu (items) thì lọc tiếp những item có quyền xem
            if (group.items) {
                return {
                    ...group,
                    items: group.items.filter(item => item.show !== false)
                };
            }
            return group;
        });
});

const footerNavItems = [
    { title: 'Github Repo', href: 'https://github.com/laravel/vue-starter-kit', icon: Folder },
    { title: 'Documentation', href: 'https://laravel.com/docs/starter-kits#vue', icon: BookOpen },
];
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