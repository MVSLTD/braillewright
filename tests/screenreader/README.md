# Braillewright screen-reader smoke suite (NVDA + VoiceOver)

Automated screen-reader tests driven by [Guidepup](https://www.guidepup.dev/)
through [`@guidepup/playwright`](https://github.com/guidepup/guidepup-playwright).
They drive **real** screen readers — NVDA on Windows, VoiceOver on macOS — against
the **live TTT staging deploy** and assert what each one actually announces.

This suite is deliberately separate from the root CI (`.github/workflows/ci.yml`):

- It needs a Windows runner (NVDA) and a macOS runner (VoiceOver), not the Ubuntu
  `wp-env` matrix, and macOS minutes bill at 10×.
- It verifies the **deployed** site (real menus, widgets, issue content), not a
  PR's code diff. The `pa11y` job in `ci.yml` already gates theme/plugin code on
  every PR. This answers the different question: *does the rendered site announce
  correctly to a screen reader?*

It also has its **own** `package.json` so its heavy Guidepup/Playwright deps never
touch the root a11y toolchain or the PR-gating pipeline.

## What it checks (starter smoke)

| Page | Check |
|---|---|
| Home | Skip link is first focus and activates to `#main` |
| Home | `banner`, `navigation` "Primary", `main`, `contentinfo` landmarks announce with names |
| Home | Primary-nav toggle exposes `aria-expanded` (mobile viewport) |
| Home | Header search control — checked, **auto-skips** (it does not render on TTT templates) |
| Issue | `h1.post-title` + `h2` section headings present |
| Issue | "Back to top" section-jumper links carry accessible names (editorial-pass fix) |
| Issue | `navigation` "Post" landmark announces |

Each test prints the full spoken log so the CI artifact shows exactly what NVDA /
VoiceOver said — use that to tighten assertions over time.

## Running it

In CI: `.github/workflows/screenreader.yml` — on demand (`workflow_dispatch`, with
an optional `url` input) and nightly (`schedule`).

Locally (needs a real Mac or Windows machine; screen readers can't run on Linux):

```bash
cd tests/screenreader
npm ci
npx @guidepup/setup        # one-time: installs portable NVDA / enables VoiceOver
npx playwright install chromium
npm run test:nvda          # on Windows
npm run test:voiceover     # on macOS
```

Override the target with `SR_BASE_URL=https://example.com npx playwright test`.
