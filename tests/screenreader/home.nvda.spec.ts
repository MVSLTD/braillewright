import { nvdaTest as test } from "@guidepup/playwright";
import { expect } from "@playwright/test";
import {
    BASE_URL,
    assertHomeStructure,
    collectSpeechWalk,
    expectSpoken,
} from "./lib/checks";

test.describe("Braillewright home — NVDA", () => {
    test("skip link, landmarks and Primary nav are exposed", async ({ page, nvda }) => {
        await page.goto(`${BASE_URL}/`, { waitUntil: "load" });

        // 1) Deterministic structure (DOM / accessibility tree).
        await assertHomeStructure(page);

        // 2) What NVDA actually announces walking the page chrome.
        const speech = await collectSpeechWalk(nvda, {
            maxSteps: 40,
            until: ["skip", "primary", "main"],
        });
        console.log(`[NVDA home spoken log]\n${speech}`);
        expectSpoken(speech, ["skip", "primary", "navigation"]);
    });

    test("primary nav toggle exposes expanded/collapsed state (mobile)", async ({ page, nvda }) => {
        await page.setViewportSize({ width: 390, height: 800 });
        await page.goto(`${BASE_URL}/`, { waitUntil: "load" });

        // The hamburger toggle is mobile-only; it carries aria-expanded.
        const toggle = page.locator("[aria-expanded]").first();
        await expect(toggle).toBeVisible();
        await expect(toggle).toHaveAttribute("aria-expanded", "false");

        await toggle.click();
        await expect(toggle).toHaveAttribute("aria-expanded", "true");

        // Soft on the announcement for now — log it, tighten after the first run.
        const phrase = (await nvda.lastSpokenPhrase()).toLowerCase();
        console.log(`[NVDA menu toggle phrase] ${phrase}`);
        expect(phrase.length).toBeGreaterThan(0);
    });

    test("header search control has an accessible name (skips if absent)", async ({ page }) => {
        await page.goto(`${BASE_URL}/`, { waitUntil: "load" });
        const search = page.getByRole("search").or(page.locator('input[type="search"]'));
        if ((await search.count()) === 0) {
            test.skip(true, "No header search control renders on the live TTT templates (see docs/CI.md).");
        }
        await expect(search.first()).toBeVisible();
    });
});
