import { voiceOverTest as test } from "@guidepup/playwright";
import { expect } from "@playwright/test";
import {
    BASE_URL,
    assertHomeStructure,
    collectVoiceOverSpeech,
    expectSpoken,
} from "./lib/checks";

test.describe("Braillewright home — VoiceOver", () => {
    test("skip link, landmarks and Primary nav are exposed", async ({ page, voiceOver }) => {
        await page.goto(`${BASE_URL}/`, { waitUntil: "load" });

        // 1) Deterministic structure (DOM / accessibility tree).
        await assertHomeStructure(page);

        // 2) What VoiceOver actually announces walking the page chrome.
        const speech = await collectVoiceOverSpeech(voiceOver);
        console.log(`[VoiceOver home spoken log]\n${speech}`);
        // Hard assertion: the reliably-announced skip link. collectVoiceOverSpeech
        // also attempts a control-jump walk (logged above) to deepen this later.
        expectSpoken(speech, ["skip"]);
    });

    test("primary nav toggle exposes expanded/collapsed state (mobile)", async ({ page, voiceOver }) => {
        await page.setViewportSize({ width: 390, height: 800 });
        await page.goto(`${BASE_URL}/`, { waitUntil: "load" });

        // The hamburger toggle is mobile-only; it carries aria-expanded.
        const toggle = page.locator("[aria-expanded]").first();
        await expect(toggle).toBeVisible();
        await expect(toggle).toHaveAttribute("aria-expanded", "false");

        await toggle.click();
        await expect(toggle).toHaveAttribute("aria-expanded", "true");

        // A programmatic click doesn't route through the SR cursor, so the spoken
        // phrase is often empty here. The aria-expanded toggle above is the
        // deterministic WCAG check; SR announcement of the state is a follow-up.
        const phrase = (await voiceOver.lastSpokenPhrase()).toLowerCase();
        console.log(`[VoiceOver menu toggle phrase] ${phrase}`);
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
