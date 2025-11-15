import { apiClient } from './client';

export interface SystemCheck {
  name: string;
  passed: boolean;
  details: string;
}

export interface InstallationStatus {
  isConfigured: boolean;
  tenantCount: number;
  adminCount: number;
  checks: SystemCheck[];
}

export interface InstallationPayload {
  company_name: string;
  company_slug: string;
  contact_email?: string;
  admin_name: string;
  admin_email: string;
  admin_password: string;
  admin_password_confirmation: string;
}

export async function fetchInstallationStatus(): Promise<InstallationStatus> {
  const response = await apiClient.get<InstallationStatus>('/install/status');
  return response.data;
}

export async function completeInstallation(payload: InstallationPayload) {
  const response = await apiClient.post('/install', payload);
  return response.data;
}

