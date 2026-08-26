export interface Branch { id: number; tenant_id: number; code: string; name: string; address: string | null; email: string | null; phone: string | null; is_active: boolean }
export interface Tenant { id: number; name: string; legal_name: string | null; tax_id: string | null; is_active: boolean; branches?: Branch[] }
