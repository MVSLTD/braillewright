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
    retries: 1,
    reporter: [["list"], ["html", { open: "never" }]],
    projects: [
        { name: "nvda", testMatch: /.*\.nvda\.spec\.ts/ },
        { name: "voiceover", testMatch: /.*\.voiceover\.spec\.ts/ },
    ],
});
