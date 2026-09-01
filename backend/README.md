# Casa Repuestos API

REST API for the Casa Repuestos multi-tenant inventory and sales platform.

## Stack

- PHP 8.3
- Laravel 12
- Laravel Sanctum
- PostgreSQL 17
- PHPUnit

## Main modules

- Authentication and password change.
- Global company administration.
- Tenant and branch access control.
- Role-based permissions.
- Categories, brands, products, suppliers, and customers.
- Purchases and receiving.
- Sales, checkout, discounts, and payments.
- Inventory and stock movements.
- Cancellations and audit logs.

## Tenant security

Tenant access and permissions are enforced by middleware and domain validation. Related records from another company are rejected by the API.

See [the permissions matrix](../docs/MATRIZ_PERMISOS.md).

## Run tests

From the repository root:

```bash
docker compose exec php php artisan test
```

See the [main project documentation](../README.md) for complete setup instructions.
