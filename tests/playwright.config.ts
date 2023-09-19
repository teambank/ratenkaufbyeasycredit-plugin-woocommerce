import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  outputDir: '../test-results/'+ process.env.VERSION + '/',
  use: {
    baseURL: process.env.BASE_URL ?? 'http://localhost',
    trace: 'on'
  },
  //timeout: 10 * 60 * 1000, // 10m
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    }
  ]
});
