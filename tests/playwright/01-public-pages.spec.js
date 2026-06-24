import { test, expect } from '@playwright/test';

// ── Public pages — no login required ──────────────────────────────────────────

test.describe('Root redirect', () => {
    test('/ redirects to a locale home page', async ({ page }) => {
        await page.goto('/');
        await expect(page).toHaveURL(/\/(fr|de|it|en)\/?$/);
        await expect(page).toHaveTitle(/.+/);
    });
});

test.describe('Home page', () => {
    test('loads and shows site name', async ({ page }) => {
        await page.goto('/fr');
        await expect(page).toHaveTitle(/reception-par-type/i);
        await expect(page.locator('body')).not.toContainText('500');
        await expect(page.locator('body')).not.toContainText('Whoops');
    });

    test('navbar is visible', async ({ page }) => {
        await page.goto('/fr');
        await expect(page.locator('header').first()).toBeVisible();
    });
});

test.describe('Locale routing', () => {
    for (const locale of ['fr', 'de', 'it', 'en']) {
        test(`/${locale} loads without error`, async ({ page }) => {
            const response = await page.goto(`/${locale}`);
            expect(response?.status()).toBeLessThan(500);
            await expect(page.locator('body')).not.toContainText('Whoops');
        });
    }

    test('invalid locale is not served (404 — route does not match)', async ({ page }) => {
        const response = await page.goto('/es');
        // The where() constraint on the route means /es simply doesn't match — 404.
        expect(response?.status()).toBe(404);
    });
});

test.describe('Pricing page', () => {
    test('loads without error', async ({ page }) => {
        const response = await page.goto('/fr/pricing');
        expect(response?.status()).toBeLessThan(500);
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page).toHaveTitle(/reception-par-type/i);
    });
});

test.describe('FAQ page', () => {
    test('loads without error', async ({ page }) => {
        await page.goto('/fr/faq');
        const status = (await page.goto('/fr/faq'))?.status();
        expect(status).toBeLessThan(500);
        await expect(page.locator('body')).not.toContainText('Whoops');
    });
});

test.describe('Guide page', () => {
    test('loads without error', async ({ page }) => {
        const response = await page.goto('/fr/guide');
        expect(response?.status()).toBeLessThan(500);
    });
});

test.describe('Legal pages', () => {
    for (const path of ['/fr/mentions-legales', '/fr/confidentialite', '/fr/cgu']) {
        test(`${path} loads`, async ({ page }) => {
            const response = await page.goto(path);
            expect(response?.status()).toBeLessThan(500);
            await expect(page.locator('body')).not.toContainText('Whoops');
        });
    }
});

test.describe('Disabled routes', () => {
    test('/register returns 404', async ({ page }) => {
        const response = await page.goto('/fr/register');
        expect(response?.status()).toBe(404);
    });

    test('/password/reset returns 404', async ({ page }) => {
        const response = await page.goto('/fr/password/reset');
        expect(response?.status()).toBe(404);
    });
});

test.describe('Search page', () => {
    test('loads the search form', async ({ page }) => {
        await page.goto('/fr/search');
        expect((await page.goto('/fr/search'))?.status()).toBeLessThan(500);
        await expect(page.locator('body')).not.toContainText('Whoops');
    });
});
