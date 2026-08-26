import { useQueries } from '@tanstack/react-query'
import { Boxes, Package, Truck, UsersRound } from 'lucide-react'
import { http } from '@/lib/http'
import { useTenant } from '@/features/tenants/TenantProvider'

type Paginated = { data: { total: number } }
type Inventory = { data: unknown[] }

export function DashboardPage() {
  const { tenant, branch } = useTenant()
  const [products, customers, suppliers, inventory] = useQueries({
    queries: [
      { queryKey: ['dashboard', 'products', tenant?.id], queryFn: async () => (await http.get<Paginated>(`/tenants/${tenant!.id}/products`)).data.data.total, enabled: Boolean(tenant) },
      { queryKey: ['dashboard', 'customers', tenant?.id], queryFn: async () => (await http.get<Paginated>(`/tenants/${tenant!.id}/customers`)).data.data.total, enabled: Boolean(tenant) },
      { queryKey: ['dashboard', 'suppliers', tenant?.id], queryFn: async () => (await http.get<Paginated>(`/tenants/${tenant!.id}/suppliers`)).data.data.total, enabled: Boolean(tenant) },
      { queryKey: ['dashboard', 'inventory', tenant?.id, branch?.id], queryFn: async () => (await http.get<Inventory>(`/tenants/${tenant!.id}/inventory`, { params: { branch_id: branch?.id } })).data.data.length, enabled: Boolean(tenant && branch) },
    ],
  })
  const cards = [
    { label: 'Productos', value: products.data, icon: Package },
    { label: 'Clientes', value: customers.data, icon: UsersRound },
    { label: 'Proveedores', value: suppliers.data, icon: Truck },
    { label: 'Productos con existencia', value: inventory.data, icon: Boxes },
  ]

  return <section className="space-y-6">
    <div><p className="text-sm text-slate-500">{tenant?.name ?? 'Cargando empresa…'}{branch ? ` · ${branch.name}` : ''}</p><h1 className="mt-1 text-2xl font-bold">Panel de administración</h1></div>
    <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">{cards.map(({ label, value, icon: Icon }) => <article key={label} className="rounded-lg border bg-white p-5 shadow-sm"><div className="flex items-start justify-between"><p className="text-sm text-slate-500">{label}</p><Icon size={19} className="text-slate-500" /></div><p className="mt-3 text-3xl font-bold">{value ?? '—'}</p></article>)}</div>
    <article className="rounded-lg border bg-white p-5"><h2 className="font-semibold">Próximo paso</h2><p className="mt-2 text-sm text-slate-600">Creá productos y registrá una compra para incorporar existencias en el inventario de la sucursal.</p></article>
  </section>
}
