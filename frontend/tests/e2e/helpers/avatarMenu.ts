import { expect, type Page } from '@playwright/test';

export async function openAvatarMenu(page: Page) {
  await page.getByTestId('avatarMenuTrigger').click();
  await expect(page.getByTestId('avatarMenuPanel')).toBeVisible();
}

export async function clickAvatarMenuItem(page: Page, testId: string) {
  await openAvatarMenu(page);
  await page.getByTestId(testId).click();
}
