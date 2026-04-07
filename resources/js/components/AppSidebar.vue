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
            show: hasPermission('system_manage_users') || hasPermission('system_manage_roles') || hasPermission('system_manage_permission'),
            items: [
                { title: 'Người dùng', href: '/users', show: hasPermission('system_manage_users') },
                { title: 'Vai trò', href: '/roles', show: hasPermission('system_manage_roles') },
                { title: 'Quyền hạn', href: '/permissions', show: hasPermission('system_manage_permission') },
            ]
        },
        {
            title: 'Tổ chức & Nhân sự',
            icon: Users,
            show: hasPermission('department_manage') || hasPermission('position_manage') || hasPermission('employee_view_all'),
            items: [
                { title: 'Phòng ban', href: '/departments', show: hasPermission('department_manage') },
                { title: 'Chức vụ', href: '/positions', show: hasPermission('position_manage') },
                { title: 'Hồ sơ nhân viên', href: '/employees', show: hasPermission('employee_view_all') },
            ]
        },
        {
            title: 'Thời gian & Nghỉ phép',
            icon: CalendarDays,
            show: hasPermission('shift_manage') || hasPermission('leave_type_manage') || hasPermission('leave_balance_adjust') || hasPermission('attendance_manage'),
            items: [
                { title: 'Ca làm việc', href: '/shifts', show: hasPermission('shift_manage') },
                { title: 'Loại phép', href: '/leaves/types', show: hasPermission('leave_type_manage') },
                { title: 'Số dư phép', href: '/leaves/balances', show: hasPermission('leave_balance_adjust') },
                // { title: 'Đơn xin nghỉ', href: '/leaves/requests', show: hasPermission('manager_leave_requests') },
            ]
        },
        {
            title: 'Tiền lương (C&B)',
            icon: Banknote,
            show: hasPermission('salary_component_manage') || hasPermission('salary_structure_view') || hasPermission('payroll_period_manage') || hasPermission('payslip_view_all'),
            items: [
                { title: 'Phiếu lương', href: '/payslips', show: hasPermission('payslip_view_all') },
                { title: 'Kỳ lương', href: '/payroll-periods', show: hasPermission('payroll_period_manage') },
                { title: 'Cơ cấu lương', href: '/salary-structures', show: hasPermission('salary_structure_view') },
                { title: 'Thành phần lương', href: '/salary-components', show: hasPermission('salary_component_manage') },
            ]
        },
        {
            title: 'Quản lý nhóm',
            icon: Users,
            show: hasPermission('employee_view_team'),
            items: [
                { title: 'Nhóm', href: '/groups', show: hasPermission('employee_view_team') },
            ]
        },
        {
            title: 'Phê duyệt nghỉ phép',
            icon: Users,
            show: hasPermission('leave_request_approve'),
            items: [
                { title: 'Phê duyệt nghỉ phép', href: '/leave-requests', show: hasPermission('leave_request_approve') },
            ]
        },
        {
            title: 'Quản lý chấm công nhóm',
            icon: Users,
            show: hasPermission('attendance_approve_ot') || hasPermission('attendance_view_team') ,
            items: [
                { title: 'Phê duyệt OT', href: '/leave-requests', show: hasPermission('attendance_approve_ot') },
                { title: 'Báo cáo đi muộn/về sớm', href: '/attendance', show: hasPermission('attendance_view_team') },
            ]
        },
        {
            title: 'Hồ sơ cá nhân',
            icon: Users,
            show: hasPermission('profile_view_own') ,
            href: '/profile'
        },
        {
            title: 'Chấm công',
            icon: Users,
            show: hasPermission('attendance_check_in') ,
            href: '/attendance'
        },
        {
            title: 'Bảng Lương',
            icon: Users,
            show: hasPermission('payslip_view_own') ,
            href: '/payslips'
        },

        {
            title: 'Nghỉ phép',
            icon: Users,
            show: hasPermission('leave_request_create') || hasPermission('leave_balance_view_own') ,
            items: [
                { title: 'Đăng ký nghỉ phép', href: '/leave-requests/create', show: hasPermission('leave_request_create') },
                { title: 'Số dư phép', href: '/leave-balances', show: hasPermission('leave_balance_view_own') },
            ]
        },
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