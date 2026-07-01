import { apiRequest } from '../../shared/http/client';

export async function healthCheckRequest(): Promise<void> {
  await apiRequest<Record<string, never>>('/health', {
    skipAuth: true,
  });
}
