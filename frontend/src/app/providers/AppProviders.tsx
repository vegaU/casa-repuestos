import type { PropsWithChildren } from 'react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { AuthProvider } from '@/features/auth/AuthProvider'
import { TenantProvider } from '@/features/tenants/TenantProvider'
const client = new QueryClient({ defaultOptions: { queries: { retry: 1, refetchOnWindowFocus: false } } })
export function AppProviders({ children }: PropsWithChildren) { return <QueryClientProvider client={client}><AuthProvider><TenantProvider>{children}</TenantProvider></AuthProvider></QueryClientProvider> }
