// Shared helpers for all Playwright tests

export const ADMIN_EMAIL    = process.env.ADMIN_EMAIL    ?? 'admin@reception-par-type.ch';
export const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD ?? 'AdminTest2024!';
export const BASE_URL       = process.env.APP_URL        ?? 'http://localhost:8000';

/**
 * Log in as admin and land on the admin dashboard.
 * Reusable across test files via storageState or direct call.
 */
export async function loginAsAdmin(page) {
    await page.goto('/fr/login');
    await page.fill('#email', ADMIN_EMAIL);
    await page.fill('#password', ADMIN_PASSWORD);
    await page.click('button[type=submit]');
    await page.waitForURL('**/admin/dashboard', { timeout: 10_000 });
}

/**
 * Assert that a response-level redirect happened (checking final URL).
 */
export async function assertRedirectsTo(page, urlPattern) {
    await page.waitForURL(urlPattern, { timeout: 8_000 });
}
