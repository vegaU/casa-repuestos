import { http } from '@/lib/http'

export type DirectoryResource = 'customers' | 'suppliers'

export interface DirectoryItem {
  id: number
  name: string
  tax_id: string | null
  email: string | null
  phone: string | null
  is_active: boolean
}

type PaginatedResponse<T> = { data: { data: T[] } }

export async function listDirectory(resource: DirectoryResource, tenantId: number, search = '') {
  const response = await http.get<PaginatedResponse<DirectoryItem>>(`/tenants/${tenantId}/${resource}`, { params: { search } })
  return response.data.data.data
}

export async function createDirectoryItem(resource: DirectoryResource, tenantId: number, payload: Pick<DirectoryItem, 'name' | 'email' | 'phone'>) {
  return (await http.post(`/tenants/${tenantId}/${resource}`, payload)).data.data as DirectoryItem
}
