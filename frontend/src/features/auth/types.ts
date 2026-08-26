export interface AuthUser { id: number; name: string; email: string; is_super_admin: boolean; must_change_password: boolean }
export interface AuthTenant { id: number; name: string; role: string }
export interface LoginResponse { token: string; token_type: 'Bearer'; user: AuthUser }
export interface MeResponse { user: AuthUser; tenants: AuthTenant[] }
