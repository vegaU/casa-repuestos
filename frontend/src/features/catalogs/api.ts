import { http } from '@/lib/http'
import type { ApiItemResponse, ApiListResponse } from '@/types/api'
export interface CatalogItem { id: number; name: string; description: string | null; is_active: boolean }
export async function listCatalog(resource: 'categories'|'brands', tenantId:number){return (await http.get<ApiListResponse<CatalogItem>>(`/tenants/${tenantId}/${resource}`)).data.data}
export async function createCatalog(resource:'categories'|'brands',tenantId:number,payload:Pick<CatalogItem,'name'|'description'|'is_active'>){return (await http.post<ApiItemResponse<CatalogItem>>(`/tenants/${tenantId}/${resource}`,payload)).data.data}
export async function updateCatalog(resource:'categories'|'brands',tenantId:number,id:number,payload:Partial<Pick<CatalogItem,'name'|'description'|'is_active'>>){return (await http.patch<ApiItemResponse<CatalogItem>>(`/tenants/${tenantId}/${resource}/${id}`,payload)).data.data}
