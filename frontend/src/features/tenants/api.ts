import { http } from '@/lib/http'
import type { ApiItemResponse, ApiListResponse } from '@/types/api'
import type { Tenant } from './types'
export async function getTenants() { return (await http.get<ApiListResponse<Tenant>>('/tenants')).data.data }
export async function getTenant(id: number) { return (await http.get<ApiItemResponse<Tenant>>(`/tenants/${id}`)).data.data }
