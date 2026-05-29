import { test, expect } from '@playwright/test';

test('muestra pantalla base del scaffold', async ({ page }) => {
  await page.goto('http://localhost:3000');
  await expect(page.getByRole('heading', { name: 'PedidosWeb' })).toBeVisible();
});
