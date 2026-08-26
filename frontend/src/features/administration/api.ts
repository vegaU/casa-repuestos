import { http } from '@/lib/http'

export interface AdminTenant { id:number; name:string; tax_id:string|null; email:string|null; phone:string|null; address:string|null; is_active:boolean; branches_count:number; users_count:number }
export interface AdminUser { id:number; name:string; email:string; pivot?: { role:string; is_active:boolean } }
export interface AdminTenantDetail extends AdminTenant { branches:Array<{id:number; code:string; name:string; is_active:boolean}>; users:AdminUser[] }
type Page<T>={data:{data:T[]}}

export const listAdminTenants = async (search='', isActive='') => (await http.get<Page<AdminTenant>>('/admin/tenants',{params:{search,is_active:isActive}})).data.data.data
export const getAdminTenant = async (id:number) => (await http.get<{data:AdminTenantDetail}>('/admin/tenants/'+id)).data.data
export const createAdminTenant = async (payload:unknown) => (await http.post('/admin/tenants',payload)).data.data
export const updateAdminTenant = async (id:number,payload:unknown) => (await http.patch('/admin/tenants/'+id,payload)).data.data
export const assignAdministrator = async (id:number,email:string) => (await http.post('/admin/tenants/'+id+'/administrators',{email})).data.data
export const removeAdministrator = async (tenantId:number,userId:number) => http.delete('/admin/tenants/'+tenantId+'/administrators/'+userId)
export const enterSupport = async (id:number) => (await http.post<{data:{tenant_id:number;tenant_name:string}}>('/admin/tenants/'+id+'/support')).data.data
