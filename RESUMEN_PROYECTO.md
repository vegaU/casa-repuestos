# Resumen del proyecto Casa Repuestos

## Estructura

```text
casa-repuestos/
├── backend/          Laravel 12 + API
├── frontend/         React + Vite + pnpm
├── docker/nginx/     Configuración del servidor web
├── docker/php/       Imagen PHP 8.3
└── docker-compose.yml
```

## Infraestructura

Docker ejecuta PostgreSQL 17, PHP 8.3-FPM y Nginx.

- Aplicación Laravel: http://localhost:8080
- PostgreSQL externo: `127.0.0.1:5433`
- Base: `casa_repuestos`
- Usuario: `casa_repuestos`

Para iniciar los servicios:

```powershell
docker compose up -d --build
```

## Backend

El backend usa Laravel 12 y Sanctum para autenticación por token.

- Superusuario: `rango@admin.com`
- Arquitectura multitenant: empresas, sucursales y roles por empresa.
- Empresa inicial: Casa Dominguez, RUC `123456`.
- Catálogos: categorías, marcas, proveedores y productos.
- Inventario por sucursal y movimientos de stock.
- Compras: al recibirlas, aumentan el stock.
- Ventas: al completarlas, descuentan el stock y evitan stock negativo.
- Pagos parciales y saldo pendiente en ventas.

Rutas principales de API:

```text
POST /api/login
POST /api/logout
GET  /api/me
GET  /api/tenants
GET  /api/tenants/{tenant}/inventory
GET  /api/tenants/{tenant}/stock-movements
```

## Frontend

El frontend está creado con React, Vite y pnpm.

```powershell
cd frontend
corepack pnpm dev
```

Vite normalmente se abre en http://localhost:5173.

## Pendientes

- Completar CRUD de clientes, proveedores y productos.
- Añadir pruebas automatizadas y documentación detallada de API.
- Preparar el módulo de facturación electrónica e-Kuatia.
- Crear la interfaz del panel React y conectarla a la API.

## GitHub

Repositorio: https://github.com/vegaU/casa-repuestos

Commit inicial: `4b4113d feat: inicializar backend multitenant e inventario`
