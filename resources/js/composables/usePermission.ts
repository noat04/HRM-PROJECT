import { usePage } from '@inertiajs/vue3';

export function usePermission() {
    const page = usePage();

    const hasRole = (roles: string | string[]) => {
        const userRoles = (page.props.auth as any).roles || [];
        if (Array.isArray(roles)) {
            return roles.some((role) => userRoles.includes(role));
        }
        return userRoles.includes(roles);
    };

    const hasPermission = (permission: string) => {
        // 👇 1. SUPER ADMIN LUÔN LUÔN ĐƯỢC PASS QUA MỌI CỬA (Bypass)
        if (hasRole('Final Admin')) {
            return true;
        }

        // 👇 2. Nếu không phải Super Admin, kiểm tra quyền cụ thể trong mảng permissions
        const userPermissions = (page.props.auth as any).permissions || [];
        return userPermissions.includes(permission);
    };

    return { hasRole, hasPermission };
}