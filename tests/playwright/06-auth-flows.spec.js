import { test, expect } from '@playwright/test';
import { ADMIN_EMAIL, ADMIN_PASSWORD, loginAsAdmin } from './helpers.js';

// ── Register page ─────────────────────────────────────────────────────────────

test.describe('Register — page structure', () => {
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

    test('page title shows "Créer un compte"', async ({ page }) => {
        await page.goto('/fr/register');
        await expect(page.locator('h1')).toContainText('Créer un compte');
    });

    test('email input has icon (pl-11 padding)', async ({ page }) => {
        await page.goto('/fr/register');
        const cls = await page.locator('#email').getAttribute('class');
        expect(cls).toContain('pl-11');
    });

    test('has link back to login page', async ({ page }) => {
        await page.goto('/fr/register');
        await expect(page.locator('a[href*="/login"]').first()).toBeVisible();
    });

    test('DE locale loads register page in correct URL', async ({ page }) => {
        const response = await page.goto('/de/register');
        expect(response?.status()).toBe(200);
        await expect(page.locator('body')).not.toContainText('Whoops');
    });
});

test.describe('Register — validation errors', () => {
    test('mismatched passwords — stays on register, no 500', async ({ page }) => {
        await page.goto('/fr/register');
        await page.fill('#name', 'Test User');
        await page.fill('#email', `mismatch_${Date.now()}@example.com`);
        await page.fill('#password', 'Password1!');
        await page.fill('#password_confirmation', 'Different99!');
        await page.click('button[type=submit]');
        await expect(page).toHaveURL(/\/register/);
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).not.toContainText('500');
    });

    test('duplicate email — shows error banner', async ({ page }) => {
        await page.goto('/fr/register');
        await page.fill('#name', 'Duplicate');
        await page.fill('#email', ADMIN_EMAIL);
        await page.fill('#password', 'Password1!');
        await page.fill('#password_confirmation', 'Password1!');
        await page.click('button[type=submit]');
        await expect(page).toHaveURL(/\/register/);
        const banner = page.locator('.bg-red-50, .bg-red-900\\/20').first();
        await expect(banner).toBeVisible();
    });

    test('empty form — stays on register page (HTML5 required)', async ({ page }) => {
        await page.goto('/fr/register');
        await page.click('button[type=submit]');
        await expect(page).toHaveURL(/\/register/);
    });

    test('short password rejected by server validation', async ({ page }) => {
        await page.goto('/fr/register');
        await page.fill('#name', 'Short Pass');
        await page.fill('#email', `short_${Date.now()}@example.com`);
        await page.fill('#password', 'abc');
        await page.fill('#password_confirmation', 'abc');
        await page.click('button[type=submit]');
        await expect(page).toHaveURL(/\/register/);
        await expect(page.locator('body')).not.toContainText('Whoops');
    });
});

test.describe('Register — successful registration', () => {
    test('valid new user → redirects to /email/verify (no 500)', async ({ page }) => {
        const uniqueEmail = `test_reg_${Date.now()}@example.com`;
        await page.goto('/fr/register');
        await page.fill('#name', 'New Test User');
        await page.fill('#email', uniqueEmail);
        await page.fill('#password', 'SecurePass1!');
        await page.fill('#password_confirmation', 'SecurePass1!');
        await page.click('button[type=submit]');

        // Must redirect to email verification notice — NOT crash with verification.verify error
        await page.waitForURL(/\/(fr|de|it|en)\/email\/verify/, { timeout: 10_000 });
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).not.toContainText('Route [verification.verify]');
        await expect(page.locator('body')).toContainText(/vérifi/i);
    });
});

// ── Login page ────────────────────────────────────────────────────────────────

test.describe('Login — page structure', () => {
    test('loads without error and shows form', async ({ page }) => {
        const response = await page.goto('/fr/login');
        expect(response?.status()).toBe(200);
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('#email')).toBeVisible();
        await expect(page.locator('#password')).toBeVisible();
        await expect(page.locator('button[type=submit]')).toBeVisible();
    });

    test('has "Mot de passe oublié?" link to /password/reset', async ({ page }) => {
        await page.goto('/fr/login');
        const link = page.locator('a[href*="/password/reset"]');
        await expect(link).toBeVisible();
        await expect(link).toContainText(/oublié/i);
    });

    test('has "Créer un compte" link to /register', async ({ page }) => {
        await page.goto('/fr/login');
        await expect(page.locator('a[href*="/register"]')).toBeVisible();
    });

    test('password toggle shows and hides password', async ({ page }) => {
        await page.goto('/fr/login');
        const input = page.locator('#password');
        await expect(input).toHaveAttribute('type', 'password');
        const toggle = page.getByRole('button', { name: /afficher|masquer/i });
        await toggle.click();
        await expect(input).toHaveAttribute('type', 'text');
        await toggle.click();
        await expect(input).toHaveAttribute('type', 'password');
    });

    test('DE locale /de/login loads correctly', async ({ page }) => {
        const response = await page.goto('/de/login');
        expect(response?.status()).toBe(200);
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('#email')).toBeVisible();
    });

    test('EN locale /en/login loads correctly', async ({ page }) => {
        const response = await page.goto('/en/login');
        expect(response?.status()).toBe(200);
        await expect(page.locator('body')).not.toContainText('Whoops');
    });
});

test.describe('Login — validation and errors', () => {
    test('wrong credentials shows error, stays on login', async ({ page }) => {
        await page.goto('/fr/login');
        await page.fill('#email', 'nobody@example.com');
        await page.fill('#password', 'wrongpassword');
        await page.click('button[type=submit]');
        await expect(page).toHaveURL(/\/login/);
        await expect(page.locator('body')).toContainText(/credentials|incorrect|invalid|match/i);
    });

    test('wrong password for real user shows error', async ({ page }) => {
        await page.goto('/fr/login');
        await page.fill('#email', ADMIN_EMAIL);
        await page.fill('#password', 'definitelywrong!');
        await page.click('button[type=submit]');
        await expect(page).toHaveURL(/\/login/);
        await expect(page.locator('body')).not.toContainText('Whoops');
    });

    test('empty submit stays on login', async ({ page }) => {
        await page.goto('/fr/login');
        await page.click('button[type=submit]');
        await expect(page).toHaveURL(/\/login/);
    });
});

test.describe('Login — successful login', () => {
    test('admin login redirects to /admin/dashboard', async ({ page }) => {
        await page.goto('/fr/login');
        await page.fill('#email', ADMIN_EMAIL);
        await page.fill('#password', ADMIN_PASSWORD);
        await page.click('button[type=submit]');
        await page.waitForURL('**/admin/dashboard', { timeout: 10_000 });
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).not.toContainText('403');
    });

    test('clicking "Mot de passe oublié?" navigates to forgot-password form', async ({ page }) => {
        await page.goto('/fr/login');
        await page.click('a[href*="/password/reset"]');
        await expect(page).toHaveURL(/\/password\/reset/);
        await expect(page.locator('#email')).toBeVisible();
    });
});

// ── Forgot password page ──────────────────────────────────────────────────────

test.describe('Forgot password — page structure', () => {
    test('loads without error and shows email input', async ({ page }) => {
        const response = await page.goto('/fr/password/reset');
        expect(response?.status()).toBe(200);
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('#email')).toBeVisible();
        await expect(page.locator('button[type=submit]')).toBeVisible();
    });

    test('email input has icon (pl-11 padding)', async ({ page }) => {
        await page.goto('/fr/password/reset');
        const cls = await page.locator('#email').getAttribute('class');
        expect(cls).toContain('pl-11');
    });

    test('has "Retour à la connexion" link back to /login', async ({ page }) => {
        await page.goto('/fr/password/reset');
        const link = page.locator('a[href*="/login"]').first();
        await expect(link).toBeVisible();
        await expect(link).toContainText(/connexion|login/i);
    });

    test('DE locale /de/password/reset loads correctly', async ({ page }) => {
        const response = await page.goto('/de/password/reset');
        expect(response?.status()).toBe(200);
        await expect(page.locator('body')).not.toContainText('Whoops');
    });

    test('EN locale /en/password/reset loads correctly', async ({ page }) => {
        const response = await page.goto('/en/password/reset');
        expect(response?.status()).toBe(200);
        await expect(page.locator('body')).not.toContainText('Whoops');
    });
});

test.describe('Forgot password — form submission', () => {
    test('known email shows green success flash', async ({ page }) => {
        await page.goto('/fr/password/reset');
        await page.fill('#email', ADMIN_EMAIL);
        await page.click('button[type=submit]');
        await page.waitForURL(/\/password\/reset/, { timeout: 10_000 });
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).not.toContainText('500');
        const successBanner = page.locator('.bg-emerald-50, .bg-emerald-900\\/20').first();
        await expect(successBanner).toBeVisible();
    });

    test('unknown email — no 500, shows error or throttle message', async ({ page }) => {
        await page.goto('/fr/password/reset');
        await page.fill('#email', 'nobody@doesnotexist.invalid');
        await page.click('button[type=submit]');
        await expect(page).toHaveURL(/\/password\/reset/);
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).not.toContainText('500');
    });

    test('empty email — stays on form', async ({ page }) => {
        await page.goto('/fr/password/reset');
        await page.click('button[type=submit]');
        await expect(page).toHaveURL(/\/password\/reset/);
    });
});

// ── Reset password page ───────────────────────────────────────────────────────

test.describe('Reset password — page structure', () => {
    test('loads with fake token without 500', async ({ page }) => {
        const response = await page.goto('/fr/password/reset/fake-token?email=test@example.com');
        expect(response?.status()).toBe(200);
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('#password')).toBeVisible();
        await expect(page.locator('#password_confirmation')).toBeVisible();
    });

    test('has hidden token input', async ({ page }) => {
        await page.goto('/fr/password/reset/my-test-token?email=test@example.com');
        const tokenInput = page.locator('input[name="token"]');
        await expect(tokenInput).toBeAttached();
        const val = await tokenInput.inputValue();
        expect(val).toBe('my-test-token');
    });

    test('email param pre-fills the email field', async ({ page }) => {
        await page.goto('/fr/password/reset/fake-token?email=prefilled@example.com');
        await expect(page.locator('#email')).toHaveValue('prefilled@example.com');
    });

    test('page title shows "Nouveau mot de passe"', async ({ page }) => {
        await page.goto('/fr/password/reset/fake-token');
        await expect(page.locator('h1')).toContainText('Nouveau mot de passe');
    });

    test('invalid token + mismatched passwords — shows error, no 500', async ({ page }) => {
        await page.goto('/fr/password/reset/invalid-token?email=test@example.com');
        await page.fill('#email', 'test@example.com');
        await page.fill('#password', 'NewPassword1!');
        await page.fill('#password_confirmation', 'Different99!');
        await page.click('button[type=submit]');
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).not.toContainText('500');
    });
});

// ── Verify email page ─────────────────────────────────────────────────────────

test.describe('Verify email page', () => {
    test('unauthenticated → redirects to login', async ({ page }) => {
        await page.goto('/fr/email/verify');
        await expect(page).toHaveURL(/\/login/);
    });

    test('renders without 500 for authenticated user', async ({ page }) => {
        await loginAsAdmin(page);
        const response = await page.goto('/fr/email/verify');
        expect(response?.status()).toBeLessThan(500);
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).not.toContainText('Route [verification.verify]');
    });

    test('resend form action points to /email/verification-notification', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/fr/email/verify');
        const form = page.locator('form[action*="verification-notification"]');
        await expect(form).toBeAttached();
    });

    test('has logout link', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/fr/email/verify');
        const logoutBtn = page.locator('form[action*="/logout"] button[type=submit]').first();
        await expect(logoutBtn).toBeAttached();
    });
});
