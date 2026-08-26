import { http } from '@/lib/http'
import type { LoginResponse, MeResponse } from './types'
export async function login(email: string, password: string): Promise<LoginResponse> { return (await http.post<LoginResponse>('/login', { email, password, device_name: 'web' })).data }
export async function me(): Promise<MeResponse> { return (await http.get<MeResponse>('/me')).data }
export async function logout(): Promise<void> { await http.post('/logout') }
