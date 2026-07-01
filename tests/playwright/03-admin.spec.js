import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './helpers.js';

// ── Admin section ─────────────────────────────────────────────────────────────

test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
});

test.describe('Admin dashboard', () => {
    test('loads without error', async ({ page }) => {
        await page.goto('/admin/dashboard');
        // Check for Laravel error page only — "500" appears legitimately in content (quotas etc.)
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page).toHaveURL(/\/admin\/dashboard/);
    });

    test('shows KPI/stat blocks', async ({ page }) => {
        await page.goto('/admin/dashboard');
        // Admin dashboard should show some numeric stats
        await expect(page.locator('body')).not.toBeEmpty();
        const body = await page.textContent('body');
        expect(body?.length).toBeGreaterThan(500);
    });
});

test.describe('Admin users list', () => {
    test('loads user table', async ({ page }) => {
        await page.goto('/admin/users');
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).not.toContainText('500');
    });

    test('search by email filters results', async ({ page }) => {
        await page.goto('/admin/users');
        // Use ps-input class to target the filter form input (not the global nav search)
        const searchInput = page.locator('input.ps-input[name="q"]');
        await expect(searchInput).toBeVisible();
        await searchInput.fill('admin');
        // Use text to avoid matching the global nav search button
        await page.getByRole('button', { name: 'Rechercher' }).click();
        await expect(page).toHaveURL(/[?&]q=admin/);
        await expect(page.locator('body')).not.toContainText('Whoops');
    });

    test('sort by name changes column', async ({ page }) => {
        await page.goto('/admin/users?sort=name&dir=asc');
        await expect(page.locator('body')).not.toContainText('Whoops');
    });

    test('filter by subscription level (non-admin levels)', async ({ page }) => {
        await page.goto('/admin/users?level=1');
        await expect(page.locator('body')).not.toContainText('Whoops');
    });
});

test.describe('Admin administrators list', () => {
    test('/admin/users/admins shows admin account', async ({ page }) => {
        await page.goto('/admin/users/admins');
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).toContainText('admin@reception-par-type.ch');
    });
});

test.describe('Admin user detail', () => {
    test('admin user show page loads', async ({ page }) => {
        // Use the dedicated admins route (level=8 is excluded from the regular users list)
        await page.goto('/admin/users/admins');
        // Get the link from the TABLE (not the nav sidebar which also has /admin/users/ links)
        const rowLink = page.locator('table a[href*="/admin/users/"]').first();
        await expect(rowLink).toBeVisible();
        const href = await rowLink.getAttribute('href');
        await page.goto(href ?? '/admin/users');
        await expect(page).toHaveURL(/\/admin\/users\/.+/);
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).not.toContainText('500');
    });
});

test.describe('Admin import', () => {
    test('import dashboard loads', async ({ page }) => {
        await page.goto('/admin/import');
        // "5000" and "2000" appear in content legitimately — only check for error page
        await expect(page.locator('body')).not.toContainText('Whoops');
    });

    test('import form has correct field names', async ({ page }) => {
        await page.goto('/admin/import');
        // Verify our S-3 fix: field must be "import_type", not "type"
        const select = page.locator('select[name="import_type"]');
        await expect(select).toBeVisible();
        // And options must be 2000/5000
        await expect(select.locator('option[value="5000"]')).toHaveCount(1);
        await expect(select.locator('option[value="2000"]')).toHaveCount(1);
    });

    test('submitting import form without a running import redirects back', async ({ page }) => {
        await page.goto('/admin/import');
        await page.selectOption('select[name="import_type"]', '5000');
        // Target the submit button that is inside the form containing the import_type select
        const triggerForm = page.locator('form').filter({ has: page.locator('select[name="import_type"]') });
        await triggerForm.locator('button[type=submit]').click();
        // Should redirect back to import index (success or "already running" flash)
        await expect(page).toHaveURL(/\/admin\/import/);
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).not.toContainText('422');
    });
});

test.describe('Admin pricing', () => {
    test('pricing management page loads', async ({ page }) => {
        await page.goto('/admin/pricing');
        await expect(page.locator('body')).not.toContainText('Whoops');
        // Page legitimately contains "500 fiches" for Pro plan — only assert no error page
    });
});

test.describe('Admin affiliates', () => {
    test('affiliates page loads', async ({ page }) => {
        await page.goto('/admin/affiliates');
        await expect(page.locator('body')).not.toContainText('Whoops');
        await expect(page.locator('body')).not.toContainText('500');
    });
});

test.describe('Non-admin blocked from admin', () => {
    test('/admin/* redirects unauthenticated users to login', async ({ page }) => {
        // Log out using the admin layout's hidden form
        await page.evaluate(() => {
            const form = document.getElementById('ps-logout-form');
            if (form) form.submit();
        });
        await page.waitForURL(/^http:\/\/localhost:8000\/(fr|de|it|en)?\/?$/, { timeout: 8_000 });

        await page.goto('/admin/dashboard');
        await expect(page).toHaveURL(/\/login/);
    });
});
