export type TasksBackupBackend = 'rds' | 'windows';

/**
 * Debe alinear con `PAQSUITE_TASKS_BACKUP_BACKEND` del backend (Vite inyecta VITE_*).
 */
export function resolveTasksBackupBackend(): TasksBackupBackend {
  const raw = String(import.meta.env.VITE_PAQSUITE_TASKS_BACKUP_BACKEND ?? 'rds').toLowerCase();

  return raw === 'windows' ? 'windows' : 'rds';
}
