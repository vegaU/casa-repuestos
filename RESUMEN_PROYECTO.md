# Resumen del proyecto Casa Repuestos

Casa Repuestos es una plataforma full stack y multitenant para administrar empresas de repuestos, sucursales, inventario, compras y ventas.

La documentación principal y las instrucciones actualizadas se encuentran en el [README](README.md).

## Estado actual

El proyecto ya incluye:

- Administración global de empresas.
- Sucursales, usuarios y permisos por rol.
- Autenticación mediante Laravel Sanctum.
- Catálogos de categorías, marcas, productos, proveedores y clientes.
- Inventario y movimientos de stock por sucursal.
- Compras con múltiples productos y actualización de existencias.
- Checkout transaccional de ventas.
- Descuentos, pagos, efectivo recibido y cálculo de vuelto.
- Cancelación de compras y ventas.
- Auditoría de operaciones críticas.
- Pruebas de aislamiento multitenant y checkout.
- Integración continua para backend y frontend.

## Tecnologías

- Laravel 12 y PHP 8.3.
- React 19 y TypeScript.
- PostgreSQL 17.
- Docker, PHP-FPM y Nginx.
- TanStack Query, React Hook Form y Zod.
- PHPUnit, Oxlint y GitHub Actions.

## Pendientes principales

- Ampliar la cobertura automatizada.
- Incorporar documentación OpenAPI.
- Preparar despliegue y datos de demostración.
- Agregar facturación y futura integración con e-Kuatia.
- Publicar capturas y demostración en línea.
