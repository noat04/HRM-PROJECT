<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Building } from 'lucide-vue-next';
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
import { dashboard } from '@/routes';
// 2. Import index từ thư mục departments và đổi tên thành departmentsIndex
import { index as departmentsIndex } from '@/routes/departments';
import { index as usersIndex } from '@/routes/users';
import { index as positionsIndex } from '@/routes/positions';
import { index as rolesIndex } from '@/routes/roles';
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    // 👇 3. BỔ SUNG MENU PHÒNG BAN VÀO ĐÂY
    {
        title: 'Phòng ban',
        href: departmentsIndex(), 
        icon: Building,
    },
     {
        title: 'Người dùng',
        href: usersIndex(), 
        icon: Building,
    },
    {
        title: 'Chức vụ',
        href: positionsIndex(), 
        icon: Building,
    },
    {
        title: 'Vai trò',
        href: rolesIndex(), 
        icon: Building,
    },

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
