<?php

namespace App\Support;

class TenantPermissions
{
    private const MATRIX = [
        'tenant_admin' => ['*'],
        'owner' => ['*'],
        'manager' => ['catalog.view','catalog.manage','sales.view','sales.manage','payments.manage','customers.manage','purchases.view','purchases.manage','inventory.view','inventory.manage','reports.view'],
        'seller' => ['sales.view','sales.manage','payments.manage','customers.manage','catalog.view','inventory.view'],
        'warehouse' => ['catalog.view','catalog.manage','purchases.view','purchases.manage','inventory.view','inventory.manage'],
        'viewer' => ['catalog.view','sales.view','purchases.view','inventory.view','reports.view'],
    ];

    public static function allows(string $role, string $permission): bool
    {
        $permissions = self::MATRIX[$role] ?? [];
        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }

    public static function matrix(): array
    {
        return self::MATRIX;
    }
}
