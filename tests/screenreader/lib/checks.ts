import { expect, type Page } from "@playwright/test";

/**
 * Shared helpers for the Braillewright screen-reader smoke suite.
 *
 * Each page check has two layers:
 *   1. Deterministic Playwright assertions against the DOM / accessibility tree
 *      (these never flake on screen-reader timing or phrasing).
 *   2. A Guidepup "spoken log" walk that proves the screen reader actually
 *      ANNOUNCES the accessible names we set. Assertions here target the label
 *      text we control (e.g. "Primary", "Back to top"), not exact SR phrasing,
 *      because NVDA and VoiceOver word things differently.
 *
 * The full spoken log is printed by each spec so the first CI run's artifact
 * shows exactly what each screen reader said — use that to tighten assertions.
 */

/** Base URL of the deployment under test. CI sets SR_BASE_URL; default = TTT staging. */
export const BASE_URL =
    process.env.SR_BASE_URL ??
    "https://staging-62a2-54d676bfb539fe3-eqtfw.wpcomstaging.com";

/** A recent TTT issue that exists on the staging clone (verified reachable 2026-06-19). */
export const ISSUE_PATH = "/newsletter-06-11-2026/";

/**
 * The symmetric subset of Guidepup's `nvda` / `voiceOver` fixture APIs that this
 * suite uses. Keeping it minimal lets the same walk drive either screen reader.
 */
export interface ScreenReader {
    navigateToWebContent(): Promise<void>;
    next(): Promise<void>;
    lastSpokenPhrase(): Promise<string>;
    spokenPhraseLog(): Promise<string[]>;
}

/**
 * Walk linearly from the start of the web content, capturing everything the
 * screen reader announces. Stops early once every `until` token has been heard
 * (keeps fast pages fast), otherwise after `maxSteps`. Returns the full spoken
 * log as one lowercased string for substring assertions.
 */
export async function collectSpeechWalk(
    sr: ScreenReader,
    opts: { maxSteps?: number; until?: string[] } = {}
): Promise<string> {
    const { maxSteps = 40, until = [] } = opts;
    await sr.navigateToWebContent();
    for (let i = 0; i < maxSteps; i++) {
        const soFar = (await sr.spokenPhraseLog()).join(" \n ").toLowerCase();
        if (until.length && until.every((t) => soFar.includes(t.toLowerCase()))) {
            break;
        }
        await sr.next();
    }
    return (await sr.spokenPhraseLog()).join(" \n ").toLowerCase();
}

/**
 * VoiceOver walk. On macOS, voiceOver.next() can stall at the web-area boundary
 * (it repeats the first element), so this captures the reliably-announced entry
 * point, then attempts to descend (interact) and jump control-to-control with
 * findNextControl. The full result is returned lowercased for substring asserts
 * and is logged so a later iteration can deepen the VoiceOver assertions on
 * evidence. Best-effort: traversal failures never lose the entry-point capture.
 */
export async function collectVoiceOverSpeech(sr: any, maxSteps = 25): Promise<string> {
    await sr.navigateToWebContent();
    const phrases: string[] = [(await sr.lastSpokenPhrase()) ?? ""];
    try {
        await sr.interact();
    } catch {
        /* interact isn't always required/available; ignore */
    }
    for (let i = 0; i < maxSteps; i++) {
        try {
            await sr.perform(sr.keyboardCommands.findNextControl);
            phrases.push((await sr.lastSpokenPhrase()) ?? "");
        } catch {
            break;
        }
    }
    try {
        phrases.push(...(await sr.spokenPhraseLog()));
    } catch {
        /* ignore */
    }
    return phrases.join(" \n ").toLowerCase();
}

/** Assert each expected token appears in the spoken log; the message names the misses. */
export function expectSpoken(speech: string, tokens: string[]): void {
    const missing = tokens.filter((t) => !speech.includes(t.toLowerCase()));
    expect(missing, `screen reader never announced: [${missing.join(", ")}]`).toEqual([]);
}

/* ----------------------------------------------------------------------------
 * HOME PAGE
 * ------------------------------------------------------------------------- */

/** Deterministic structure checks for the home page (no screen reader needed). */
export async function assertHomeStructure(page: Page): Promise<void> {
    // The skip link is the first focusable element and targets #main.
    await page.keyboard.press("Tab");
    const skip = page.locator("a.skip-content");
    await expect(skip).toBeFocused();
    await expect(skip).toHaveAttribute("href", "#main");

    // Activating it moves focus into the main landmark (WCAG 2.4.1).
    await skip.press("Enter");
    await expect(page.locator("#main")).toBeFocused();

    // Primary landmarks render with their accessible names.
    await expect(page.getByRole("banner")).toBeVisible();
    await expect(page.getByRole("navigation", { name: "Primary" })).toBeVisible();
    await expect(page.locator("#main")).toBeVisible();
    await expect(page.getByRole("contentinfo")).toBeVisible();
}

/* ----------------------------------------------------------------------------
 * ISSUE PAGE
 * ------------------------------------------------------------------------- */

/** Deterministic structure checks for an issue (single-post) page. */
export async function assertIssueStructure(page: Page): Promise<void> {
    await expect(page.locator("h1.post-title")).toBeVisible();
    await expect(page.locator("h2").first()).toBeVisible();

    // The editorial-pass fix: every "Back to top" section-jumper link now carries
    // an accessible name (previously an emoji-only link with no name).
    const backToTop = page.getByLabel("Back to top");
    await expect(backToTop.first()).toBeVisible();
    expect(await backToTop.count()).toBeGreaterThan(0);

    // The post-navigation landmark is distinctly labelled.
    await expect(page.getByRole("navigation", { name: "Post" })).toBeVisible();
}
