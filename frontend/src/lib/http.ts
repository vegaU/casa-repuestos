import axios from 'axios'
export const http = axios.create({ baseURL: import.meta.env.VITE_API_URL ?? '/api', headers: { Accept: 'application/json', 'Content-Type': 'application/json' } })
export function setAuthToken(token: string | null) { if (token) http.defaults.headers.common.Authorization = `Bearer ${token}`; else delete http.defaults.headers.common.Authorization }

/** Converts Laravel validation responses into a useful message for forms. */
export function apiErrorMessage(error: unknown, fallback = 'No se pudo completar la operación.') {
  if (!axios.isAxiosError(error)) return fallback
  const data = error.response?.data as { message?: string; errors?: Record<string, string[]> } | undefined
  const first = data?.errors && Object.values(data.errors).flat()[0]
  return first ?? data?.message ?? fallback
}
