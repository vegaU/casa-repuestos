import { http } from '@/lib/http'
export interface Product { id: number; name: string; sku: string | null; sale_price: string; is_active: boolean }
export async function listProducts(tenantId: number, search = '') { return (await http.get<{ data: { data: Product[] } }>(`/tenants/${tenantId}/products`, { params: { search } })).data.data.data }
export async function createProduct(tenantId: number, payload: { name: string; sku?: string; cost_price?: number; sale_price?: number }) { return (await http.post(`/tenants/${tenantId}/products`, payload)).data }
