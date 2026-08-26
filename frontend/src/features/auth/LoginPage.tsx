import { useState, type FormEvent } from 'react'
import { Navigate } from 'react-router-dom'
import { AxiosError } from 'axios'
import { useAuth } from './AuthProvider'
import type { LaravelValidationError } from '@/types/api'

export function LoginPage() {
  const { user, signIn } = useAuth(); const [error, setError] = useState<string | null>(null); const [loading, setLoading] = useState(false)
  if (user) return <Navigate to="/" replace />
  async function submit(event: FormEvent<HTMLFormElement>) { event.preventDefault(); setLoading(true); setError(null); const form = new FormData(event.currentTarget); try { await signIn(String(form.get('email')), String(form.get('password'))) } catch (reason) { const response = (reason as AxiosError<LaravelValidationError>).response; setError(response?.data.errors?.email?.[0] ?? response?.data.message ?? 'No fue posible iniciar sesión.') } finally { setLoading(false) } }
  return <main className="grid min-h-screen place-items-center bg-slate-100 p-6"><form onSubmit={submit} className="w-full max-w-sm space-y-5 rounded-xl bg-white p-8 shadow"><div><h1 className="text-2xl font-bold text-slate-900">Casa Repuestos</h1><p className="mt-1 text-sm text-slate-500">Iniciá sesión para continuar.</p></div>{error && <p role="alert" className="rounded bg-red-50 p-3 text-sm text-red-700">{error}</p>}<label className="block text-sm font-medium text-slate-700">Correo<input required name="email" type="email" autoComplete="email" className="mt-1 w-full rounded border border-slate-300 px-3 py-2" /></label><label className="block text-sm font-medium text-slate-700">Contraseña<input required name="password" type="password" autoComplete="current-password" className="mt-1 w-full rounded border border-slate-300 px-3 py-2" /></label><button disabled={loading} className="w-full rounded bg-slate-900 px-4 py-2 font-medium text-white disabled:opacity-50">{loading ? 'Ingresando…' : 'Ingresar'}</button></form></main>
}
