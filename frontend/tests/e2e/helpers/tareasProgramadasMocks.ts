import type { Page, Route } from '@playwright/test';

export const tareasSessionPayload = {
  token: 'test-token',
  user: { id: 1, displayName: 'Admin MVP', login: 'admin.mvp' },
  functionalProfile: 'admin',
  codCliente: null,
  codVendedor: null,
  locale: 'es-AR',
  theme: 'light',
  firstLogin: false,
  inactivityTimeoutMinutes: 10,
  security: { roles: ['Administrador'], accesoTotal: true },
};

const backupProcess = {
  id: 1,
  processCode: 'BACKUP_DATABASE',
  processName: 'Backup de base de datos',
  moduleCode: 'GEN',
  isActive: true,
  supportsManualExecution: true,
  supportsScheduledExecution: true,
  supportsSimulation: false,
  worksByCompany: true,
  selectionStrategyAuto: 'BY_CRITERIA',
  selectionStrategyManual: 'NOT_APPLICABLE',
};

const backupParameters = {
  items: [
    {
      id: 1,
      parameterCode: 'includeDateTimeInName',
      parameterName: 'Incluir fecha y hora en el nombre',
      dataType: 'BOOL',
      uiControlType: 'CHECKBOX',
      isRequired: true,
      isUserEditable: true,
      isMultivalue: false,
      displayOrder: 20,
      defaultValue: false,
      metadataJson: { appliesToModes: ['manual', 'automatic'], valueSource: 'definition' },
    },
    {
      id: 2,
      parameterCode: 'backupPolicy',
      parameterName: 'Política de backup',
      dataType: 'ENUM',
      uiControlType: 'SELECT',
      isRequired: false,
      isUserEditable: true,
      isMultivalue: false,
      displayOrder: 30,
      defaultValue: 'overwrite',
      allowedValuesSource: 'overwrite|append',
      metadataJson: { appliesToModes: ['manual', 'automatic'], valueSource: 'definition' },
    },
  ],
};

type MockState = {
  definitions: Array<Record<string, unknown>>;
  executions: Array<Record<string, unknown>>;
  nextDefinitionId: number;
  nextExecutionId: number;
};

function envelope<T>(resultado: T) {
  return { error: 0, respuesta: 'ok', resultado };
}

export async function mockTareasProgramadasApi(
  page: Page,
  state: MockState = {
    definitions: [],
    executions: [],
    nextDefinitionId: 1,
    nextExecutionId: 1,
  },
) {
  await page.route('**/api/v1/**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify(envelope({})),
    });
  });

  await page.route('**/api/v1/auth/login', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify(envelope(tareasSessionPayload)),
    });
  });

  await page.route('**/api/v1/auth/me', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify(envelope(tareasSessionPayload)),
    });
  });

  await page.route('**/api/v1/config/public', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify(
        envelope({
          gridLayoutsEnabled: false,
          pivotsEnabled: false,
          pivotLayoutsEnabled: false,
          excelImportEnabled: false,
          securityAdminEnabled: false,
        }),
      ),
    });
  });

  await page.route('**/api/v1/user/menu', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify(envelope([])),
    });
  });

  await page.route('**/api/v1/users/me/preferences', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify(
        envelope({ locale: 'es', theme: 'generic.light', openInNewTab: false }),
      ),
    });
  });

  await page.route('**/api/v1/task-processes', async (route: Route) => {
    if (route.request().method() !== 'GET') {
      await route.continue();
      return;
    }
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify(envelope({ items: [backupProcess] })),
    });
  });

  await page.route('**/api/v1/task-processes/1/parameters', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify(envelope(backupParameters)),
    });
  });

  await page.route('**/api/v1/task-definitions**', async (route: Route) => {
    const url = route.request().url();
    const method = route.request().method();

    if (method === 'GET' && !url.match(/\/task-definitions\/\d+(\?|$)/)) {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(envelope({ items: state.definitions })),
      });
      return;
    }

    if (method === 'POST' && url.endsWith('/task-definitions')) {
      const body = route.request().postDataJSON() as Record<string, unknown>;
      const id = state.nextDefinitionId++;
      const now = new Date().toISOString();
      const item = {
        id,
        processId: body.processId,
        taskTitle: body.taskTitle,
        taskDescription: body.taskDescription ?? '',
        isActive: body.isActive ?? true,
        frequencyType: body.frequencyType,
        frequencyInterval: body.frequencyInterval ?? 1,
        frequencyDaysMask: body.frequencyDaysMask ?? null,
        baseDate: body.baseDate,
        baseTime: body.baseTime,
        nextRunAt: now,
        lastRunAt: null,
        lastStatusCode: null,
        companyIds: body.companyIds ?? [1],
        parameters: body.parameters ?? [],
        notifyEnabled: body.notifyEnabled ?? false,
        notifyOnError: body.notifyOnError ?? true,
        notifyOnSuccess: body.notifyOnSuccess ?? false,
        notifyEmails: body.notifyEmails ?? [],
      };
      state.definitions.push(item);
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(envelope(item)),
      });
      return;
    }

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify(envelope({})),
    });
  });

  await page.route('**/api/v1/task-executions**', async (route: Route) => {
    const method = route.request().method();
    if (method !== 'GET') {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(envelope({})),
      });
      return;
    }

    if (state.executions.length === 0 && state.definitions.length > 0) {
      const def = state.definitions[0];
      state.executions.push({
        id: state.nextExecutionId++,
        definitionId: def.id,
        companyId: 1,
        statusCode: 'EXITOSA',
        mode: 'MANUAL',
        isSimulation: false,
        message: 'Backup E2E mock',
        startedAt: new Date().toISOString(),
        finishedAt: new Date().toISOString(),
      });
    }

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify(
        envelope({
          items: state.executions,
          total: state.executions.length,
          page: 1,
          pageSize: 25,
          totalPages: 1,
        }),
      ),
    });
  });
}

export async function loginTareasAdmin(page: Page) {
  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('admin.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();
  await page.waitForURL(/\/dashboard$/);
}

export async function pickDxSelectOption(page: Page, testId: string, optionText: string) {
  const root = page.getByTestId(testId);
  await root.click();
  const overlay = page.locator('.dx-dropdowneditor-overlay').last();
  await overlay.locator('.dx-list-item').filter({ hasText: optionText }).click();
}
