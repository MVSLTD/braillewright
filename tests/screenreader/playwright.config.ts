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
        // VoiceOver startup under @guidepup/guidepup 0.29.0 is genuinely flaky on the macos-26 runner
        // ("Timed out waiting for VoiceOver to be running") — measured ~58% of start attempts time out
        // and only succeed on a later one (a known guidepup VoiceOver start/teardown issue; even
        // guidepup's own CI leans on retries). Give VoiceOver many retries so a slow start is absorbed
        // rather than failing the job. Failed attempts fast-fail in ~12s, well inside the 25-min budget.
        { name: "voiceover", testMatch: /.*\.voiceover\.spec\.ts/, retries: 10 },
    ],
});
