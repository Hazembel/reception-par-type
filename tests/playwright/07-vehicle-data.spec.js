import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './helpers.js';

// Helper: navigate to the first vehicle found via search results.
// Returns the URL string, or null if no vehicles exist in the DB.
async function firstVehicleUrl(page) {
    await page.goto('/fr/search');
    await page.waitForLoadState('domcontentloaded');
    const link = page.locator('a[href*="/vehicle/"]').first();
    const visible = await link.isVisible({ timeout: 5_000 }).catch(() => false);
    if (!visible) return null;
    return link.getAttribute('href');
}

// ── Data-row component: empty-string bug ──────────────────────────────────────

test.describe('data-row component (bug fix — blank vs —)', () => {
    test('no <dd> on a vehicle page is ever blank (must show — or a value)', async ({ page }) => {
        await loginAsAdmin(page);
        const href = await firstVehicleUrl(page);
        if (!href) { test.skip(); return; }

        await page.goto(href);
        await page.waitForLoadState('domcontentloaded');

        const dds = await page.locator('dd').all();
        expect(dds.length).toBeGreaterThan(0);

        for (const dd of dds) {
            const text = (await dd.innerText()).trim();
            // Must never be completely blank
            expect(text, `Found a blank <dd> on ${href}`).not.toBe('');
        }
    });

    test('null energie / boite_vitesse shows — not blank', async ({ page }) => {
        await loginAsAdmin(page);
        const href = await firstVehicleUrl(page);
        if (!href) { test.skip(); return; }

        await page.goto(href);
        await page.waitForLoadState('domcontentloaded');

        // Every dd must have either a dash or a real value — never whitespace-only
        const emptyDds = await page.locator('dd').filter({ hasText: /^\s*$/ }).count();
        expect(emptyDds).toBe(0);
    });
});

// ── Vehicle detail page structure ─────────────────────────────────────────────

test.describe('Vehicle detail page', () => {
    test('page loads without 500 error', async ({ page }) => {
        await loginAsAdmin(page);
        const href = await firstVehicleUrl(page);
        if (!href) { test.skip(); return; }

        const response = await page.goto(href);
        expect(response?.status()).toBeLessThan(500);
        await expect(page.locator('body')).not.toContainText('Whoops');
    });

    test('Motorisation card is always visible', async ({ page }) => {
        await loginAsAdmin(page);
        const href = await firstVehicleUrl(page);
        if (!href) { test.skip(); return; }

        await page.goto(href);
        await expect(page.locator('body')).toContainText('Motorisation');
    });

    test('Émissions card is always visible', async ({ page }) => {
        await loginAsAdmin(page);
        const href = await firstVehicleUrl(page);
        if (!href) { test.skip(); return; }

        await page.goto(href);
        await expect(page.locator('body')).toContainText('Émissions');
    });

    test('admin sees Masses and Jantes cards (not locked)', async ({ page }) => {
        await loginAsAdmin(page);
        const href = await firstVehicleUrl(page);
        if (!href) { test.skip(); return; }

        await page.goto(href);
        // Admin (level 8) must see the actual data cards, not locked placeholders
        await expect(page.locator('body')).toContainText('Masses');
        await expect(page.locator('body')).toContainText('Jantes');
    });

    test('unauthenticated user sees vehicle page (Motorisation visible)', async ({ page }) => {
        // No login — public user
        const href = await firstVehicleUrl(page);
        if (!href) { test.skip(); return; }

        await page.goto(href);
        await page.waitForLoadState('domcontentloaded');
        expect((await page.goto(href))?.status()).toBeLessThan(500);
        await expect(page.locator('body')).toContainText('Motorisation');
    });
});

// ── DIAGNOSTIC — reports data completeness, never fails ───────────────────────

test.describe('DIAGNOSTIC — vehicle data completeness', () => {
    test('reports how many fields have real data vs —', async ({ page }) => {
        await loginAsAdmin(page);
        const href = await firstVehicleUrl(page);

        if (!href) {
            console.error('❌ NO VEHICLES IN DB');
            console.error('   Run: php artisan astra:import --type=all --force');
            test.skip();
            return;
        }

        await page.goto(href);
        await page.waitForLoadState('domcontentloaded');

        const allDds = await page.locator('dd').all();
        let dashCount = 0;
        let dataCount = 0;

        for (const dd of allDds) {
            const text = (await dd.innerText()).trim();
            if (text === '—') dashCount++;
            else if (text !== '') dataCount++;
        }

        console.log(`📊 Vehicle ${href}: ${dataCount} values shown, ${dashCount} fields empty (—)`);

        if (dataCount === 0 && dashCount > 0) {
            console.error('❌ ALL TECHNICAL FIELDS ARE NULL — ASTRA import required');
            console.error('   Place TG-Automobil.txt in storage/app/astra/2000/');
            console.error('   Then run: php artisan astra:import --type=all --force');
        }

        // Never fails — data absence is an ops issue, not a code bug
        expect(allDds.length).toBeGreaterThan(0);
    });
});
