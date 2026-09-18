import { expect, test, type Page } from '@playwright/test';
import { getReservation, login, seedReservation } from '../helpers.mjs';

async function dragReservationEventBySlots(page: Page, reservationId: number, slotCount: number): Promise<void> {
  const event = page.locator(`[data-testid="reservation-calendar-event"][data-reservation-id="${reservationId}"]`).first();
  await expect(event).toBeVisible({ timeout: 15_000 });

  const slotHeight = await getCalendarSlotHeight(page);
  const eventBox = await event.boundingBox();

  if (!eventBox) {
    throw new Error('Unable to resolve reservation event geometry for drag test.');
  }

  const startX = eventBox.x + eventBox.width / 2;
  const startY = eventBox.y + Math.min(eventBox.height / 2, 12);
  const endY = startY + slotHeight * slotCount;

  await page.mouse.move(startX, startY);
  await page.mouse.down();
  await page.mouse.move(startX, endY, { steps: 12 });
  await page.mouse.up();
}

async function resizeReservationEventBySlots(page: Page, reservationId: number, slotCount: number): Promise<void> {
  const event = page.locator(`[data-testid="reservation-calendar-event"][data-reservation-id="${reservationId}"]`).first();
  await expect(event).toBeVisible({ timeout: 15_000 });

  const slotHeight = await getCalendarSlotHeight(page);
  const eventBox = await event.boundingBox();

  if (!eventBox) {
    throw new Error('Unable to resolve reservation event geometry for resize test.');
  }

  const startX = eventBox.x + eventBox.width / 2;
  const startY = eventBox.y + eventBox.height - 2;
  const endY = startY + slotHeight * slotCount;

  await page.mouse.move(startX, startY);
  await page.mouse.down();
  await page.mouse.move(startX, endY, { steps: 12 });
  await page.mouse.up();
}

async function getCalendarSlotHeight(page: Page): Promise<number> {
  const slotRows = page.locator('#reservation-calendar .fc-time-grid .fc-slats tr');
  const firstSlot = await slotRows.nth(0).boundingBox();
  const secondSlot = await slotRows.nth(1).boundingBox();

  if (!firstSlot || !secondSlot) {
    throw new Error('Unable to resolve reservation calendar slot geometry.');
  }

  return secondSlot.y - firstSlot.y;
}

function waitForReservationUpdate(page: Page) {
  return page.waitForResponse(async (response) => {
    if (!response.url().includes('/ajax/reservation.php') || response.request().method() !== 'POST') {
      return false;
    }

    return response.request().postData()?.includes('action=update_times') ?? false;
  });
}

test.beforeEach(async ({ page }) => {
  await login(page);
});

test('persists drag and drop changes for editable reservation events', async ({ page, request }) => {
  const seed = await seedReservation(request, {
    begin: '2030-05-01 10:00:00',
    end: '2030-05-01 11:00:00',
  });

  await page.goto(`/front/reservation.php?reservationitems_id=${seed.reservationItemId}&mois_courant=5&annee_courante=2030`);
  await expect(page.locator('#reservation-calendar')).toBeVisible();

  const updateResponsePromise = waitForReservationUpdate(page);

  await dragReservationEventBySlots(page, seed.reservationId, 2);

  const updateResponse = await updateResponsePromise;
  expect(updateResponse.ok()).toBeTruthy();
  await expect.poll(async () => (await getReservation(request, seed.reservationId)).begin).toBe('2030-05-01 11:00:00');
  await expect.poll(async () => (await getReservation(request, seed.reservationId)).end).toBe('2030-05-01 12:00:00');

  await page.reload();
  await expect(page.locator(`[data-testid="reservation-calendar-event"][data-reservation-id="${seed.reservationId}"]`)).toBeVisible();
  const persistedReservation = await getReservation(request, seed.reservationId);
  expect(persistedReservation.begin).toBe('2030-05-01 11:00:00');
  expect(persistedReservation.end).toBe('2030-05-01 12:00:00');
});

test('rejects drag and drop changes that overlap another reservation', async ({ page, request }) => {
  const seed = await seedReservation(request, {
    begin: '2030-05-01 10:00:00',
    end: '2030-05-01 11:00:00',
  });
  await seedReservation(request, {
    reservationItemId: seed.reservationItemId,
    computerId: seed.computerId,
    computerName: seed.computerName,
    begin: '2030-05-01 12:00:00',
    end: '2030-05-01 13:00:00',
  });

  await page.goto(`/front/reservation.php?reservationitems_id=${seed.reservationItemId}&mois_courant=5&annee_courante=2030`);
  await expect(page.locator('#reservation-calendar')).toBeVisible();

  const updateResponsePromise = waitForReservationUpdate(page);
  await dragReservationEventBySlots(page, seed.reservationId, 4);

  const updateResponse = await updateResponsePromise;
  expect(updateResponse.ok()).toBeTruthy();
  expect(await updateResponse.json()).toMatchObject({ success: false });
  await expect.poll(async () => (await getReservation(request, seed.reservationId)).begin).toBe(seed.reservationBegin);
  await expect.poll(async () => (await getReservation(request, seed.reservationId)).end).toBe(seed.reservationEnd);

  await page.reload();
  await expect(page.locator(`[data-testid="reservation-calendar-event"][data-reservation-id="${seed.reservationId}"]`)).toBeVisible();
  const persistedReservation = await getReservation(request, seed.reservationId);
  expect(persistedReservation.begin).toBe(seed.reservationBegin);
  expect(persistedReservation.end).toBe(seed.reservationEnd);
});

test('rejects resize changes that overlap another reservation', async ({ page, request }) => {
  const seed = await seedReservation(request, {
    begin: '2030-05-01 10:00:00',
    end: '2030-05-01 11:00:00',
  });
  await seedReservation(request, {
    reservationItemId: seed.reservationItemId,
    computerId: seed.computerId,
    computerName: seed.computerName,
    begin: '2030-05-01 12:00:00',
    end: '2030-05-01 13:00:00',
  });

  await page.goto(`/front/reservation.php?reservationitems_id=${seed.reservationItemId}&mois_courant=5&annee_courante=2030`);
  await expect(page.locator('#reservation-calendar')).toBeVisible();

  const updateResponsePromise = waitForReservationUpdate(page);
  await resizeReservationEventBySlots(page, seed.reservationId, 3);

  const updateResponse = await updateResponsePromise;
  expect(updateResponse.ok()).toBeTruthy();
  expect(await updateResponse.json()).toMatchObject({ success: false });
  await expect.poll(async () => (await getReservation(request, seed.reservationId)).begin).toBe(seed.reservationBegin);
  await expect.poll(async () => (await getReservation(request, seed.reservationId)).end).toBe(seed.reservationEnd);

  await page.reload();
  await expect(page.locator(`[data-testid="reservation-calendar-event"][data-reservation-id="${seed.reservationId}"]`)).toBeVisible();
  const persistedReservation = await getReservation(request, seed.reservationId);
  expect(persistedReservation.begin).toBe(seed.reservationBegin);
  expect(persistedReservation.end).toBe(seed.reservationEnd);
});
