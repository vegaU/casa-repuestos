# Casa Repuestos Web

React frontend for the Casa Repuestos multi-tenant inventory and sales platform.

## Stack

- React 19
- TypeScript
- Vite
- Tailwind CSS
- TanStack Query
- React Hook Form
- Zod
- Axios

## Application areas

- Authentication and forced password change.
- Company selection and global administration.
- Dashboard.
- Products and catalogs.
- Customers and suppliers.
- Inventory.
- Purchases.
- Sales checkout and sales history.

The frontend is organized by business feature under `src/features` and communicates with the Laravel API through a shared Axios client.

## Development

```bash
corepack enable
pnpm install
cp .env.example .env
pnpm dev
```

## Quality checks

```bash
pnpm lint
pnpm typecheck
pnpm build
```

See the [main project documentation](../README.md) for the complete environment setup.
