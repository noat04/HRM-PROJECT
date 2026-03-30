import { usePage } from '@inertiajs/vue3';

export function usePermission() {
    const hasRole = (role: string | string[]) => {
        // Lấy dữ liệu roles từ backend gửi sang (Mặc định là mảng rỗng nếu không có)
        const rawRoles = (usePage().props.auth as any)?.roles || [];
        
        // BƯỚC XỬ LÝ THÔNG MINH: 
        // Ép tất cả về dạng mảng chuỗi (String Array), bất kể backend gửi Object hay String
        const userRoles = rawRoles.map((r: any) => typeof r === 'string' ? r : r.name);

        // Kiểm tra
        if (Array.isArray(role)) {
            return role.some(r => userRoles.includes(r));
        }
        return userRoles.includes(role);
    };

    const hasPermission = (permission: string | string[]) => {
        const rawPermissions = (usePage().props.auth as any)?.permissions || [];
        const userPermissions = rawPermissions.map((p: any) => typeof p === 'string' ? p : p.name);

        if (Array.isArray(permission)) {
            return permission.some(p => userPermissions.includes(p));
        }
        return userPermissions.includes(permission);
    };

    return { hasRole, hasPermission };
}