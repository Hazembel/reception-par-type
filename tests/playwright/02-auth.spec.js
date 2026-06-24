import { test, expect } from '@playwright/test';
import { ADMIN_EMAIL, ADMIN_PASSWORD, loginAsAdmin } from './helpers.js';

// ── Authentication flows ───────────────────────────────────────────────────────

test.describe('Login page', () => {
    test('shows login form', async ({ page }) => {
        await page.goto('/fr/login');
        await expect(page.locator('#email')).toBeVisible();
        await expect(page.locator('#password')).toBeVisible();
        await expect(page.locator('button[type=submit]')).toBeVisible();
    });

    test('password toggle shows/hides password', async ({ page }) => {
        await page.goto('/fr/login');
        const input = page.locator('#password');
        await expect(input).toHaveAttribute('type', 'password');

        // Use the show/hide button by its specific aria-label
        const toggle = page.getByRole('button', { name: /afficher|masquer/i });
        await toggle.click();
        await expect(input).toHaveAttribute('type', 'text');

        await toggle.click();
        await expect(input).toHaveAttribute('type', 'password');
    });

    test('wrong credentials shows error', async ({ page }) => {
        await page.goto('/fr/login');
        await page.fill('#email', 'nobody@example.com');
        await page.fill('#password', 'wrongpassword');
        await page.click('button[type=submit]');

        await expect(page.locator('body')).toContainText(/credentials|incorrect|invalid/i);
        // Must still be on login page
        await expect(page).toHaveURL(/\/login/);
    });

    test('empty fields show validation error', async ({ page }) => {
        await page.goto('/fr/login');
        await page.click('button[type=submit]');
        // Either HTML5 required stops it or server returns 422
        const url = page.url();
        expect(url).toMatch(/\/login/);
    });
});

test.describe('Admin login', () => {
    test('admin credentials redirect to /admin/dashboard', async ({ page }) => {
        await loginAsAdmin(page);
        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).not.toContainText('403');
    });
});

test.describe('Logout', () => {
    test('logout ends session and redirects to /', async ({ page }) => {
        await loginAsAdmin(page);

        // Admin layout uses a link that submits a hidden form (#ps-logout-form)
        await page.evaluate(() => {
            const form = document.getElementById('ps-logout-form');
            if (form) form.submit();
        });

        // Should land somewhere public (root redirect)
        await page.waitForURL(/^http:\/\/localhost:8000\/(fr|de|it|en)?\/?$/, { timeout: 8_000 });

        // Attempting to access admin should now redirect to login
        await page.goto('/admin/dashboard');
        await expect(page).toHaveURL(/\/login/);
    });
});

test.describe('Protected routes without auth', () => {
    test('/admin/dashboard redirects to login when not authenticated', async ({ page }) => {
        await page.goto('/admin/dashboard');
        await expect(page).toHaveURL(/\/login/);
    });

    test('/fr/account/invoices redirects to login when not authenticated', async ({ page }) => {
        const response = await page.goto('/fr/account/invoices');
        // Either 302 → login, or already on login page
        await expect(page).toHaveURL(/\/login/);
    });
});
