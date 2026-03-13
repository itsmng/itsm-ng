import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { defineConfig } from '@playwright/test';

const dirname = path.dirname(fileURLToPath(import.meta.url));
const launchArgs = typeof process.getuid === 'function' && process.getuid() === 0 ? ['--no-sandbox'] : [];

export default defineConfig({
  testDir: path.join(dirname, 'spec'),
  testMatch: '*.spec.mts',
  timeout: 60_000,
  fullyParallel: false,
  workers: 1,
  retries: process.env.CI ? 2 : 0,
  reporter: [
    ['list'],
    ['html', { open: 'never', outputFolder: path.join(dirname, '../files/_playwright/report') }],
  ],
  outputDir: path.join(dirname, '../files/_playwright/test-results'),
  use: {
    baseURL: process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:8088',
    browserName: 'chromium',
    headless: true,
    launchOptions: {
      args: launchArgs,
    },
    screenshot: 'only-on-failure',
    trace: 'retain-on-failure',
    video: 'retain-on-failure',
  },
});
