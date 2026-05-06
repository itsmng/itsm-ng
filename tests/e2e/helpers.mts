import { readFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { expect, type APIRequestContext, type APIResponse, type Locator, type Page } from '@playwright/test';

export interface SeedTicketOptions {
  withTaskState?: 'todo' | 'done';
}

export interface SeedTicketResult {
  ticketId: number;
  ticketName: string;
  ticketContent: string;
  ticketUrl: string;
  userId: number;
  taskId?: number;
  taskContent?: string;
  taskState?: string;
}

export type ActorPanelRole = 'requester' | 'observer' | 'assign';

export interface SeedReservationOptions {
  begin?: string;
  end?: string;
  reservationItemId?: number;
  computerId?: number;
  computerName?: string;
}

export interface SeedReservationResult {
  computerId: number;
  computerName: string;
  reservationItemId: number;
  reservationId: number;
  reservationBegin: string;
  reservationEnd: string;
  reservationComment: string;
  userId: number;
}

export interface ReservationApiItem {
  id: number | string;
  begin?: string;
  end?: string;
  reservationitems_id?: number | string;
}

export interface SeedAppointmentScheduleOptions {
  day?: number;
  availabilityBegin?: string;
  availabilityEnd?: string;
  exceptionBegin?: string;
  exceptionEnd?: string;
}

export interface SeedAppointmentScheduleResult {
  groupId: number;
  targetId: number;
  availabilityId: number;
  exceptionId: number;
  day: number;
  date: string;
  userId: number;
}

export interface SeedAppointmentOptions extends SeedAppointmentScheduleOptions {
  name?: string;
  begin?: string;
  end?: string;
  requesterId?: number;
}

export interface SeedAppointmentResult extends SeedAppointmentScheduleResult {
  appointmentId: number;
  appointmentName: string;
  appointmentBegin: string;
  appointmentEnd: string;
}

interface ApiSession {
  apiUrl: string;
  sessionToken: string;
  userId: number;
}

interface RichTextContext {
  editorId: string | null;
  page: Page;
  textarea: Locator;
}

const dirname = path.dirname(fileURLToPath(import.meta.url));
const repoRoot = path.resolve(dirname, '..', '..');

function resolveRepoPath(relativePath: string): string {
  return path.resolve(repoRoot, relativePath);
}

function getMimeType(filePath: string): string {
  const extension = path.extname(filePath).toLowerCase();
  switch (extension) {
    case '.png':
      return 'image/png';
    case '.jpg':
    case '.jpeg':
      return 'image/jpeg';
    case '.gif':
      return 'image/gif';
    case '.webp':
      return 'image/webp';
    default:
      throw new Error(`Unsupported fixture type for rich text upload: ${filePath}`);
  }
}

function getAppToken(): string {
  const appToken = process.env.PLAYWRIGHT_APP_TOKEN;
  if (!appToken) {
    throw new Error('Playwright app token is required for E2E API seeding.');
  }

  return appToken;
}

function getApiUrl(_request: APIRequestContext): string {
  const baseURL = process.env.PLAYWRIGHT_BASE_URL;
  if (!baseURL) {
    throw new Error('Playwright baseURL is required for E2E API seeding.');
  }

  return new URL('/apirest.php/', baseURL).toString();
}

async function parseJsonResponse<T>(response: APIResponse, context: string): Promise<T> {
  const body = await response.text();
  if (!response.ok()) {
    throw new Error(`${context} failed with ${response.status()}: ${body}`);
  }

  try {
    return JSON.parse(body) as T;
  } catch (error) {
    throw new Error(`${context} returned invalid JSON: ${body}`, { cause: error });
  }
}

async function initApiSession(request: APIRequestContext): Promise<ApiSession> {
  const apiUrl = getApiUrl(request);
  const appToken = getAppToken();
  const credentials = Buffer.from('itsm:itsm').toString('base64');
  const response = await request.get(`${apiUrl}initSession`, {
    headers: {
      'App-Token': appToken,
      Authorization: `Basic ${credentials}`,
    },
    params: {
      get_full_session: 'true',
    },
  });
  const data = await parseJsonResponse<{ session_token?: string; session?: { glpiID?: number | string } }>(
    response,
    'API session initialization'
  );
  if (!data.session_token) {
    throw new Error(`API session initialization did not return a session token: ${JSON.stringify(data)}`);
  }

  const userId = Number(data.session?.glpiID);
  if (Number.isNaN(userId) || userId <= 0) {
    throw new Error(`API session initialization did not return a valid user id: ${JSON.stringify(data)}`);
  }

  return { apiUrl, sessionToken: data.session_token, userId };
}

async function closeApiSession(request: APIRequestContext, session: ApiSession): Promise<void> {
  const response = await request.get(`${session.apiUrl}killSession`, {
    headers: {
      'App-Token': getAppToken(),
      'Session-Token': session.sessionToken,
    },
  });

  if (!response.ok()) {
    const body = await response.text();
    throw new Error(`API session shutdown failed with ${response.status()}: ${body}`);
  }
}

async function createItem(
  request: APIRequestContext,
  session: ApiSession,
  itemtype: string,
  input: Record<string, unknown>
): Promise<number> {
  const response = await request.post(`${session.apiUrl}${itemtype}/`, {
    headers: {
      'App-Token': getAppToken(),
      'Content-Type': 'application/json',
      'Session-Token': session.sessionToken,
    },
    data: {
      input,
    },
  });

  const data = await parseJsonResponse<{ id?: number | string; message?: string }>(response, `Creating ${itemtype}`);
  if (data.id === undefined || Number.isNaN(Number(data.id))) {
    throw new Error(`Creating ${itemtype} did not return a valid id: ${JSON.stringify(data)}`);
  }

  return Number(data.id);
}

async function getItem<T>(
  request: APIRequestContext,
  session: ApiSession,
  itemtype: string,
  id: number
): Promise<T> {
  const response = await request.get(`${session.apiUrl}${itemtype}/${id}`, {
    headers: {
      'App-Token': getAppToken(),
      'Session-Token': session.sessionToken,
    },
  });

  return parseJsonResponse<T>(response, `Fetching ${itemtype}#${id}`);
}

export async function seedTicket(request: APIRequestContext, options: SeedTicketOptions = {}): Promise<SeedTicketResult> {
  const session = await initApiSession(request);

  try {
    const suffix = `${Date.now()}-${Math.random().toString(16).slice(2, 10)}`;
    const ticketName = `E2E Ticket ${suffix}`;
    const ticketContent = `Seeded ticket description ${suffix}`;
    const ticketId = await createItem(request, session, 'Ticket', {
      name: ticketName,
      content: ticketContent,
      description: ticketContent,
      _users_id_requester: session.userId,
      _users_id_assign: session.userId,
    });

    const result: SeedTicketResult = {
      ticketId,
      ticketName,
      ticketContent,
      ticketUrl: `/front/ticket.form.php?id=${ticketId}`,
      userId: session.userId,
    };

    if (options.withTaskState) {
      const taskStateMap = {
        todo: 1,
        done: 2,
      } as const;
      const taskContent = `Seeded task ${suffix}`;
      const taskId = await createItem(request, session, 'TicketTask', {
        tickets_id: ticketId,
        content: taskContent,
        state: taskStateMap[options.withTaskState],
        users_id_tech: session.userId,
      });
      const task = await getItem<{ content?: string; state?: number | string }>(request, session, 'TicketTask', taskId);

      result.taskId = taskId;
      result.taskContent = task.content ?? taskContent;
      result.taskState = String(task.state ?? taskStateMap[options.withTaskState]);
    }

    return result;
  } finally {
    await closeApiSession(request, session);
  }
}

export async function seedReservation(
  request: APIRequestContext,
  options: SeedReservationOptions = {}
): Promise<SeedReservationResult> {
  const session = await initApiSession(request);

  try {
    const suffix = `${Date.now()}-${Math.random().toString(16).slice(2, 10)}`;
    const computerName = options.computerName ?? `E2E Reservable Computer ${suffix}`;
    const reservationBegin = options.begin ?? '2030-05-01 10:00:00';
    const reservationEnd = options.end ?? '2030-05-01 11:00:00';
    const reservationComment = `E2E reservation ${suffix}`;

    const computerId = options.computerId ?? (await createItem(request, session, 'Computer', {
      name: computerName,
      entities_id: 0,
      is_recursive: 0,
    }));

    const reservationItemId = options.reservationItemId ?? (await createItem(request, session, 'ReservationItem', {
      itemtype: 'Computer',
      items_id: computerId,
      entities_id: 0,
      is_recursive: 0,
      is_active: 1,
      is_deleted: 0,
      comment: reservationComment,
    }));

    const reservationId = await createItem(request, session, 'Reservation', {
      reservationitems_id: reservationItemId,
      begin: reservationBegin,
      end: reservationEnd,
      users_id: session.userId,
      comment: reservationComment,
      _ajax_reservation: 1,
    });

    return {
      computerId,
      computerName,
      reservationItemId,
      reservationId,
      reservationBegin,
      reservationEnd,
      reservationComment,
      userId: session.userId,
    };
  } finally {
    await closeApiSession(request, session);
  }
}

export async function getReservation(request: APIRequestContext, reservationId: number): Promise<ReservationApiItem> {
  const session = await initApiSession(request);

  try {
    return await getItem<ReservationApiItem>(request, session, 'Reservation', reservationId);
  } finally {
    await closeApiSession(request, session);
  }
}

export async function seedAppointmentSchedule(
  request: APIRequestContext,
  options: SeedAppointmentScheduleOptions = {}
): Promise<SeedAppointmentScheduleResult> {
  const session = await initApiSession(request);

  try {
    const suffix = `${Date.now()}-${Math.random().toString(16).slice(2, 10)}`;
    const day = options.day ?? new Date().getDay();
    const groupId = await createItem(request, session, 'Group', {
      name: `E2E Appointment Group ${suffix}`,
      entities_id: 0,
      is_recursive: 0,
    });
    const targetId = await createItem(request, session, 'AppointmentTarget', {
      itemtype: 'Group',
      items_id: groupId,
      entities_id: 0,
      is_recursive: 0,
      is_active: 1,
      is_deleted: 0,
      comment: `E2E appointment target ${suffix}`,
    });
    const availabilityId = await createItem(request, session, 'AppointmentAvailability', {
      appointmenttargets_id: targetId,
      day,
      begin: options.availabilityBegin ?? '09:00:00',
      end: options.availabilityEnd ?? '17:00:00',
    });

    const today = new Date();
    const selectedDate = new Date(today);
    selectedDate.setDate(today.getDate() + ((day - today.getDay() + 7) % 7));
    const date = selectedDate.toISOString().slice(0, 10);
    const exceptionId = await createItem(request, session, 'AppointmentAvailabilityException', {
      appointmenttargets_id: targetId,
      plan: {
        begin: options.exceptionBegin ?? `${date} 13:00:00`,
        end: options.exceptionEnd ?? `${date} 14:00:00`,
      },
      is_available: 0,
      comment: `E2E unavailable block ${suffix}`,
    });

    return {
      groupId,
      targetId,
      availabilityId,
      exceptionId,
      day,
      date,
      userId: session.userId,
    };
  } finally {
    await closeApiSession(request, session);
  }
}

export async function seedAppointment(
  request: APIRequestContext,
  options: SeedAppointmentOptions = {}
): Promise<SeedAppointmentResult> {
  const schedule = await seedAppointmentSchedule(request, {
    ...options,
    exceptionBegin: options.exceptionBegin ?? `${localAppointmentDate(options.day)} 16:00:00`,
    exceptionEnd: options.exceptionEnd ?? `${localAppointmentDate(options.day)} 17:00:00`,
  });
  const session = await initApiSession(request);

  try {
    const suffix = `${Date.now()}-${Math.random().toString(16).slice(2, 10)}`;
    const appointmentName = options.name ?? `E2E Appointment ${suffix}`;
    const appointmentBegin = options.begin ?? `${schedule.date} 10:00:00`;
    const appointmentEnd = options.end ?? `${schedule.date} 11:00:00`;
    const appointmentId = await createItem(request, session, 'Appointment', {
      name: appointmentName,
      appointmenttargets_id: schedule.targetId,
      users_id_requester: options.requesterId ?? session.userId,
      plan: {
        begin: appointmentBegin,
        end: appointmentEnd,
      },
      text: `E2E appointment body ${suffix}`,
    });

    return {
      ...schedule,
      appointmentId,
      appointmentName,
      appointmentBegin,
      appointmentEnd,
      userId: session.userId,
    };
  } finally {
    await closeApiSession(request, session);
  }
}

function localAppointmentDate(day = new Date().getDay()): string {
  const today = new Date();
  const selectedDate = new Date(today);
  selectedDate.setDate(today.getDate() + ((day - today.getDay() + 7) % 7));
  return selectedDate.toISOString().slice(0, 10);
}

function getCollectionItems(data: unknown): Array<Record<string, unknown>> {
  if (Array.isArray(data)) {
    return data.filter((item): item is Record<string, unknown> => typeof item === 'object' && item !== null);
  }

  if (typeof data !== 'object' || data === null) {
    return [];
  }

  return Object.entries(data)
    .filter(([key, value]) => /^\d+$/.test(key) && typeof value === 'object' && value !== null)
    .map(([, value]) => value as Record<string, unknown>);
}

export async function findTicketIdByName(request: APIRequestContext, ticketName: string): Promise<number | null> {
  const session = await initApiSession(request);

  try {
    const response = await request.get(`${session.apiUrl}Ticket/`, {
      headers: {
        'App-Token': getAppToken(),
        'Session-Token': session.sessionToken,
      },
      params: {
        'searchText[name]': ticketName,
      },
    });
    const data = await parseJsonResponse<unknown>(response, `Finding ticket "${ticketName}"`);
    const match = getCollectionItems(data).find((item) => item.name === ticketName);

    if (match?.id === undefined || Number.isNaN(Number(match.id))) {
      return null;
    }

    return Number(match.id);
  } finally {
    await closeApiSession(request, session);
  }
}

export async function waitForTicketIdByName(
  request: APIRequestContext,
  ticketName: string,
  timeoutMs = 10_000
): Promise<number> {
  const deadline = Date.now() + timeoutMs;

  while (Date.now() <= deadline) {
    const ticketId = await findTicketIdByName(request, ticketName);
    if (ticketId !== null) {
      return ticketId;
    }

    await new Promise((resolve) => setTimeout(resolve, 250));
  }

  throw new Error(`Unable to find ticket "${ticketName}" within ${timeoutMs}ms.`);
}

export async function login(page: Page): Promise<void> {
  await page.goto('/index.php');
  await page.locator('#login_name').fill('itsm');
  await page.locator('#login_password').fill('itsm');
  await page.locator('form[aria-label="Login Form"] input[type="submit"]').click();
  await page.waitForLoadState('networkidle');
}

export async function openTicket(page: Page, seed: SeedTicketResult): Promise<void> {
  await page.goto(seed.ticketUrl);
  await expect(page.getByTestId('timeline-history')).toBeVisible();
}

async function getRichTextContext(form: Locator): Promise<RichTextContext> {
  const textarea = form.locator('textarea[name="content"]').first();
  await expect(textarea).toBeAttached();
  const editorId = await textarea.getAttribute('id');
  const page = form.page();

  if (editorId) {
    await page.waitForFunction(
      (id) => {
        const editorRegistry = window as unknown as Record<string, unknown>;
        const ckEditor = editorRegistry[id];
        const tinyMceEditor = (window as Window & { tinymce?: { get: (editorId: string) => unknown } }).tinymce?.get(id);

        return ckEditor !== undefined || (tinyMceEditor !== undefined && tinyMceEditor !== null);
      },
      editorId,
      { timeout: 5_000 }
    ).catch(() => undefined);
  }

  return { editorId, page, textarea };
}

export function getActorPanel(page: Page, role: ActorPanelRole): Locator {
  return page.locator(`.itil-actor-card[data-actor-role="${role}"]`).first();
}

export async function waitForActorValueSelect(panel: Locator): Promise<Locator> {
  const selector = panel.locator('[data-role="selector-container"] select').first();
  await expect(selector).toBeAttached();
  return selector;
}

export async function submitForm(
  page: Page,
  submitName: 'add' | 'update',
  root: Locator | Page = page
): Promise<void> {
  const submitButton = root.locator(`button[name="${submitName}"], input[name="${submitName}"]:not([type="hidden"])`).first();
  await expect(submitButton).toBeAttached();
  await Promise.all([
    page.waitForLoadState('domcontentloaded'),
    submitButton.click(),
  ]);
  await page.waitForLoadState('networkidle');
}

export async function fillRichTextForm(form: Locator, content: string): Promise<void> {
  const { editorId, page } = await getRichTextContext(form);

  await page.evaluate(
    ({ value, id }) => {
      const textareaById = id ? document.getElementById(id) : null;
      const textareaElement = textareaById instanceof HTMLTextAreaElement
        ? textareaById
        : document.querySelector('textarea[name="content"]') as HTMLTextAreaElement | null;
      if (!textareaElement) {
        throw new Error('Unable to find the timeline content textarea.');
      }

      const ckEditor = id
        ? (window as unknown as Record<string, unknown>)[id] as { setData?: (html: string) => void; getData?: () => string } | undefined
        : undefined;
      const tinyMceEditor = id && 'tinymce' in window
        ? (window as Window & { tinymce?: { get: (editorId: string) => { setContent: (html: string) => void; save: () => void } | null } }).tinymce?.get(id)
        : null;

      if (ckEditor?.setData) {
        ckEditor.setData(value);
        textareaElement.value = ckEditor.getData?.() ?? value;
      } else if (tinyMceEditor) {
        tinyMceEditor.setContent(value);
        tinyMceEditor.save();
      } else {
        textareaElement.value = value;
      }

      textareaElement.dispatchEvent(new Event('input', { bubbles: true }));
      textareaElement.dispatchEvent(new Event('change', { bubbles: true }));
    },
    { value: content, id: editorId }
  );
}

export async function uploadRichTextFixture(form: Locator, fixtureRelativePath: string): Promise<void> {
  const fixturePath = resolveRepoPath(fixtureRelativePath);
  const fileBuffer = await readFile(fixturePath);
  const { editorId, page } = await getRichTextContext(form);
  const uploadResponsePromise = page.waitForResponse((response) => {
    return response.url().includes('/ajax/v2/richtext_image_upload.php')
      && response.request().method() === 'POST';
  });

  await page.evaluate(
    ({ id, fileName, fileBase64, mimeType }) => {
      if (!id) {
        throw new Error('Unable to resolve the rich text editor id.');
      }

      const editor = (window as unknown as Record<string, unknown>)[id] as
        | { ui?: { getEditableElement?: () => HTMLElement | null } }
        | undefined;

      if (!editor?.ui?.getEditableElement) {
        throw new Error(`Unable to resolve CKEditor instance "${id}".`);
      }

      const editable = editor.ui.getEditableElement();
      if (!(editable instanceof HTMLElement)) {
        throw new Error('Unable to resolve the CKEditor editable element.');
      }

      const binary = atob(fileBase64);
      const bytes = Uint8Array.from(binary, (character) => character.charCodeAt(0));
      const file = new File([bytes], fileName, { type: mimeType });
      const dataTransfer = new DataTransfer();
      dataTransfer.items.add(file);

      editable.focus();

      ['dragenter', 'dragover', 'drop'].forEach((type) => {
        const event = typeof DragEvent === 'function'
          ? new DragEvent(type, {
              bubbles: true,
              cancelable: true,
              dataTransfer,
            })
          : new Event(type, {
              bubbles: true,
              cancelable: true,
            });

        if (!(event instanceof DragEvent)) {
          Object.defineProperty(event, 'dataTransfer', { value: dataTransfer });
        }

        editable.dispatchEvent(event);
      });
    },
    {
      id: editorId,
      fileName: path.basename(fixturePath),
      fileBase64: fileBuffer.toString('base64'),
      mimeType: getMimeType(fixturePath),
    }
  );

  const uploadResponse = await uploadResponsePromise;
  expect(uploadResponse.ok()).toBeTruthy();

  const uploadPayload = await uploadResponse.json() as {
    filename?: string;
    prefix?: string;
    tag?: string;
  };
  expect(typeof uploadPayload.filename).toBe('string');
  expect(typeof uploadPayload.prefix).toBe('string');
  expect(typeof uploadPayload.tag).toBe('string');

  await expect(form.locator('input[name^="_content["]')).toHaveCount(1);
  await expect(form.locator('input[name^="_prefix_content["]')).toHaveCount(1);
  await expect(form.locator('input[name^="_tag_content["]')).toHaveCount(1);
  await expect(form.locator('.ck-content img[src^="blob:"]').first()).toBeVisible();
}

export async function getInnerHtml(locator: Locator): Promise<string> {
  return locator.evaluate((element) => element.innerHTML);
}

export async function submitAddForm(form: Locator, page: Page): Promise<void> {
  await submitForm(page, 'add', form);
}
