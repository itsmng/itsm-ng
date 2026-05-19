import { expect, test, type Page } from '@playwright/test';
import { login, seedAppointment, seedAppointmentSchedule } from '../helpers.mjs';

function localDateForWeekday(day: number): string {
  const date = new Date();
  date.setDate(date.getDate() + ((day - date.getDay() + 7) % 7));

  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const dayOfMonth = String(date.getDate()).padStart(2, '0');

  return `${year}-${month}-${dayOfMonth}`;
}

async function selectAppointmentCalendarSlot(page: Page, date: string, time: string): Promise<void> {
  const header = page.locator(`#appointment-calendar .fc-day-header[data-date="${date}"]`).first();
  const slot = page.locator(`#appointment-calendar .fc-time-grid .fc-slats tr[data-time="${time}"]`).first();
  await expect(header).toBeVisible({ timeout: 15_000 });
  await expect(slot).toBeVisible({ timeout: 15_000 });

  const headerBox = await header.boundingBox();
  const slotBox = await slot.boundingBox();
  if (!headerBox || !slotBox) {
    throw new Error('Unable to resolve appointment calendar slot geometry.');
  }

  const slotHeight = slotBox.height || 20;
  const x = headerBox.x + headerBox.width / 2;
  const startY = slotBox.y + 4;
  const endY = startY + slotHeight;

  await page.mouse.move(x, startY);
  await page.mouse.down();
  await page.mouse.move(x, endY, { steps: 8 });
  await page.mouse.up();
}

async function expectNoAppointmentDialog(page: Page): Promise<void> {
  await expect(page.locator('.ui-dialog-title', { hasText: 'Appointment' })).toHaveCount(0);
}

test.beforeEach(async ({ page }) => {
  await login(page);
});

test('opens booking form only for selectable appointment availability', async ({ page, request }) => {
  const seed = await seedAppointmentSchedule(request);
  const date = localDateForWeekday(seed.day);

  await page.goto(`/front/appointment.php?appointmenttargets_id=${seed.targetId}`);
  await expect(page.locator('#appointment-calendar')).toBeVisible();
  await expect(page.locator('[data-appointment-event-type="availability"]')).toHaveCount(1);
  await expect(page.locator('[data-appointment-event-type="exception"]')).toHaveCount(1);

  await selectAppointmentCalendarSlot(page, date, '08:00:00');
  await expectNoAppointmentDialog(page);

  await selectAppointmentCalendarSlot(page, date, '13:00:00');
  await expectNoAppointmentDialog(page);

  await selectAppointmentCalendarSlot(page, date, '10:00:00');
  await expect(page.locator('.ui-dialog-title', { hasText: 'Appointment' })).toBeVisible();
  await expect(page.locator('.appointment-calendar-dialog form')).toBeVisible();
});

test('shows requester and title for visible booked appointments', async ({ page, request }) => {
  const seed = await seedAppointment(request, {
    name: 'E2E Descriptive appointment title',
  });

  await page.goto(`/front/appointment.php?appointmenttargets_id=${seed.targetId}`);
  await expect(page.locator('#appointment-calendar')).toBeVisible();

  const appointment = page.locator(`[data-appointment-id="${seed.appointmentId}"]`).first();
  await expect(appointment).toBeVisible({ timeout: 15_000 });
  await expect(appointment).toContainText(`itsm - ${seed.appointmentName}`);
});
