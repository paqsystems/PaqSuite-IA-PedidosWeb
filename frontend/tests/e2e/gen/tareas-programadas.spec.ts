import { test, expect } from '@playwright/test';
import {
  loginTareasAdmin,
  mockTareasProgramadasApi,
  pickDxSelectOption,
} from '../helpers/tareasProgramadasMocks';

test.describe.configure({ timeout: 90_000 });

test.describe('GEN-13 tareas programadas (CC #20)', () => {
  test('muestra las cuatro solapas y aterriza en definiciones', async ({ page }) => {
    await mockTareasProgramadasApi(page);
    await loginTareasAdmin(page);

    await page.goto('/general/tareas-programadas');
    await expect(page).toHaveURL(/\/general\/tareas-programadas\/definiciones$/);
    await expect(page.getByTestId('page-tareas-programadas')).toBeVisible();

    await expect(page.getByTestId('tareasProgramadas.tab.procesos')).toBeVisible();
    await expect(page.getByTestId('tareasProgramadas.tab.definiciones')).toBeVisible();
    await expect(page.getByTestId('tareasProgramadas.tab.monitoreo')).toBeVisible();
    await expect(page.getByTestId('tareasProgramadas.tab.historial')).toBeVisible();
    await expect(page.getByTestId('tareasProgramadas.definiciones.root')).toBeVisible();
  });

  test('navega entre solapas de procesos, definiciones, monitoreo e historial', async ({ page }) => {
    await mockTareasProgramadasApi(page);
    await loginTareasAdmin(page);
    await page.goto('/general/tareas-programadas/definiciones');

    await page.getByTestId('tareasProgramadas.tab.procesos').click();
    await expect(page).toHaveURL(/\/procesos$/);
    await expect(page.getByTestId('tareasProgramadas.procesos.root')).toBeVisible();

    await page.getByTestId('tareasProgramadas.tab.definiciones').click();
    await expect(page).toHaveURL(/\/definiciones$/);
    await expect(page.getByTestId('tareasProgramadas.definiciones.root')).toBeVisible();

    await page.getByTestId('tareasProgramadas.tab.monitoreo').click();
    await expect(page).toHaveURL(/\/monitoreo$/);
    await expect(page.getByTestId('tareasProgramadas.monitoreo.root')).toBeVisible();

    await page.getByTestId('tareasProgramadas.tab.historial').click();
    await expect(page).toHaveURL(/\/historial$/);
    await expect(page.getByTestId('tareasProgramadas.ejecuciones.root')).toBeVisible();
  });

  test('alta definición BACKUP_DATABASE diaria y lista en grilla', async ({ page }) => {
    const state = {
      definitions: [] as Array<Record<string, unknown>>,
      executions: [] as Array<Record<string, unknown>>,
      nextDefinitionId: 1,
      nextExecutionId: 1,
    };
    await mockTareasProgramadasApi(page, state);
    await loginTareasAdmin(page);
    await page.goto('/general/tareas-programadas/definiciones');

    const createResponse = page.waitForResponse(
      (response) =>
        response.url().includes('/api/v1/task-definitions') && response.request().method() === 'POST',
    );

    await page.getByTestId('tareasProgramadas.definiciones.new').click();
    await expect(page.getByTestId('tareasProgramadas.definiciones.form')).toBeVisible();

    await pickDxSelectOption(page, 'tareasProgramadas.definiciones.process', 'Backup de base de datos');

    const taskTitle = 'Backup diario demo E2E';
    await page.getByTestId('tareasProgramadas.definiciones.taskTitle').locator('input').fill(taskTitle);

    await page.getByTestId('tareasProgramadas.definiciones.save').click();
    const response = await createResponse;
    expect(response.ok()).toBeTruthy();

    await expect(page).toHaveURL(/\/definiciones$/);
    await expect(page.getByTestId('tareasProgramadas.definiciones.grid')).toBeVisible();
    await expect(
      page.getByTestId('tareasProgramadas.definiciones.grid').locator('.dx-datagrid-rowsview').getByText(taskTitle),
    ).toBeVisible();

    expect(state.definitions).toHaveLength(1);
    expect(state.definitions[0]?.taskTitle).toBe(taskTitle);
    expect(state.definitions[0]?.frequencyType).toBe('DAILY');
    expect(state.definitions[0]?.processId).toBe(1);
  });

  test('historial muestra ejecución tras crear definición', async ({ page }) => {
    const state = {
      definitions: [
        {
          id: 7,
          processId: 1,
          taskTitle: 'Backup existente',
          isActive: true,
          frequencyType: 'DAILY',
          frequencyInterval: 1,
          baseDate: '2026-11-11',
          baseTime: '03:00',
          companyIds: [1],
        },
      ],
      executions: [
        {
          id: 100,
          definitionId: 7,
          companyId: 1,
          statusCode: 'EXITOSA',
          mode: 'MANUAL',
          isSimulation: false,
          message: 'Backup mock OK',
          startedAt: '2026-11-11T12:00:00Z',
          finishedAt: '2026-11-11T12:01:00Z',
        },
      ],
      nextDefinitionId: 8,
      nextExecutionId: 101,
    };
    await mockTareasProgramadasApi(page, state);
    await loginTareasAdmin(page);
    await page.goto('/general/tareas-programadas/historial');

    await expect(page.getByTestId('tareasProgramadas.ejecuciones.root')).toBeVisible();
    await expect(page.getByTestId('tareasProgramadas.ejecuciones.grid')).toBeVisible();
    await expect(page.getByText('Exitosa')).toBeVisible();
    await expect(
      page.getByTestId('tareasProgramadas.ejecuciones.grid').locator('.dx-datagrid-rowsview').getByText('7'),
    ).toBeVisible();
  });
});
