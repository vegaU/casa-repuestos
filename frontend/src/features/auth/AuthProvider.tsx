/* oxlint-disable react(only-export-components, react(set-state-in-effect)) */
import { createContext, useContext, useEffect, useMemo, useState, type PropsWithChildren } from 'react'
import { AxiosError } from 'axios'
import { login as loginRequest, logout as logoutRequest, me } from './api'
import type { AuthTenant, AuthUser } from './types'
import { http, setAuthToken } from '@/lib/http'

const TOKEN_KEY = 'casa-repuestos.token'
interface AuthContextValue { user: AuthUser | null; tenants: AuthTenant[]; isLoading: boolean; signIn(email: string, password: string): Promise<void>; signOut(): Promise<void> }
const AuthContext = createContext<AuthContextValue | null>(null)
export function AuthProvider({ children }: PropsWithChildren) {
  const [user, setUser] = useState<AuthUser | null>(null); const [tenants, setTenants] = useState<AuthTenant[]>([]); const [isLoading, setLoading] = useState(true)
  const clear = () => { localStorage.removeItem(TOKEN_KEY); setAuthToken(null); setUser(null); setTenants([]) }
  useEffect(() => { const token = localStorage.getItem(TOKEN_KEY); if (!token) { setLoading(false); return }; setAuthToken(token); me().then(({ user, tenants }) => { setUser(user); setTenants(tenants) }).catch(clear).finally(() => setLoading(false)) }, [])
  useEffect(() => { const id = http.interceptors.response.use(undefined, (error: AxiosError) => { if (error.response?.status === 401) clear(); return Promise.reject(error) }); return () => http.interceptors.response.eject(id) }, [])
  const value = useMemo(() => ({ user, tenants, isLoading, async signIn(email: string, password: string) { const result = await loginRequest(email, password); localStorage.setItem(TOKEN_KEY, result.token); setAuthToken(result.token); const profile = await me(); setUser(profile.user); setTenants(profile.tenants) }, async signOut() { try { await logoutRequest() } finally { clear() } } }), [user, tenants, isLoading])
  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}
export function useAuth() { const context = useContext(AuthContext); if (!context) throw new Error('useAuth debe usarse dentro de AuthProvider'); return context }
