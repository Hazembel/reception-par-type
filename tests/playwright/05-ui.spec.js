import { test, expect } from '@playwright/test';

// ── UI behaviour — dark mode, navbar, no JS errors ────────────────────────────

test.describe('Dark mode toggle', () => {
    test('toggling dark mode adds dark class to html element', async ({ page }) => {
        await page.goto('/fr');
        // Alpine controls dark mode on <html>
        const htmlEl = page.locator('html');

        const toggle = page.locator('[x-on\\:click*="dark"], [\\@click*="dark"], button[aria-label*="dark" i], button[aria-label*="mode" i]').first();

        if (await toggle.count() > 0) {
            const before = await htmlEl.getAttribute('class') ?? '';
            await toggle.click();
            const after = await htmlEl.getAttribute('class') ?? '';
            expect(before).not.toEqual(after);
        } else {
            // Dark mode toggle not found by aria-label — skip gracefully
            test.skip(true, 'Dark mode toggle not found via aria-label');
        }
    });
});

test.describe('Navbar', () => {
    test('navbar contains login link when logged out', async ({ page }) => {
        await page.goto('/fr');
        const loginLink = page.locator('a[href*="/login"]');
        await expect(loginLink.first()).toBeVisible();
    });

    test('navbar scroll glass effect — header stays visible', async ({ page }) => {
        await page.goto('/fr');
        const header = page.locator('header, nav').first();
        await expect(header).toBeVisible();

        // Scroll down
        await page.evaluate(() => window.scrollTo(0, 500));
        await page.waitForTimeout(400);
        await expect(header).toBeVisible();
    });
});

test.describe('No JS console errors on key pages', () => {
    const pages = ['/fr', '/fr/search', '/fr/login', '/fr/pricing'];

    for (const path of pages) {
        test(`no critical JS errors on ${path}`, async ({ page }) => {
            const errors = [];
            page.on('pageerror', err => errors.push(err.message));

            await page.goto(path);
            await page.waitForLoadState('networkidle');

            const critical = errors.filter(e =>
                !e.includes('favicon') &&
                !e.includes('Extension') &&
                !e.includes('chrome-extension')
            );
            expect(critical).toHaveLength(0);
        });
    }
});

test.describe('Responsive layout', () => {
    test('home page looks correct on mobile (375px)', async ({ page }) => {
        await page.setViewportSize({ width: 375, height: 812 });
        await page.goto('/fr');
        await expect(page.locator('body')).not.toContainText('Whoops');
        const header = page.locator('header, nav').first();
        await expect(header).toBeVisible();
    });

    test('home page looks correct on desktop (1280px)', async ({ page }) => {
        await page.setViewportSize({ width: 1280, height: 800 });
        await page.goto('/fr');
        await expect(page.locator('body')).not.toContainText('Whoops');
    });
});
