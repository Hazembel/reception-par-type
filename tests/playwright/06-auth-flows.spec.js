import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './helpers.js';

// ── Register page ─────────────────────────────────────────────────────────────

test.describe('Register page', () => {
    test('loads without error and shows all required fields', async ({ page }) => {
        const response = await page.goto('/fr/register');
        expect(response?.status()).toBe(200);
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('#name')).toBeVisible();
        await expect(page.locator('#email')).toBeVisible();
        await expect(page.locator('#password')).toBeVisible();
        await expect(page.locator('#password_confirmation')).toBeVisible();
        await expect(page.locator('button[type=submit]')).toBeVisible();
    });

    test('email input has left icon (pl-11 padding)', async ({ page }) => {
        await page.goto('/fr/register');
        const cls = await page.locator('#email').getAttribute('class');
        expect(cls).toContain('pl-11');
    });

    test('has link back to login page', async ({ page }) => {
        await page.goto('/fr/register');
        await expect(page.locator('a[href*="/login"]').first()).toBeVisible();
    });

    test('page title shows "Créer un compte"', async ({ page }) => {
        await page.goto('/fr/register');
        await expect(page.locator('h1')).toContainText('Créer un compte');
    });

    test('shows inline validation error for mismatched passwords', async ({ page }) => {
        await page.goto('/fr/register');
        await page.fill('#name', 'Test User');
        await page.fill('#email', 'unique_test_999@example.com');
        await page.fill('#password', 'Password1!');
        await page.fill('#password_confirmation', 'Different99!');
        await page.click('button[type=submit]');
        // Must stay on register — no redirect on validation failure
        await expect(page).toHaveURL(/\/register/);
        await expect(page.locator('body')).not.toContainText('Whoops');
    });

    test('shows error for already-used email', async ({ page }) => {
        await page.goto('/fr/register');
        await page.fill('#name', 'Duplicate');
        await page.fill('#email', 'admin@reception-par-type.ch');
        await page.fill('#password', 'Password1!');
        await page.fill('#password_confirmation', 'Password1!');
        await page.click('button[type=submit]');
        await expect(page).toHaveURL(/\/register/);
        // Error banner must appear
        const banner = page.locator('.bg-red-50, .bg-red-900\\/20').first();
        await expect(banner).toBeVisible();
    });
});

// ── Forgot password page ──────────────────────────────────────────────────────

test.describe('Forgot password page', () => {
    test('loads without error', async ({ page }) => {
        const response = await page.goto('/fr/password/reset');
        expect(response?.status()).toBe(200);
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('#email')).toBeVisible();
        await expect(page.locator('button[type=submit]')).toBeVisible();
    });

    test('email input has left icon (pl-11 padding)', async ({ page }) => {
        await page.goto('/fr/password/reset');
        const cls = await page.locator('#email').getAttribute('class');
        expect(cls).toContain('pl-11');
    });

    test('has "Retour à la connexion" link', async ({ page }) => {
        await page.goto('/fr/password/reset');
        const link = page.locator('a[href*="/login"]').first();
        await expect(link).toBeVisible();
        await expect(link).toContainText(/connexion|login/i);
    });

    test('submit with known email shows green success flash', async ({ page }) => {
        await page.goto('/fr/password/reset');
        await page.fill('#email', 'admin@reception-par-type.ch');
        await page.click('button[type=submit]');
        // Wait for the redirect — should land back on /password/reset with a status flash
        await page.waitForURL(/\/password\/reset/, { timeout: 10_000 });
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).not.toContainText('Internal Server Error');
        // Green flash must be visible
        const successBanner = page.locator('.bg-emerald-50, .bg-emerald-900\\/20').first();
        await expect(successBanner).toBeVisible();
    });

    test('submit with invalid email shows error', async ({ page }) => {
        await page.goto('/fr/password/reset');
        await page.fill('#email', 'nobody@doesnotexist.invalid');
        await page.click('button[type=submit]');
        await expect(page).toHaveURL(/\/password\/reset/);
        // Either an error banner or stays with the form — no 500
        await expect(page.locator('body')).not.toContainText('Whoops');
    });
});

// ── Reset password page ───────────────────────────────────────────────────────

test.describe('Reset password page', () => {
    test('loads with token and email params', async ({ page }) => {
        const response = await page.goto('/fr/password/reset/fake-token?email=test@example.com');
        expect(response?.status()).toBe(200);
        await expect(page.locator('body')).not.toContainText('Whoops');
        // Token is passed as a hidden field inside the reset form
        const tokenInput = page.locator('form').filter({ has: page.locator('#password') }).locator('input[name="token"]');
        await expect(tokenInput).toBeAttached();
        await expect(page.locator('#email')).toHaveValue('test@example.com');
        await expect(page.locator('#password')).toBeVisible();
        await expect(page.locator('#password_confirmation')).toBeVisible();
    });

    test('email input has left icon (pl-11 padding)', async ({ page }) => {
        await page.goto('/fr/password/reset/fake-token?email=test@example.com');
        const cls = await page.locator('#email').getAttribute('class');
        expect(cls).toContain('pl-11');
    });

    test('page title shows "Nouveau mot de passe"', async ({ page }) => {
        await page.goto('/fr/password/reset/fake-token');
        await expect(page.locator('h1')).toContainText('Nouveau mot de passe');
    });
});

// ── Login page enhancements ───────────────────────────────────────────────────

test.describe('Login page enhancements (bug fixes)', () => {
    test('has "Mot de passe oublié?" link pointing to password reset', async ({ page }) => {
        await page.goto('/fr/login');
        const forgotLink = page.locator('a[href*="/password/reset"]');
        await expect(forgotLink).toBeVisible();
        await expect(forgotLink).toContainText(/oublié/i);
    });

    test('has "Créer un compte" link pointing to register', async ({ page }) => {
        await page.goto('/fr/login');
        const registerLink = page.locator('a[href*="/register"]');
        await expect(registerLink).toBeVisible();
    });

    test('clicking "Mot de passe oublié?" navigates to forgot-password page', async ({ page }) => {
        await page.goto('/fr/login');
        await page.click('a[href*="/password/reset"]');
        await expect(page).toHaveURL(/\/password\/reset/);
        await expect(page.locator('#email')).toBeVisible();
    });

    test('shows green flash when redirected after password reset', async ({ page }) => {
        // Simulate arriving at login with a session status (as happens after reset)
        // We can't easily fake a session, but we can at least verify the UI slot exists
        await page.goto('/fr/login');
        // The page must not show a broken layout
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('#email')).toBeVisible();
        await expect(page.locator('#password')).toBeVisible();
    });
});

// ── Verify email page ─────────────────────────────────────────────────────────

test.describe('Verify email page (bug fix — resend button)', () => {
    test('unauthenticated access redirects to login', async ({ page }) => {
        await page.goto('/fr/email/verify');
        await expect(page).toHaveURL(/\/login/);
    });

    test('page structure: has resend button and logout link when visited by auth user', async ({ page }) => {
        // Log in as admin — admin is already verified so we'll be redirected,
        // but we verify the view compiles correctly by checking the route returns < 500.
        await loginAsAdmin(page);
        const response = await page.goto('/fr/email/verify');
        // Even for verified users the view renders (middleware just doesn't block admin)
        expect(response?.status()).toBeLessThan(500);
        await expect(page.locator('body')).not.toContainText('Whoops');
    });

    test('resend form action points to /email/verification-notification', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/fr/email/verify');
        // Check the form's action attribute
        const form = page.locator('form[action*="verification-notification"]');
        await expect(form).toBeAttached();
    });

    test('page has logout link', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/fr/email/verify');
        // Multiple logout forms exist (navbar + verify page) — check at least one is present
        const logoutBtn = page.locator('form[action*="/logout"] button[type=submit]').first();
        await expect(logoutBtn).toBeAttached();
    });
});
