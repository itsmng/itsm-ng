import { expect, test } from '@playwright/test';
import {
  fillRichTextForm,
  login,
  openTicket,
  seedTicket,
  submitAddForm,
} from '../helpers.mjs';

test.beforeEach(async ({ page }) => {
  await login(page);
});

test('shows the seeded ticket timeline context', async ({ page, request }) => {
  const seed = await seedTicket(request);

  await openTicket(page, seed);

  await expect(page.locator('.title', { hasText: seed.ticketName })).toBeVisible();
  await expect(page.getByTestId('timeline-history')).toContainText(seed.ticketContent);
});

test('creates a followup from the timeline', async ({ page, request }) => {
  const seed = await seedTicket(request);
  const followupContent = `E2E followup ${Date.now()}`;

  await openTicket(page, seed);
  await page.getByTestId('timeline-add-followup').click();

  const form = page.getByTestId('timeline-editor').locator('form').first();
  await fillRichTextForm(form, followupContent);
  await submitAddForm(form, page);

  const followupItem = page
    .locator('[data-testid="timeline-item"][data-item-type="ITILFollowup"]')
    .filter({ hasText: followupContent })
    .first();

  await expect(followupItem).toBeVisible();

  await page.reload();
  await expect(
    page
      .locator('[data-testid="timeline-item"][data-item-type="ITILFollowup"]')
      .filter({ hasText: followupContent })
      .first()
  ).toBeVisible();
});

test('creates a task from the timeline', async ({ page, request }) => {
  const seed = await seedTicket(request);
  const taskContent = `E2E task ${Date.now()}`;

  await openTicket(page, seed);
  await page.getByTestId('timeline-add-task').click();

  const form = page.getByTestId('timeline-editor').locator('form').first();
  await fillRichTextForm(form, taskContent);
  await submitAddForm(form, page);

  const taskItem = page
    .locator('[data-testid="timeline-item"][data-item-type="TicketTask"]')
    .filter({ hasText: taskContent })
    .first();

  await expect(taskItem).toBeVisible();

  await page.reload();
  await expect(
    page
      .locator('[data-testid="timeline-item"][data-item-type="TicketTask"]')
      .filter({ hasText: taskContent })
      .first()
  ).toBeVisible();
});

test('marks a seeded todo task as done from the timeline', async ({ page, request }) => {
  const seed = await seedTicket(request, { withTaskState: 'todo' });

  await openTicket(page, seed);

  const taskItem = page.locator(`[data-testid="timeline-item"][data-item-id="${seed.taskId}"]`);
  const toggle = taskItem.getByTestId('timeline-task-state-toggle');

  await expect(toggle).toHaveAttribute('data-state', '1');
  await toggle.click();
  await expect(toggle).toHaveAttribute('data-state', '2');

  await page.reload();
  await expect(
    page
      .locator(`[data-testid="timeline-item"][data-item-id="${seed.taskId}"]`)
      .getByTestId('timeline-task-state-toggle')
  ).toHaveAttribute('data-state', '2');
});

test('marks a seeded done task back to todo from the timeline', async ({ page, request }) => {
  const seed = await seedTicket(request, { withTaskState: 'done' });

  await openTicket(page, seed);

  const taskItem = page.locator(`[data-testid="timeline-item"][data-item-id="${seed.taskId}"]`);
  const toggle = taskItem.getByTestId('timeline-task-state-toggle');

  await expect(toggle).toHaveAttribute('data-state', '2');
  await toggle.click();
  await expect(toggle).toHaveAttribute('data-state', '1');

  await page.reload();
  await expect(
    page
      .locator(`[data-testid="timeline-item"][data-item-id="${seed.taskId}"]`)
      .getByTestId('timeline-task-state-toggle')
  ).toHaveAttribute('data-state', '1');
});
