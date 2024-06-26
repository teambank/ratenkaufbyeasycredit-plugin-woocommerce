import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  outputDir: '../test-results/'+ process.env.VERSION + '/',
  use: {
    baseURL: process.env.BASE_URL ?? 'http://localhost/',
    trace: 'on'
  },
  timeout: 5 * 60 * 1000, // 5m
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    }
  ],
  webServer: {
    command: 'PHP_CLI_SERVER_WORKERS=8 sudo php -S localhost:80 -t /opt/wordpress',
    url: 'http://localhost/',
    reuseExistingServer: !process.env.CI,
    stdout: 'ignore',
    stderr: 'pipe',
    timeout: 5 * 1000
  },
});
