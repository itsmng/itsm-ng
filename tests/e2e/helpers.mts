import { expect, type APIRequestContext, type APIResponse, type Locator, type Page } from '@playwright/test';

export interface SeedTicketOptions {
  withTaskState?: 'todo' | 'done';
}

export interface SeedTicketResult {
  ticketId: number;
  ticketName: string;
  ticketContent: string;
  ticketUrl: string;
  taskId?: number;
  taskContent?: string;
  taskState?: string;
}

interface ApiSession {
  apiUrl: string;
  sessionToken: string;
  userId: number;
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
  const response = await request.get(`${apiUrl}initSession`, {
    headers: {
      'App-Token': appToken,
    },
    params: {
      login: 'itsm',
      password: 'itsm',
    },
  });
  const data = await parseJsonResponse<{ session_token?: string }>(response, 'API session initialization');
  if (!data.session_token) {
    throw new Error('API session initialization did not return a session token.');
  }

  const sessionResponse = await request.get(`${apiUrl}getFullSession`, {
    headers: {
      'App-Token': appToken,
      'Session-Token': data.session_token,
    },
  });
  const sessionData = await parseJsonResponse<{ session?: { glpiID?: number | string } }>(
    sessionResponse,
    'Fetching API session details'
  );
  const userId = Number(sessionData.session?.glpiID);
  if (Number.isNaN(userId) || userId <= 0) {
    throw new Error(`API session details did not return a valid user id: ${JSON.stringify(sessionData)}`);
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

export async function fillRichTextForm(form: Locator, content: string): Promise<void> {
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

  await page.evaluate(
    ({ value, id }) => {
      const textareaElement = document.querySelector(`textarea[name="content"]${id ? `#${id}` : ''}`) as HTMLTextAreaElement | null;
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

export async function submitAddForm(form: Locator, page: Page): Promise<void> {
  await Promise.all([
    page.waitForLoadState('domcontentloaded'),
    form.locator('[name="add"]').click(),
  ]);
  await page.waitForLoadState('networkidle');
}
