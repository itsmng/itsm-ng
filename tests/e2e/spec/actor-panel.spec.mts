import { expect, type Locator, type Page, test } from '@playwright/test';
import {
  getActorPanel,
  login,
  openTicket,
  seedTicket,
  submitForm,
  waitForTicketIdByName,
  waitForActorValueSelect,
} from '../helpers.mjs';

async function openObserverPanel(page: Page): Promise<Locator> {
  const ticketTabButton = page.getByRole('button', { name: 'Ticket', exact: true });
  if (await ticketTabButton.count()) {
    await expect(ticketTabButton).toBeVisible();

    if ((await ticketTabButton.getAttribute('aria-expanded')) !== 'true') {
      await ticketTabButton.click();
    }
  }

  const observerPanel = getActorPanel(page, 'observer');
  await expect(observerPanel).toBeVisible({ timeout: 15_000 });
  return observerPanel;
}

async function addObserver(
  page: Page,
  observerPanel: Locator,
  userId: number | string | null,
  options: {
    alternativeEmail?: string;
    useNotification?: boolean;
  } = {}
): Promise<Locator> {
  const actorSelect = await waitForActorValueSelect(observerPanel);
  const actorId = userId === null ? '0' : String(userId);

  await expect(actorSelect.locator(`option[value="${actorId}"]`)).toHaveCount(1);

  if (userId === null) {
    await actorSelect.selectOption(actorId);
  } else {
    await Promise.all([
      page.waitForResponse((response) => {
        return response.url().includes('/ajax/v2/itilActorEmail.php')
          && response.request().method() === 'GET'
          && response.ok();
      }),
      actorSelect.selectOption(actorId),
    ]);
  }

  const selectorContainer = observerPanel.locator('[data-role="selector-container"]');
  const emailInput = selectorContainer.locator('input[name^="_itil_observer[alternative_email]"]').first();
  const notificationCheckbox = selectorContainer
    .locator('input[type="checkbox"][name^="_itil_observer[use_notification]"]')
    .first();

  await expect(emailInput).toBeVisible();
  await expect(notificationCheckbox).toBeVisible();

  if (options.alternativeEmail !== undefined) {
    await emailInput.fill(options.alternativeEmail);
  }

  if (options.useNotification !== undefined) {
    if ((await notificationCheckbox.isChecked()) !== options.useNotification) {
      await notificationCheckbox.click();
    }
  }

  await observerPanel.locator('[data-role="add-actor"]').click();

  return observerPanel.locator(
    `[data-actor-entry][data-entry-type="user"][data-entry-id="${actorId}"][data-persisted="0"]`
  );
}

async function getAvailableObserverUserIds(observerPanel: Locator, count: number): Promise<number[]> {
  const actorSelect = await waitForActorValueSelect(observerPanel);
  const actorIds = await actorSelect.locator('option').evaluateAll((options) => {
    return Array.from(new Set(options
      .map((option) => option.getAttribute('value') || '')
      .filter((value) => value !== '' && value !== '0')))
      .map((value) => Number(value))
      .filter((value) => Number.isInteger(value) && value > 0);
  });

  expect(actorIds.length).toBeGreaterThanOrEqual(count);
  return actorIds.slice(0, count);
}

test.beforeEach(async ({ page }) => {
  await login(page);
});

test('shows the pending badge and submits pending observer changes with manual email values', async ({ page, request }) => {
  const seed = await seedTicket(request);
  const alternativeEmail = `observer-${Date.now()}@example.com`;

  await openTicket(page, seed);

  const observerPanel = await openObserverPanel(page);
  await expect(observerPanel.locator('[data-actor-entry]')).toHaveCount(0);
  await expect(observerPanel.locator('[data-role="empty-state"]')).toBeVisible();

  const pendingObserver = await addObserver(page, observerPanel, seed.userId, {
    alternativeEmail,
    useNotification: false,
  });

  await expect(pendingObserver).toBeVisible();
  await expect(pendingObserver.locator('.badge.text-bg-warning')).toHaveText('Pending');
  await expect(pendingObserver).toContainText(alternativeEmail);
  await expect(pendingObserver).toContainText('Email followup: No');
  await expect(
    pendingObserver.locator('input[type="hidden"][name="_itil_observer[_type]"][value="user"]')
  ).toHaveCount(1);
  await expect(
    pendingObserver.locator(`input[type="hidden"][name="_itil_observer[users_id]"][value="${seed.userId}"]`)
  ).toHaveCount(1);
  await expect(
    pendingObserver.locator('input[type="hidden"][name="_itil_observer[use_notification][]"][value="0"]')
  ).toHaveCount(1);
  await expect(
    pendingObserver.locator(`input[type="hidden"][name="_itil_observer[alternative_email][]"][value="${alternativeEmail}"]`)
  ).toHaveCount(1);

  await submitForm(page, 'update');

  const persistedObserver = (await openObserverPanel(page)).locator(
    `[data-actor-entry][data-entry-type="user"][data-entry-id="${seed.userId}"][data-persisted="1"]`
  );
  await expect(persistedObserver).toBeVisible();
  await expect(persistedObserver.locator('.badge.text-bg-warning')).toHaveCount(0);
  await expect(persistedObserver).toContainText(alternativeEmail);
  await expect(persistedObserver).toContainText('Email followup: No');

  await page.reload();
  const reloadedObserver = (await openObserverPanel(page)).locator(
    `[data-actor-entry][data-entry-type="user"][data-entry-id="${seed.userId}"][data-persisted="1"]`
  );
  await expect(reloadedObserver).toBeVisible();
  await expect(reloadedObserver.locator('.badge.text-bg-warning')).toHaveCount(0);
  await expect(reloadedObserver).toContainText(alternativeEmail);
});

test('does not submit a pending observer that was removed before saving', async ({ page, request }) => {
  const seed = await seedTicket(request);
  const alternativeEmail = `removed-${Date.now()}@example.com`;

  await openTicket(page, seed);

  const observerPanel = await openObserverPanel(page);
  const pendingObserver = await addObserver(page, observerPanel, seed.userId, {
    alternativeEmail,
    useNotification: false,
  });

  await expect(pendingObserver).toBeVisible();

  await pendingObserver.locator('[data-role="remove-actor"]').click();
  await expect(observerPanel.locator('[data-actor-entry]')).toHaveCount(0);
  await expect(observerPanel.locator('[data-role="empty-state"]')).toBeVisible();
  await expect(observerPanel.locator('input[type="hidden"][name="_itil_observer[_type]"]')).toHaveCount(0);
  await expect(observerPanel.locator('input[type="hidden"][name="_itil_observer[users_id]"]')).toHaveCount(0);
  await expect(observerPanel.locator('input[type="hidden"][name="_itil_observer[use_notification][]"]')).toHaveCount(0);
  await expect(observerPanel.locator('input[type="hidden"][name="_itil_observer[alternative_email][]"]')).toHaveCount(0);

  await submitForm(page, 'update');

  await page.reload();
  await expect((await openObserverPanel(page)).locator('[data-actor-entry]')).toHaveCount(0);
});

test('submits multiple pending observers in a single save', async ({ page, request }) => {
  const ticketName = `Batch observer ticket ${Date.now()}`;
  const ticketContent = `Batch observer content ${Date.now()}`;

  await page.goto('/front/ticket.form.php');
  await page.locator('input[name="name"]').fill(ticketName);
  const ticketForm = page.locator('form').filter({ has: page.locator('input[name="add"], button[name="add"]') }).first();
  await page.getByRole('textbox', { name: /Rich Text Editor/ }).fill(ticketContent);

  const observerPanel = await openObserverPanel(page);
  const [firstObserverId, secondObserverId] = await getAvailableObserverUserIds(observerPanel, 2);
  const pendingFirstObserver = await addObserver(page, observerPanel, firstObserverId);
  const pendingSecondObserver = await addObserver(page, observerPanel, secondObserverId);

  await expect(pendingFirstObserver).toBeVisible();
  await expect(pendingSecondObserver).toBeVisible();
  await expect(observerPanel.locator('.badge.text-bg-warning')).toHaveCount(2);
  await expect(observerPanel.locator('input[type="hidden"][name="_users_id_observer[]"]')).toHaveCount(2);
  await expect(
    observerPanel.locator(`input[type="hidden"][name="_users_id_observer[]"][value="${firstObserverId}"]`)
  ).toHaveCount(1);
  await expect(
    observerPanel.locator(`input[type="hidden"][name="_users_id_observer[]"][value="${secondObserverId}"]`)
  ).toHaveCount(1);

  await submitForm(page, 'add', ticketForm);

  const createdTicketId = await waitForTicketIdByName(request, ticketName);
  await page.goto(`/front/ticket.form.php?id=${createdTicketId}`);

  const persistedObserverPanel = await openObserverPanel(page);
  const persistedFirstObserver = persistedObserverPanel.locator(
    `[data-actor-entry][data-entry-type="user"][data-entry-id="${firstObserverId}"][data-persisted="1"]`
  );
  const persistedSecondObserver = persistedObserverPanel.locator(
    `[data-actor-entry][data-entry-type="user"][data-entry-id="${secondObserverId}"][data-persisted="1"]`
  );

  await expect(persistedObserverPanel.locator('.badge.text-bg-warning')).toHaveCount(0);
  await expect(persistedObserverPanel.locator('[data-actor-entry]')).toHaveCount(2);
  await expect(persistedFirstObserver).toBeVisible();
  await expect(persistedSecondObserver).toBeVisible();

  await page.reload();
  const reloadedObserverPanel = await openObserverPanel(page);
  await expect(reloadedObserverPanel.locator('[data-actor-entry]')).toHaveCount(2);
  await expect(reloadedObserverPanel.locator(
    `[data-actor-entry][data-entry-type="user"][data-entry-id="${firstObserverId}"][data-persisted="1"]`
  )).toBeVisible();
  await expect(reloadedObserverPanel.locator(
    `[data-actor-entry][data-entry-type="user"][data-entry-id="${secondObserverId}"][data-persisted="1"]`
  )).toBeVisible();
});
