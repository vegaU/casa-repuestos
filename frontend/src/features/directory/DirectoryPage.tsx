import { type FormEvent, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Plus } from 'lucide-react'
import { createDirectoryItem, listDirectory, type DirectoryResource } from './api'
import { useTenant } from '@/features/tenants/TenantProvider'

export function DirectoryPage({ resource, title }: { resource: DirectoryResource; title: string }) {
  const { tenant } = useTenant()
  const [search, setSearch] = useState('')
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [phone, setPhone] = useState('')
  const queryClient = useQueryClient()
  const query = useQuery({
    queryKey: [resource, tenant?.id, search],
    queryFn: () => listDirectory(resource, tenant!.id, search),
    enabled: Boolean(tenant),
  })
  const mutation = useMutation({
    mutationFn: () => createDirectoryItem(resource, tenant!.id, { name, email: email || null, phone: phone || null }),
    onSuccess: () => {
      setName('')
      setEmail('')
      setPhone('')
      void queryClient.invalidateQueries({ queryKey: [resource, tenant?.id] })
    },
  })

  if (!tenant) return <p>Seleccioná una empresa.</p>

  function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    mutation.mutate()
  }

  return <section className="space-y-5">
    <div><p className="text-sm text-slate-500">Directorio</p><h1 className="text-2xl font-bold">{title}</h1></div>
    <input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Buscar por nombre o RUC" className="w-full rounded border p-2" />
    <form onSubmit={submit} className="flex flex-wrap gap-2 rounded-lg border bg-white p-4">
      <input value={name} onChange={(event) => setName(event.target.value)} required placeholder="Nombre" className="rounded border p-2" />
      <input value={email} onChange={(event) => setEmail(event.target.value)} type="email" placeholder="Correo" className="rounded border p-2" />
      <input value={phone} onChange={(event) => setPhone(event.target.value)} placeholder="Teléfono" className="rounded border p-2" />
      <button disabled={mutation.isPending} className="flex items-center gap-2 rounded bg-slate-900 px-3 text-white"><Plus size={16} />{mutation.isPending ? 'Guardando…' : 'Agregar'}</button>
    </form>
    {mutation.isError && <p className="text-sm text-red-600">No se pudo guardar. Revisá los datos ingresados.</p>}
    <div className="overflow-hidden rounded-lg border bg-white"><table className="w-full text-left text-sm"><thead className="bg-slate-50 text-slate-500"><tr><th className="p-3">Nombre</th><th className="p-3">Contacto</th><th className="p-3">Estado</th></tr></thead><tbody>
      {query.isLoading ? <tr><td className="p-3" colSpan={3}>Cargando…</td></tr> : query.data?.map((item) => <tr key={item.id} className="border-t"><td className="p-3"><strong>{item.name}</strong>{item.tax_id && <p className="text-slate-500">RUC: {item.tax_id}</p>}</td><td className="p-3">{item.email ?? item.phone ?? '—'}</td><td className="p-3">{item.is_active ? 'Activo' : 'Inactivo'}</td></tr>)}
    </tbody></table></div>
  </section>
}
