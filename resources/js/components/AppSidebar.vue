<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
// 👇 Đã import thêm các Icon cho phù hợp với từng Menu
import { BookOpen, Folder, LayoutGrid, Building, Users, Briefcase, Shield, UserCheck, Clock, CalendarDays } from 'lucide-vue-next';
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
import { dashboard } from '@/routes';
import { index as departmentsIndex } from '@/routes/departments';
import { index as usersIndex } from '@/routes/users';
import { index as positionsIndex } from '@/routes/positions';
import { index as rolesIndex } from '@/routes/roles';
import { index as employeesIndex } from '@/routes/employees';
import { index as shiftsIndex } from '@/routes/shifts';
import { index as leaveTypesIndex } from '@/routes/leaves/types';
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Phòng ban',
        href: departmentsIndex(), 
        icon: Building,
    },
     {
        title: 'Người dùng',
        href: usersIndex(), 
        icon: Users, // 👈 Đổi thành icon Users
    },
    {
        title: 'Chức vụ',
        href: positionsIndex(), 
        icon: Briefcase, // 👈 Đổi thành icon Cặp xách
    },
    {
        title: 'Vai trò',
        href: rolesIndex(), 
        icon: Shield, // 👈 Đổi thành icon Cái khiên
    },
    {
        title: 'Nhân viên',
        href: employeesIndex(), 
        icon: UserCheck, // 👈 Đổi thành icon Nhân sự
    },
    {
        title: 'Ca làm việc',
        href: shiftsIndex(), 
        icon: Clock, // 👈 Đổi thành icon Đồng hồ
    },
    // 👇 ĐÃ SỬA LỖI DỞ DANG Ở ĐÂY: Thêm menu Loại nghỉ phép
   {
        title: 'Nghỉ phép',
        href: leaveTypesIndex(), 
        icon: CalendarDays, 
    }
];

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
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
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