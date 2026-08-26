import axios from 'axios'
export const http = axios.create({ baseURL: import.meta.env.VITE_API_URL ?? '/api', headers: { Accept: 'application/json', 'Content-Type': 'application/json' } })
export function setAuthToken(token: string | null) { if (token) http.defaults.headers.common.Authorization = `Bearer ${token}`; else delete http.defaults.headers.common.Authorization }
