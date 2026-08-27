# Matriz de permisos

La autorización se valida en el backend mediante el middleware `tenant.permission`.
El superadministrador conserva acceso global y la asociación inactiva a una empresa
siempre bloquea el acceso.

| Rol | Alcance |
| --- | --- |
| `tenant_admin` | Administración completa de su empresa. |
| `manager` | Catálogos, clientes, ventas, pagos, compras, inventario y reportes. |
| `seller` | Consultas de catálogos e inventario; clientes, ventas y pagos. |
| `warehouse` | Catálogos, compras e inventario. |
| `viewer` | Consultas de catálogos, ventas, compras, inventario y reportes. |

`owner` se mantiene temporalmente como alias legado de `tenant_admin` para los
datos iniciales existentes. Las asignaciones nuevas deben usar `tenant_admin`.
