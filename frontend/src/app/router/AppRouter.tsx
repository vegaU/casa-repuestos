import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import { LoginPage } from '@/features/auth/LoginPage'
import { useAuth } from '@/features/auth/AuthProvider'
import { AppLayout } from '@/components/shared/AppLayout'
import { CatalogPage } from '@/features/catalogs/CatalogPage'
import { ProductsPage } from '@/features/products/ProductsPage'
import { DirectoryPage } from '@/features/directory/DirectoryPage'
import { InventoryPage } from '@/features/inventory/InventoryPage'
import { DashboardPage } from '@/features/dashboard/DashboardPage'
import { PurchasePage } from '@/features/purchases/PurchasePage'
import { SalePage } from '@/features/sales/SalePage'
import { CompaniesPage } from '@/features/administration/CompaniesPage'
import { ChangePasswordPage } from '@/features/auth/ChangePasswordPage'
function Protected() { const { user, isLoading } = useAuth(); if (isLoading) return <main className="grid min-h-screen place-items-center">Cargando…</main>; if (!user) return <Navigate to="/login" replace />; return user.must_change_password ? <ChangePasswordPage /> : <AppLayout /> }
function SuperAdmin() { const { user } = useAuth(); return user?.is_super_admin ? <CompaniesPage /> : <Navigate to="/" replace /> }
export function AppRouter() { return <BrowserRouter><Routes><Route path="/login" element={<LoginPage />} /><Route element={<Protected />}><Route path="/" element={<DashboardPage />} /><Route path="/administracion/empresas" element={<SuperAdmin />} /><Route path="/productos" element={<ProductsPage />} /><Route path="/compras" element={<PurchasePage />} /><Route path="/ventas" element={<SalePage />} /><Route path="/inventario" element={<InventoryPage />} /><Route path="/clientes" element={<DirectoryPage resource="customers" title="Clientes" />} /><Route path="/proveedores" element={<DirectoryPage resource="suppliers" title="Proveedores" />} /><Route path="/categorias" element={<CatalogPage resource="categories" title="Categorías"/>}/><Route path="/marcas" element={<CatalogPage resource="brands" title="Marcas"/>}/><Route path="*" element={<DashboardPage />} /></Route></Routes></BrowserRouter> }
