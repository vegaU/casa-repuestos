import { Boxes, ChevronLeft, CircleDollarSign, LayoutDashboard, LogOut, Menu, Package, Settings, ShoppingCart, Tags, UsersRound, Warehouse } from 'lucide-react'
import { useState } from 'react'
import { NavLink, Outlet } from 'react-router-dom'
import { useAuth } from '@/features/auth/AuthProvider'
import { useTenant } from '@/features/tenants/TenantProvider'

const links=[['/', 'Inicio', LayoutDashboard],['/productos','Productos',Package],['/compras','Compras',ShoppingCart],['/ventas','Ventas',CircleDollarSign],['/inventario','Inventario',Warehouse],['/clientes','Clientes',UsersRound],['/proveedores','Proveedores',UsersRound],['/categorias','Categorías',Tags],['/marcas','Marcas',Tags]] as const

export function AppLayout(){
  const [collapsed,setCollapsed]=useState(false)
  const [supportTenant,setSupportTenant]=useState<number|null>(()=>Number(localStorage.getItem('casa-repuestos.support-tenant'))||null)
  const {user,signOut}=useAuth()
  const {tenants,tenant,branch,selectTenant,selectBranch}=useTenant()
  const inSupport=user?.is_super_admin&&supportTenant===tenant?.id
  return <div className="min-h-screen bg-slate-100 text-slate-900">
    <aside className={`fixed inset-y-0 left-0 z-20 flex flex-col bg-slate-950 text-slate-100 transition-all ${collapsed?'w-16':'w-64'}`}><div className="flex h-16 items-center gap-3 border-b border-slate-800 px-4"><Boxes/><span className={collapsed?'hidden':'font-semibold'}>Casa Repuestos</span></div>
      <nav className="flex-1 p-3">{links.map(([to,label,Icon])=><NavLink key={to} to={to} className="mb-1 flex items-center gap-3 rounded px-3 py-2 text-sm hover:bg-slate-800"><Icon size={18}/><span className={collapsed?'hidden':''}>{label}</span></NavLink>)}{user?.is_super_admin&&<NavLink to="/administracion/empresas" className="mt-4 flex items-center gap-3 rounded px-3 py-2 text-sm text-amber-300 hover:bg-slate-800"><Settings size={18}/><span className={collapsed?'hidden':''}>Administración · Empresas</span></NavLink>}</nav>
      <button onClick={()=>setCollapsed(!collapsed)} className="m-3 flex items-center gap-3 rounded p-2 hover:bg-slate-800">{collapsed?<Menu size={18}/>:<ChevronLeft size={18}/>}</button>
    </aside>
    <div className={`transition-all ${collapsed?'ml-16':'ml-64'}`}><header className="min-h-16 border-b bg-white px-6 py-3">{inSupport&&<div className="mb-3 flex items-center justify-between rounded bg-amber-100 px-3 py-2 text-sm text-amber-900"><span>Modo soporte: {tenant?.name}. Actuás como superadministrador; las acciones se auditan.</span><button onClick={()=>{localStorage.removeItem('casa-repuestos.support-tenant');setSupportTenant(null)}}>Salir</button></div>}<div className="flex flex-wrap items-center justify-between gap-3"><div className="flex gap-2"><select value={tenant?.id??''} onChange={e=>selectTenant(Number(e.target.value))} className="rounded border px-2 py-1">{tenants.map(t=><option key={t.id} value={t.id}>{t.name}</option>)}</select><select value={branch?.id??''} onChange={e=>selectBranch(Number(e.target.value))} className="rounded border px-2 py-1">{tenant?.branches?.map(b=><option key={b.id} value={b.id}>{b.name}</option>)}</select></div><div className="flex items-center gap-3 text-sm"><span>{user?.name}</span><button onClick={()=>void signOut()} aria-label="Cerrar sesión"><LogOut size={18}/></button></div></div></header><main className="p-6"><Outlet/></main></div>
  </div>
}
