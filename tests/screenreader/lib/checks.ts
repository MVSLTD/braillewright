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

/** Base URL of the deployment under test. Set SR_BASE_URL (CI sources it from the SR_BASE_URL repo variable). */
export const BASE_URL =
    process.env.SR_BASE_URL ??
    (() => {
        throw new Error(
            "SR_BASE_URL is required: the base URL of the site under test. " +
            "In CI it comes from the SR_BASE_URL repository variable; locally, export it before running.",
        );
    })();

/**
 * A single-post address on the site under test.
 *
 * Defaults to a TTT issue verified reachable 2026-06-19, so the nightly TTT run is
 * unchanged. Override with SR_ISSUE_PATH when pointing this suite at another
 * Braillewright site -- a TTT newsletter URL 404s everywhere else, and
 * assertIssueStructure would then fail on a missing page rather than on anything
 * about the theme.
 */
// ⛔ `||`, NOT `??`. A workflow_dispatch input left blank arrives as an EMPTY STRING, and
// a scheduled run supplies none at all. `??` only falls back on null/undefined, so `??`
// here would let "" through, the issue spec would load the HOME page, and every nightly
// TTT run would fail on a missing h1.post-title. `||` catches the empty string too.
export const ISSUE_PATH = process.env.SR_ISSUE_PATH || "/newsletter-06-11-2026/";

/**
 * A lowercase phrase the screen reader must actually announce on this site -- proof that
 * speech is flowing and the heading walk reached real content, not just that the DOM
 * looked right.
 *
 * ⛔ Defaults to "top tech tidbits". It MUST be overridden with SR_BRAND for any other
 * site: a screen reader will never announce "top tech tidbits" on a site that is not Top
 * Tech Tidbits, so leaving it would fail every spec for a reason that has nothing to do
 * with accessibility.
 *
 * ⚠️⚠️ IT MUST BE A PHRASE THE **HEADING WALK** REACHES -- NOT JUST TEXT ON THE PAGE, AND
 * NOT NECESSARILY THE SITE NAME. Learned the hard way 2026-08-26 on the first cross-site
 * run: `SR_BRAND=sterling` against https://sterlingcreations.ca/ failed, and the site was
 * fine. NVDA's walk announced:
 *
 *     main landmark, welcome!, heading, level 1
 *     blog:, heading, level 2
 *     complementary landmark, sidebar, heading, level 2
 *     recent posts, heading, level 2
 *
 * The h1 is "Welcome!" -- the PAGE title -- because that site's front page is a STATIC
 * PAGE. Top Tech Tidbits' front page is the blog index, so there the SITE title is the h1
 * and "top tech tidbits" is announced. sterlingcreations.ca's site title is perfectly
 * accessible (a `screen-reader-text` span inside the masthead link); it is simply not a
 * heading, which is correct on a static front page.
 *
 * 👉 So: check `show_on_front`. If it is `page`, use a word from THAT PAGE'S TITLE.
 *    Measured 2026-08-26 -- sterlingcreations.com: "Home"; sterlingcreations.ca: "Welcome!".
 */
// Same `||` reasoning as ISSUE_PATH above.
export const BRAND = (process.env.SR_BRAND || "top tech tidbits").toLowerCase();

/**
 * Whether this site's POSTS are expected to carry editorial "Back to top" section-jumper
 * links. True for Top Tech Tidbits newsletters; false for an ordinary blog.
 *
 * `||` for the same empty-string reason as ISSUE_PATH. Compared against "0" so that any
 * other value keeps the check ON -- a typo must not silently disable an assertion.
 */
export const EXPECT_BACKTOTOP = (process.env.SR_EXPECT_BACKTOTOP || "1") !== "0";

/**
 * The token the ISSUE (single-post) spec expects to hear. Defaults to BRAND.
 *
 * ⚠️ ONE TOKEN CANNOT SERVE BOTH PAGES on every site. The home page's h1 and a single
 * post's h1 are different things, and only sometimes share a word:
 *
 *   toptechtidbits.com  home h1 = the site title "Top Tech Tidbits"; and its newsletter
 *                       posts also carry "Top Tech Tidbits" in headings -- so one token
 *                       happens to work, which is why this was never noticed.
 *   sterlingcreations.ca  home h1 = "Welcome!" (a static front page); post h1 = the post
 *                       title. "welcome" is announced on the home page and NEVER on a post.
 *
 * Measured 2026-08-26: SR_BRAND=welcome passed the home spec and failed the issue spec on
 * exactly that. Set SR_ISSUE_BRAND to a word from the post at SR_ISSUE_PATH.
 */
export const ISSUE_BRAND = (process.env.SR_ISSUE_BRAND || BRAND).toLowerCase();

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
 * Walk the page by HEADING and capture what the screen reader announces.
 *
 * Heading navigation (NVDA's moveToNextHeading / VoiceOver's findNextHeading) is
 * the primary way screen-reader users move through a page, and — unlike linear
 * next() traversal, which proved flaky on BOTH screen readers in CI — it is a
 * discrete, reliable jump. Stops as soon as a jump stops advancing (the same
 * phrase twice = wrapped past the last heading). Returns the speech lowercased.
 *
 * Resilience: on a cold CI runner the screen reader's virtual buffer is
 * occasionally not ready when navigateToWebContent() fires, so the heading
 * quick-key falls through as a literal keystroke and the walk comes back empty
 * (observed on the 2026-06-21 staging run: the entire spoken log was an echoed
 * "h", while prod, on identical content, announced every heading). A walk that
 * engaged browse mode always announces at least one "heading"; if none appears
 * we settle briefly and retry the walk before giving up. The happy path (browse
 * mode ready on the first try) returns immediately with no added wait, and a
 * genuinely missing heading still fails honestly after the retries -- the
 * assertion is never relaxed.
 */
export async function collectHeadingWalk(
    sr: any,
    headingCommand: any,
    maxSteps = 30,
    { attempts = 3, settleMs = 1500 }: { attempts?: number; settleMs?: number } = {},
): Promise<string> {
    let speech = "";
    for (let attempt = 1; attempt <= attempts; attempt++) {
        speech = await walkHeadingsOnce(sr, headingCommand, maxSteps);
        // "heading" present => browse-mode heading nav actually engaged, so judge
        // the content as-is. Absent => the virtual buffer was not ready (the known
        // CI flake); settle and retry rather than report a false a11y failure.
        if (speech.includes("heading")) {
            break;
        }
        if (attempt < attempts) {
            await new Promise((resolve) => setTimeout(resolve, settleMs));
        }
    }
    return speech;
}

/** A single heading-navigation pass; returns the joined speech, lowercased. */
async function walkHeadingsOnce(
    sr: any,
    headingCommand: any,
    maxSteps: number,
): Promise<string> {
    await sr.navigateToWebContent();
    const phrases: string[] = [(await sr.lastSpokenPhrase()) ?? ""];
    let prev = "";
    for (let i = 0; i < maxSteps; i++) {
        try {
            await sr.perform(headingCommand);
        } catch {
            break;
        }
        const phrase = (await sr.lastSpokenPhrase()) ?? "";
        if (phrase && phrase === prev) {
            break; // wrapped past the last heading — stop
        }
        phrases.push(phrase);
        prev = phrase;
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

    // The editorial-pass fix: every "Back to top" section-jumper link carries an
    // accessible name (previously an emoji-only link with no name).
    //
    // ⚠️⚠️ THIS ONE IS NOT A THEME CHECK -- IT TESTS TTT NEWSLETTER *CONTENT*.
    // Measured 2026-08-26: a TTT issue has 12 `<a aria-label="Back to top">` section
    // jumpers, which Aaron adds editorially; sterlingcreations.ca's ordinary blog post has
    // ZERO, because a blog post has no sections to jump between. Asserting it on such a
    // site fails for a reason that has nothing to do with accessibility.
    //
    // ⛔ Do NOT confuse it with the theme's floating arrow. BOTH sites render
    // `<button id="scroll-to-top" class="scroll-to-top">`; that IS a theme feature and is
    // not what this locator matches.
    //
    // Defaults ON so the TTT nightly keeps the regression test the editorial pass earned.
    if (EXPECT_BACKTOTOP) {
        const backToTop = page.getByLabel("Back to top");
        await expect(backToTop.first()).toBeVisible();
        expect(await backToTop.count()).toBeGreaterThan(0);
    }

    // The post-navigation landmark is distinctly labelled.
    await expect(page.getByRole("navigation", { name: "Post" })).toBeVisible();
}
