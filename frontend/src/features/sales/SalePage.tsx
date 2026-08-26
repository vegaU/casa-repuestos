import { type FormEvent, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Check } from 'lucide-react'
import { http } from '@/lib/http'
import { useTenant } from '@/features/tenants/TenantProvider'

type Product = { id: number; name: string; sku: string | null; sale_price: string | number }
type Customer = { id: number; name: string }
type Paginated<T> = { data: { data: T[] } }

export function SalePage() {
  const { tenant, branch } = useTenant()
  const queryClient = useQueryClient()
  const [customerId, setCustomerId] = useState('')
  const [productId, setProductId] = useState('')
  const [quantity, setQuantity] = useState('1')
  const [unitPrice, setUnitPrice] = useState('')
  const products = useQuery({ queryKey: ['products', tenant?.id], queryFn: async () => (await http.get<Paginated<Product>>(`/tenants/${tenant!.id}/products`)).data.data.data, enabled: Boolean(tenant) })
  const customers = useQuery({ queryKey: ['customers', tenant?.id], queryFn: async () => (await http.get<Paginated<Customer>>(`/tenants/${tenant!.id}/customers`)).data.data.data, enabled: Boolean(tenant) })
  const mutation = useMutation({
    mutationFn: async () => {
      const sale = await http.post<{ data: { id: number } }>(`/tenants/${tenant!.id}/sales`, { branch_id: branch!.id, customer_id: customerId ? Number(customerId) : null, items: [{ product_id: Number(productId), quantity: Number(quantity), unit_price: Number(unitPrice) }] })
      return (await http.post(`/tenants/${tenant!.id}/sales/${sale.data.data.id}/complete`)).data
    },
    onSuccess: () => {
      setCustomerId('')
      setProductId('')
      setQuantity('1')
      setUnitPrice('')
      void queryClient.invalidateQueries({ queryKey: ['inventory', tenant?.id, branch?.id] })
      void queryClient.invalidateQueries({ queryKey: ['dashboard', 'inventory', tenant?.id, branch?.id] })
    },
  })

  if (!tenant || !branch) return <p>Seleccioná una empresa y una sucursal.</p>

  function changeProduct(value: string) {
    setProductId(value)
    const product = products.data?.find((item) => item.id === Number(value))
    if (product) setUnitPrice(String(product.sale_price ?? ''))
  }

  function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    mutation.mutate()
  }

  return <section className="space-y-6">
    <div><p className="text-sm text-slate-500">Salida de mercadería · {branch.name}</p><h1 className="text-2xl font-bold">Nueva venta</h1></div>
    <form onSubmit={submit} className="grid gap-4 rounded-lg border bg-white p-5 lg:grid-cols-2">
      <label className="text-sm">Cliente<select value={customerId} onChange={(event) => setCustomerId(event.target.value)} className="mt-1 w-full rounded border p-2"><option value="">Consumidor final</option>{customers.data?.map((customer) => <option key={customer.id} value={customer.id}>{customer.name}</option>)}</select></label>
      <label className="text-sm">Producto<select value={productId} onChange={(event) => changeProduct(event.target.value)} required className="mt-1 w-full rounded border p-2"><option value="">Seleccionar producto</option>{products.data?.map((product) => <option key={product.id} value={product.id}>{product.name}{product.sku ? ` (${product.sku})` : ''}</option>)}</select></label>
      <div className="grid grid-cols-2 gap-3"><label className="text-sm">Cantidad<input value={quantity} onChange={(event) => setQuantity(event.target.value)} type="number" min="0.001" step="0.001" required className="mt-1 w-full rounded border p-2" /></label><label className="text-sm">Precio unitario<input value={unitPrice} onChange={(event) => setUnitPrice(event.target.value)} type="number" min="0" required className="mt-1 w-full rounded border p-2" /></label></div>
      <div className="lg:col-span-2"><button disabled={mutation.isPending} className="flex items-center gap-2 rounded bg-slate-900 px-4 py-2 text-white"><Check size={16} />{mutation.isPending ? 'Registrando…' : 'Registrar y completar venta'}</button></div>
    </form>
    {mutation.isSuccess && <p className="rounded bg-emerald-50 p-3 text-sm text-emerald-800">Venta completada. El inventario de la sucursal fue actualizado.</p>}
    {mutation.isError && <p className="rounded bg-red-50 p-3 text-sm text-red-800">No se pudo completar la venta. Verificá que exista stock disponible y revisá los datos.</p>}
  </section>
}
