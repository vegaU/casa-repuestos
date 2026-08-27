import { http } from '@/lib/http'
export interface Product { id:number; name:string; sku:string|null; barcode:string|null; description:string|null; unit:string; cost_price:string; sale_price:string; reorder_point:string; is_active:boolean; category?:{id:number;name:string}|null; brand?:{id:number;name:string}|null }
export interface ProductPayload { name:string; sku?:string|null; barcode?:string|null; description?:string|null; unit?:string; cost_price?:number; sale_price?:number; reorder_point?:number; category_id?:number|null; brand_id?:number|null; is_active?:boolean }
export async function listProducts(tenantId:number,search=''){return (await http.get<{data:{data:Product[]}}>('/tenants/'+tenantId+'/products',{params:{search}})).data.data.data}
export async function createProduct(tenantId:number,payload:ProductPayload){return (await http.post<{data:Product}>('/tenants/'+tenantId+'/products',payload)).data.data}
export async function updateProduct(tenantId:number,id:number,payload:ProductPayload){return (await http.patch<{data:Product}>('/tenants/'+tenantId+'/products/'+id,payload)).data.data}
