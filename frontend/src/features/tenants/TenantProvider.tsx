import { createContext, useContext, useEffect, useMemo, useState, type PropsWithChildren } from 'react'
import { useQuery } from '@tanstack/react-query'
import { getTenant, getTenants } from './api'
import type { Branch, Tenant } from './types'
import { useAuth } from '@/features/auth/AuthProvider'

const TENANT_KEY='casa-repuestos.tenant'; const BRANCH_KEY='casa-repuestos.branch'
interface TenantContextValue { tenants: Tenant[]; tenant: Tenant | null; branch: Branch | null; isLoading: boolean; selectTenant(id: number): void; selectBranch(id: number): void }
const TenantContext=createContext<TenantContextValue|null>(null)
export function TenantProvider({children}:PropsWithChildren){const {user}=useAuth();const {data:tenants=[],isLoading}=useQuery({queryKey:['tenants'],queryFn:getTenants,enabled:Boolean(user)});const [tenantId,setTenantId]=useState<number|null>(()=>Number(localStorage.getItem(TENANT_KEY))||null);const [branchId,setBranchId]=useState<number|null>(()=>Number(localStorage.getItem(BRANCH_KEY))||null);const {data:tenant}=useQuery({queryKey:['tenant',tenantId],queryFn:()=>getTenant(tenantId!),enabled:Boolean(tenantId)});useEffect(()=>{if(!tenantId&&tenants[0])setTenantId(tenants[0].id)},[tenantId,tenants]);useEffect(()=>{if(tenant?.branches?.length&&!tenant.branches.some(b=>b.id===branchId)){setBranchId(tenant.branches.find(b=>b.is_active)?.id??tenant.branches[0].id)}},[tenant,branchId]);const value=useMemo(()=>({tenants,tenant:tenant??null,branch:tenant?.branches?.find(b=>b.id===branchId)??null,isLoading,selectTenant(id:number){localStorage.setItem(TENANT_KEY,String(id));localStorage.removeItem(BRANCH_KEY);setTenantId(id);setBranchId(null)},selectBranch(id:number){localStorage.setItem(BRANCH_KEY,String(id));setBranchId(id)}}),[tenants,tenant,branchId,isLoading]);return <TenantContext.Provider value={value}>{children}</TenantContext.Provider>}
export function useTenant(){const c=useContext(TenantContext);if(!c)throw new Error('useTenant debe usarse dentro de TenantProvider');return c}
