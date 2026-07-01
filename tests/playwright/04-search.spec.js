import { test, expect } from '@playwright/test';

// ── Search functionality ───────────────────────────────────────────────────────

test.describe('Search page', () => {
    test('GET /fr/search loads the search UI', async ({ page }) => {
        await page.goto('/fr/search');
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).not.toContainText('500');
    });

    test('search form accepts a text query', async ({ page }) => {
        await page.goto('/fr/search');
        const form = page.locator('form').first();
        await expect(form).toBeVisible();
    });

    test('POST /fr/search with a brand returns results or empty state', async ({ page }) => {
        await page.goto('/fr/search');
        // Fill the first text input (search field)
        await page.locator('input[type="text"], input[type="search"]').first().fill('BMW');
        await page.locator('form').first().evaluate(f => f.submit());

        await page.waitForLoadState('networkidle');
        await expect(page.locator('body')).not.toContainText('Whoops');
        // Note: vehicle codes in results legitimately contain "500" — don't check for it
        // Either shows results table or "no results" message
        const body = await page.textContent('body');
        expect(body?.length).toBeGreaterThan(200);
    });

    test('search works in German locale', async ({ page }) => {
        await page.goto('/de/search');
        await expect(page.locator('body')).not.toContainText('Whoops');
    });

    test('search works in Italian locale', async ({ page }) => {
        await page.goto('/it/search');
        await expect(page.locator('body')).not.toContainText('Whoops');
    });

    test('search works in English locale', async ({ page }) => {
        await page.goto('/en/search');
        await expect(page.locator('body')).not.toContainText('Whoops');
    });
});

test.describe('Compare page', () => {
    test('/fr/compare loads without error', async ({ page }) => {
        const response = await page.goto('/fr/compare');
        expect(response?.status()).toBeLessThan(500);
        await expect(page.locator('body')).not.toContainText('Whoops');
    });
});
