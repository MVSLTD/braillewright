import { defineConfig } from "@playwright/test";
import { screenReaderConfig } from "@guidepup/playwright";

/**
 * Screen-reader smoke config for Braillewright.
 *
 * `screenReaderConfig` (from @guidepup/playwright) supplies the settings real
 * screen readers require: a headed browser, a single worker, and no test
 * parallelism. We layer our own project split on top so CI can run
 * `--project=nvda` on a Windows runner and `--project=voiceover` on a macOS
 * runner; tests are matched by filename suffix (`*.nvda.spec.ts` /
 * `*.voiceover.spec.ts`).
 *
 * Timeouts are generous: driving a real screen reader step-by-step is slow.
 */
export default defineConfig({
    ...screenReaderConfig,
    testDir: ".",
    timeout: 5 * 60 * 1000,
    expect: { timeout: 30 * 1000 },
    // Retries are set per-project below: NVDA is reliable, VoiceOver on the macOS runner is not.
    // This top-level value is the NVDA default.
    retries: 2,
    reporter: [["list"], ["html", { open: "never" }]],
    projects: [
        // NVDA passes on the first attempt; the top-level 2 retries applies here.
        { name: "nvda", testMatch: /.*\.nvda\.spec\.ts/ },
        // VoiceOver runs on macos-14 (see the workflow matrix) to dodge the macOS 15+/26 AppleScript
        // automation regression (actions/runner-images#11257). A few retries still cover the
        // occasional slow VoiceOver start; matches the retry budget guidepup's own CI uses.
        { name: "voiceover", testMatch: /.*\.voiceover\.spec\.ts/, retries: 5 },
    ],
});
