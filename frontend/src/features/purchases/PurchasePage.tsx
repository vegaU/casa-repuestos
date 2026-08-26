import { type FormEvent, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Check, Plus } from 'lucide-react'
import { http } from '@/lib/http'
import { useTenant } from '@/features/tenants/TenantProvider'

type Product = { id: number; name: string; sku: string | null; cost_price: string | number }
type Supplier = { id: number; name: string }
type Paginated<T> = { data: { data: T[] } }

export function PurchasePage() {
  const { tenant, branch } = useTenant()
  const queryClient = useQueryClient()
  const [supplierId, setSupplierId] = useState('')
  const [productId, setProductId] = useState('')
  const [quantity, setQuantity] = useState('1')
  const [unitCost, setUnitCost] = useState('')
  const [document, setDocument] = useState('')
  const products = useQuery({ queryKey: ['products', tenant?.id], queryFn: async () => (await http.get<Paginated<Product>>(`/tenants/${tenant!.id}/products`)).data.data.data, enabled: Boolean(tenant) })
  const suppliers = useQuery({ queryKey: ['suppliers', tenant?.id], queryFn: async () => (await http.get<Paginated<Supplier>>(`/tenants/${tenant!.id}/suppliers`)).data.data.data, enabled: Boolean(tenant) })
  const mutation = useMutation({
    mutationFn: async () => {
      const purchase = await http.post<{ data: { id: number } }>(`/tenants/${tenant!.id}/purchases`, { branch_id: branch!.id, supplier_id: supplierId ? Number(supplierId) : null, supplier_document_number: document || null, items: [{ product_id: Number(productId), quantity: Number(quantity), unit_cost: Number(unitCost) }] })
      return (await http.post(`/tenants/${tenant!.id}/purchases/${purchase.data.data.id}/receive`)).data
    },
    onSuccess: () => {
      setSupplierId('')
      setProductId('')
      setQuantity('1')
      setUnitCost('')
      setDocument('')
      void queryClient.invalidateQueries({ queryKey: ['inventory', tenant?.id, branch?.id] })
      void queryClient.invalidateQueries({ queryKey: ['dashboard', 'inventory', tenant?.id, branch?.id] })
    },
  })

  if (!tenant || !branch) return <p>Seleccioná una empresa y una sucursal.</p>

  function onProductChange(value: string) {
    setProductId(value)
    const product = products.data?.find((item) => item.id === Number(value))
    if (product) setUnitCost(String(product.cost_price ?? ''))
  }

  function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    mutation.mutate()
  }

  return <section className="space-y-6">
    <div><p className="text-sm text-slate-500">Ingreso de mercadería · {branch.name}</p><h1 className="text-2xl font-bold">Nueva compra</h1></div>
    <form onSubmit={submit} className="grid gap-4 rounded-lg border bg-white p-5 lg:grid-cols-2">
      <label className="text-sm">Proveedor<select value={supplierId} onChange={(event) => setSupplierId(event.target.value)} className="mt-1 w-full rounded border p-2"><option value="">Sin proveedor</option>{suppliers.data?.map((supplier) => <option key={supplier.id} value={supplier.id}>{supplier.name}</option>)}</select></label>
      <label className="text-sm">Nº de documento<input value={document} onChange={(event) => setDocument(event.target.value)} className="mt-1 w-full rounded border p-2" placeholder="Factura o remisión" /></label>
      <label className="text-sm">Producto<select value={productId} onChange={(event) => onProductChange(event.target.value)} required className="mt-1 w-full rounded border p-2"><option value="">Seleccionar producto</option>{products.data?.map((product) => <option key={product.id} value={product.id}>{product.name}{product.sku ? ` (${product.sku})` : ''}</option>)}</select></label>
      <div className="grid grid-cols-2 gap-3"><label className="text-sm">Cantidad<input value={quantity} onChange={(event) => setQuantity(event.target.value)} type="number" min="0.001" step="0.001" required className="mt-1 w-full rounded border p-2" /></label><label className="text-sm">Costo unitario<input value={unitCost} onChange={(event) => setUnitCost(event.target.value)} type="number" min="0" required className="mt-1 w-full rounded border p-2" /></label></div>
      <div className="lg:col-span-2"><button disabled={mutation.isPending} className="flex items-center gap-2 rounded bg-slate-900 px-4 py-2 text-white">{mutation.isPending ? <Plus size={16} className="animate-spin" /> : <Check size={16} />}{mutation.isPending ? 'Registrando…' : 'Registrar y recibir compra'}</button></div>
    </form>
    {mutation.isSuccess && <p className="rounded bg-emerald-50 p-3 text-sm text-emerald-800">Compra recibida. El inventario de la sucursal fue actualizado.</p>}
    {mutation.isError && <p className="rounded bg-red-50 p-3 text-sm text-red-800">No se pudo registrar la compra. Revisá los campos e intentá nuevamente.</p>}
  </section>
}
