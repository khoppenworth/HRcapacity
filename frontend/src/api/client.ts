import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api/v1';

export interface AuthState {
  token: string | null;
  tenant: string | null;
}

const authState: AuthState = {
  token: localStorage.getItem('auth_token'),
  tenant: localStorage.getItem('tenant_slug')
};

export const apiClient = axios.create({
  baseURL: API_URL
});

apiClient.interceptors.request.use((config) => {
  if (authState.token) {
    config.headers = config.headers ?? {};
    config.headers.Authorization = `Bearer ${authState.token}`;
  }

  if (authState.tenant) {
    config.headers = config.headers ?? {};
    config.headers['X-Tenant'] = authState.tenant;
  }

  return config;
});

export function setAuth(token: string | null, tenant: string | null) {
  authState.token = token;
  authState.tenant = tenant;

  if (token) {
    localStorage.setItem('auth_token', token);
  } else {
    localStorage.removeItem('auth_token');
  }

  if (tenant) {
    localStorage.setItem('tenant_slug', tenant);
  } else {
    localStorage.removeItem('tenant_slug');
  }
}

export function getAuth(): AuthState {
  return { ...authState };
}
