import { test, expect } from '@playwright/test';
import {
  loginTareasAdmin,
  mockTareasProgramadasApi,
  pickDxSelectOption,
  clickDxCheckbox,
  createEmptyTareasMockState,
} from '../helpers/tareasProgramadasMocks';

test.describe.configure({ timeout: 120_000 });

test.describe('GEN-13 tareas programadas (CC PQ #22)', () => {
  test('alta BACKUP con tres tildes de nombre en parámetros', async ({ page }) => {
    const state = createEmptyTareasMockState();
    await mockTareasProgramadasApi(page, state);
    await loginTareasAdmin(page);
    await page.goto('/general/tareas-programadas/definiciones');

    await page.getByTestId('tareasProgramadas.definiciones.new').click();
    await expect(page.getByTestId('tareasProgramadas.definiciones.form')).toBeVisible();
    await pickDxSelectOption(page, 'tareasProgramadas.definiciones.process', 'Backup de base de datos');
    await page.getByTestId('tareasProgramadas.definiciones.taskTitle').locator('input').fill('Backup CC22 flags');

    await clickDxCheckbox(page, 'tareasProgramadas.definiciones.parameter.includeDateTimeInName');
    await clickDxCheckbox(page, 'tareasProgramadas.definiciones.parameter.includeWeekdayInName');
    await clickDxCheckbox(page, 'tareasProgramadas.definiciones.parameter.includeMonthInName');

    const createResponse = page.waitForResponse(
      (r) => r.url().includes('/api/v1/task-definitions') && r.request().method() === 'POST',
    );
    await page.getByTestId('tareasProgramadas.definiciones.save').click();
    await createResponse;

    const params = (state.definitions[0]?.parameters as Array<{ parameterCode: string; value: unknown }>) ?? [];
    const codes = new Set(params.map((p) => p.parameterCode));
    expect(codes.has('includeDateTimeInName')).toBeTruthy();
    expect(codes.has('includeWeekdayInName')).toBeTruthy();
    expect(codes.has('includeMonthInName')).toBeTruthy();
    const asMap = Object.fromEntries(params.map((p) => [p.parameterCode, p.value]));
    expect(asMap.includeDateTimeInName).toBe(true);
    expect(asMap.includeWeekdayInName).toBe(true);
    expect(asMap.includeMonthInName).toBe(true);
  });

  test('editar definición rehidrata los tres checkboxes', async ({ page }) => {
    const state = createEmptyTareasMockState();
    state.definitions.push({
      id: 42,
      processId: 1,
      taskTitle: 'Backup flags persisted',
      isActive: true,
      frequencyType: 'DAILY',
      frequencyInterval: 1,
      baseDate: '2026-11-14',
      baseTime: '04:00',
      companyIds: [1],
      parameters: [
        { parameterCode: 'includeDateTimeInName', value: true },
        { parameterCode: 'includeWeekdayInName', value: true },
        { parameterCode: 'includeMonthInName', value: false },
      ],
      notifyEnabled: false,
      notifyOnError: true,
      notifyOnSuccess: false,
      notifyEmails: [],
    });
    await mockTareasProgramadasApi(page, state);
    await loginTareasAdmin(page);
    await page.goto('/general/tareas-programadas/definiciones/42/editar');
    await expect(page.getByTestId('tareasProgramadas.definiciones.form')).toBeVisible({ timeout: 20_000 });

    await expect(
      page.getByTestId('tareasProgramadas.definiciones.parameter.includeDateTimeInName').locator('.dx-checkbox-checked'),
    ).toBeVisible();
    await expect(
      page.getByTestId('tareasProgramadas.definiciones.parameter.includeWeekdayInName').locator('.dx-checkbox-checked'),
    ).toBeVisible();
    await expect(
      page.getByTestId('tareasProgramadas.definiciones.parameter.includeMonthInName').locator('.dx-checkbox-checked'),
    ).toHaveCount(0);
  });

  test('eliminar con historial muestra aviso y borra definición conservando ejecuciones', async ({ page }) => {
    const state = createEmptyTareasMockState();
    state.definitions.push({
      id: 7,
      processId: 1,
      taskTitle: 'Con historial',
      isActive: true,
      frequencyType: 'DAILY',
      frequencyInterval: 1,
      baseDate: '2026-11-11',
      baseTime: '03:00',
      companyIds: [1],
      parameters: [],
      hasExecutionHistory: true,
    });
    state.executions.push({
      id: 100,
      definitionId: 7,
      companyId: 1,
      statusCode: 'EXITOSA',
      mode: 'MANUAL',
      isSimulation: false,
      message: 'ok',
      startedAt: '2026-11-11T12:00:00Z',
      finishedAt: '2026-11-11T12:01:00Z',
    });
    await mockTareasProgramadasApi(page, state);
    await loginTareasAdmin(page);
    await page.goto('/general/tareas-programadas/definiciones');

    const row = page.getByTestId('tareasProgramadas.definiciones.grid').locator('.dx-datagrid-rowsview .dx-data-row').first();
    await row.getByTestId('tareasProgramadas.definiciones.delete').click();
    await expect(page.getByTestId('tareasProgramadas.definiciones.deleteConfirm')).toBeVisible();
    await expect(page.getByTestId('tareasProgramadas.definiciones.deleteConfirmHistory')).toBeVisible();

    const delResponse = page.waitForResponse(
      (r) => r.url().includes('/task-definitions/7') && r.request().method() === 'DELETE',
    );
    await page.getByTestId('tareasProgramadas.definiciones.deleteConfirmOk').click();
    await delResponse;

    expect(state.definitions).toHaveLength(0);
    expect(state.definitionsDeleted).toHaveLength(1);
    expect(state.executions).toHaveLength(1);

    await page.getByTestId('tareasProgramadas.tab.historial').click();
    await expect(page.getByTestId('tareasProgramadas.ejecuciones.grid')).toBeVisible();
    await expect(page.getByText('Exitosa')).toBeVisible();
  });

  test('ejecución manual activa responde EN_EJECUCION sin bloquear la UI', async ({ page }) => {
    const state = createEmptyTareasMockState();
    state.definitions.push({
      id: 9,
      processId: 1,
      taskTitle: 'Run manual',
      isActive: true,
      frequencyType: 'DAILY',
      frequencyInterval: 1,
      baseDate: '2026-11-14',
      baseTime: '05:00',
      companyIds: [1],
      parameters: [],
    });
    await mockTareasProgramadasApi(page, state);
    await loginTareasAdmin(page);
    await page.goto('/general/tareas-programadas/definiciones');

    const runResponse = page.waitForResponse(
      (r) => r.url().includes('/task-definitions/9/run') && r.request().method() === 'POST',
    );
    const row = page.getByTestId('tareasProgramadas.definiciones.grid').locator('.dx-data-row').first();
    await row.getByTestId('tareasProgramadas.definiciones.run').click();
    const response = await runResponse;
    expect(response.ok()).toBeTruthy();
    const json = (await response.json()) as { resultado?: { statusCode?: string } };
    expect(json.resultado?.statusCode).toBe('EN_EJECUCION');
    await expect(page.getByTestId('tareasProgramadas.definiciones.root')).toBeVisible();
    expect(state.executions.some((e) => e.statusCode === 'EN_EJECUCION')).toBeTruthy();
  });

  test('definición inactiva: run deshabilitado (CC #20 / SDK — gap vs pedido manual inactivo)', async ({ page }) => {
    const state = createEmptyTareasMockState();
    state.definitions.push({
      id: 11,
      processId: 1,
      taskTitle: 'Inactiva',
      isActive: false,
      frequencyType: 'DAILY',
      frequencyInterval: 1,
      baseDate: '2026-11-14',
      baseTime: '06:00',
      companyIds: [1],
      parameters: [],
    });
    await mockTareasProgramadasApi(page, state);
    await loginTareasAdmin(page);
    await page.goto('/general/tareas-programadas/definiciones');

    const runBtn = page
      .getByTestId('tareasProgramadas.definiciones.grid')
      .locator('.dx-data-row')
      .first()
      .getByTestId('tareasProgramadas.definiciones.run');
    await expect(runBtn).toBeDisabled();
  });
});