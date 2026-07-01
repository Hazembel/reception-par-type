import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './helpers.js';

test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
});

// ── Page overview ─────────────────────────────────────────────────────────────

test.describe('Données ASTRA — page overview', () => {
    test('import dashboard loads without error', async ({ page }) => {
        const response = await page.goto('/admin/import');
        expect(response?.status()).toBeLessThan(500);
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).not.toContainText('Exception');
    });

    test('shows 4 KPI cards: Imports 2000, Imports 5000, En cours, Échoués', async ({ page }) => {
        await page.goto('/admin/import');
        await expect(page.locator('body')).toContainText('Imports 2000');
        await expect(page.locator('body')).toContainText('Imports 5000');
        await expect(page.locator('body')).toContainText('En cours');
        await expect(page.locator('body')).toContainText('Échoués');
    });

    test('disk status section is visible with directory rows', async ({ page }) => {
        await page.goto('/admin/import');
        await expect(page.locator('body')).toContainText('Dossier 2000');
        await expect(page.locator('body')).toContainText('Dossier 5000');
        await expect(page.locator('body')).toContainText('Fichier principal');
    });

    test('import history table is visible with expected columns', async ({ page }) => {
        await page.goto('/admin/import');
        await expect(page.locator('body')).toContainText('Historique');
        await expect(page.locator('body')).toContainText('Statut');
        await expect(page.locator('body')).toContainText('Insérés');
    });
});

// ── Trigger form ──────────────────────────────────────────────────────────────

test.describe('Données ASTRA — trigger form', () => {
    test('import_type select exists with options 2000 and 5000', async ({ page }) => {
        await page.goto('/admin/import');
        const select = page.locator('select[name="import_type"]');
        await expect(select).toBeVisible();
        await expect(select.locator('option[value="5000"]')).toHaveCount(1);
        await expect(select.locator('option[value="2000"]')).toHaveCount(1);
    });

    test('force checkbox is present', async ({ page }) => {
        await page.goto('/admin/import');
        await expect(page.locator('input[name="force"][type="checkbox"]')).toBeAttached();
    });

    test('submit with type=2000 redirects back to import page without 500', async ({ page }) => {
        await page.goto('/admin/import');
        await page.selectOption('select[name="import_type"]', '2000');
        const triggerForm = page.locator('form').filter({ has: page.locator('select[name="import_type"]') });
        await triggerForm.locator('button[type=submit]').click();
        await expect(page).toHaveURL(/\/admin\/import/);
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).not.toContainText('Exception');
    });

    test('submit with type=5000 redirects back to import page without 500', async ({ page }) => {
        await page.goto('/admin/import');
        await page.selectOption('select[name="import_type"]', '5000');
        const triggerForm = page.locator('form').filter({ has: page.locator('select[name="import_type"]') });
        await triggerForm.locator('button[type=submit]').click();
        await expect(page).toHaveURL(/\/admin\/import/);
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).not.toContainText('Exception');
    });

    test('submit with force=checked does not crash', async ({ page }) => {
        await page.goto('/admin/import');
        await page.selectOption('select[name="import_type"]', '2000');
        const forceBox = page.locator('input[name="force"][type="checkbox"]');
        if (await forceBox.isVisible()) {
            await forceBox.check();
        }
        const triggerForm = page.locator('form').filter({ has: page.locator('select[name="import_type"]') });
        await triggerForm.locator('button[type=submit]').click();
        await expect(page).toHaveURL(/\/admin\/import/);
        await expect(page.locator('body')).not.toContainText('Whoops');
    });
});

// ── Import log detail ─────────────────────────────────────────────────────────

test.describe('Données ASTRA — import log detail', () => {
    test('clicking a log entry opens its detail page', async ({ page }) => {
        await page.goto('/admin/import');
        const logLink = page.locator('a[href*="/admin/import/"]').first();
        const hasLog = await logLink.isVisible({ timeout: 3_000 }).catch(() => false);

        if (!hasLog) {
            console.log('ℹ️  No import logs yet — trigger an import first');
            test.skip();
            return;
        }

        await logLink.click();
        await expect(page).toHaveURL(/\/admin\/import\/.+/);
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).not.toContainText('500');
    });
});

// ── DIAGNOSTIC — reports ASTRA file presence ──────────────────────────────────

test.describe('DIAGNOSTIC — ASTRA file presence on disk', () => {
    test('reports which directories and files are present', async ({ page }) => {
        await page.goto('/admin/import');
        const body = await page.locator('body').textContent() ?? '';

        // Read KPI numbers from the page (structure: cards with numeric values)
        const kpiEls = await page.locator('.ps-kpi-value, [class*="kpi"]').allTextContents();
        if (kpiEls.length) {
            console.log('📊 Import KPIs:', kpiEls.join(' | '));
        }

        // Report absent directories
        if (/Dossier 2000.*Absent/s.test(body)) {
            console.warn('⚠️  storage/app/astra/2000/ does not exist or is empty');
        } else {
            console.log('✅ Dossier 2000 present');
        }

        if (/Dossier 5000.*Absent/s.test(body)) {
            console.warn('⚠️  storage/app/astra/5000/ does not exist or is empty');
        } else {
            console.log('✅ Dossier 5000 present');
        }

        if (/Fichier principal.*Absent/s.test(body)) {
            console.warn('⚠️  TG-Automobil.txt (or equivalent) is missing');
            console.warn('   → Download from ASTRA and place in storage/app/astra/2000/');
        } else {
            console.log('✅ Fichier principal present');
        }

        // Diagnostic only — never fails
        await expect(page.locator('body')).not.toContainText('Whoops');
    });
});
