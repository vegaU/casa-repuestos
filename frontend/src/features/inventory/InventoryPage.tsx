import { useQuery } from '@tanstack/react-query'
import { http } from '@/lib/http'
import { useTenant } from '@/features/tenants/TenantProvider'

interface InventoryItem {
  id: number
  quantity: string | number
  reserved_quantity: string | number
  product: { name: string; sku: string | null; unit: string | null }
  branch: { name: string }
}

export function InventoryPage() {
  const { tenant, branch } = useTenant()
  const query = useQuery({
    queryKey: ['inventory', tenant?.id, branch?.id],
    queryFn: async () => (await http.get<{ data: InventoryItem[] }>(`/tenants/${tenant!.id}/inventory`, { params: { branch_id: branch?.id } })).data.data,
    enabled: Boolean(tenant && branch),
  })

  if (!tenant || !branch) return <p>Seleccioná una empresa y una sucursal.</p>

  return <section className="space-y-5">
    <div><p className="text-sm text-slate-500">Sucursal: {branch.name}</p><h1 className="text-2xl font-bold">Inventario</h1></div>
    <div className="overflow-hidden rounded-lg border bg-white"><table className="w-full text-left text-sm"><thead className="bg-slate-50 text-slate-500"><tr><th className="p-3">Producto</th><th className="p-3">SKU</th><th className="p-3 text-right">Existencia</th><th className="p-3 text-right">Reservado</th></tr></thead><tbody>
      {query.isLoading ? <tr><td className="p-3" colSpan={4}>Cargando…</td></tr> : query.data?.length === 0 ? <tr><td className="p-3" colSpan={4}>Aún no hay existencias para esta sucursal.</td></tr> : query.data?.map((item) => <tr key={item.id} className="border-t"><td className="p-3 font-medium">{item.product.name}</td><td className="p-3">{item.product.sku ?? '—'}</td><td className="p-3 text-right">{item.quantity}</td><td className="p-3 text-right">{item.reserved_quantity}</td></tr>)}
    </tbody></table></div>
  </section>
}
