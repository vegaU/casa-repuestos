import { type FormEvent, useState } from 'react'
import { http } from '@/lib/http'

export function ChangePasswordPage() {
  const [password,setPassword]=useState('')
  const [confirmation,setConfirmation]=useState('')
  const [error,setError]=useState('')
  const [loading,setLoading]=useState(false)
  async function submit(event:FormEvent){event.preventDefault();setError('');if(password!==confirmation){setError('Las contraseñas no coinciden.');return}setLoading(true);try{await http.post('/change-password',{password,password_confirmation:confirmation});window.location.reload()}catch{setError('No se pudo actualizar la contraseña. Debe tener al menos 10 caracteres.')}finally{setLoading(false)}}
  return <main className="grid min-h-screen place-items-center bg-slate-100 p-6"><form onSubmit={submit} className="w-full max-w-md space-y-4 rounded-lg border bg-white p-6 shadow-sm"><h1 className="text-xl font-bold">Cambiá tu contraseña temporal</h1><p className="text-sm text-slate-600">Por seguridad, debés establecer una contraseña propia antes de usar el sistema.</p><label className="block text-sm">Nueva contraseña<input value={password} onChange={e=>setPassword(e.target.value)} type="password" minLength={10} required className="mt-1 w-full rounded border p-2"/></label><label className="block text-sm">Confirmar contraseña<input value={confirmation} onChange={e=>setConfirmation(e.target.value)} type="password" minLength={10} required className="mt-1 w-full rounded border p-2"/></label>{error&&<p className="text-sm text-red-600">{error}</p>}<button disabled={loading} className="w-full rounded bg-slate-900 py-2 text-white">{loading?'Guardando…':'Actualizar contraseña'}</button></form></main>
}
