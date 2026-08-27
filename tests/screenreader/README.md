# Braillewright screen-reader smoke suite (NVDA + VoiceOver)

Automated screen-reader tests driven by [Guidepup](https://www.guidepup.dev/)
through [`@guidepup/playwright`](https://github.com/guidepup/guidepup-playwright).
They drive **real** screen readers — NVDA on Windows, VoiceOver on macOS — against a
**live deploy** and assert what each one actually announces.

The suite defaults to Top Tech Tidbits (staging in one job, production in the other), and
since 2026-08-26 it can be pointed at **any Braillewright site** — see
[Testing another site](#testing-another-site) below. **Every default preserves the nightly
TTT run exactly**, so supplying nothing changes nothing.

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
| Issue | "Back to top" section-jumper links carry accessible names (editorial-pass fix) — **only when `SR_EXPECT_BACKTOTOP` is not `0`; see below** |
| Issue | `navigation` "Post" landmark announces |

⚠️ **The "Back to top" row is the one check here that is NOT about the theme.** It tests TTT
newsletter **content**: a TTT issue carries 12 `<a aria-label="Back to top">` section jumpers that
are added editorially, while an ordinary blog post carries **zero**, because it has no sections to
jump between. Do not confuse it with the theme's floating arrow — every site renders
`<button id="scroll-to-top">`, and that is not what this locator matches.

Each test prints the full spoken log so the CI artifact shows exactly what NVDA /
VoiceOver said — use that to tighten assertions over time.

## Running it

In CI: `.github/workflows/screenreader.yml` — on demand (`workflow_dispatch`, with five
optional inputs, all documented below) and nightly (`schedule`).

⚠️ **It has no `pull_request` trigger, so it is NOT a merge gate.** Code reaches `main` and
auto-deploys to staging without NVDA or VoiceOver having run. To exercise it against a
change first, dispatch at the branch:
`gh workflow run screenreader.yml -R MVSLTD/braillewright --ref <branch>`.

⛔ **`concurrency` is keyed to the ref with `cancel-in-progress: true`, so two dispatches on
the same ref cancel each other.** Run them one at a time.

Locally (needs a real Mac or Windows machine; screen readers can't run on Linux):

```bash
cd tests/screenreader
npm ci
npx @guidepup/setup        # one-time: installs portable NVDA / enables VoiceOver
npx playwright install chromium
npm run test:nvda          # on Windows
npm run test:voiceover     # on macOS
```

## Testing another site

Five settings drive the suite. Each is a `workflow_dispatch` input in CI and an environment
variable locally. **Every default is the Top Tech Tidbits value, so leaving them all blank
reproduces the nightly run byte-for-byte.**

| Dispatch input | Env var | Default | What it is |
|---|---|---|---|
| `url` | `SR_BASE_URL` | the staging **secret**, or the TTT prod literal | The site to test |
| `issue_path` | `SR_ISSUE_PATH` | `/newsletter-06-11-2026/` | A single-post address. A TTT newsletter URL 404s everywhere else |
| `brand` | `SR_BRAND` | `top tech tidbits` | A lowercase phrase the screen reader must announce on the HOME page |
| `issue_brand` | `SR_ISSUE_BRAND` | same as `brand` | The phrase it must announce on the POST page |
| `expect_backtotop` | `SR_EXPECT_BACKTOTOP` | `1` | `0` for an ordinary blog, whose posts have no section jumpers |

⚠️ `SR_BASE_URL` comes from a repository **SECRET**, not a repository variable. GitHub masks
secrets in logs and does not mask variables, and this repo is public.

### ⛔ The brand token must be a phrase the HEADING WALK reaches

Not merely text on the page, and not necessarily the site name. `SR_BRAND=sterling` against
`https://sterlingcreations.ca/` failed while the site was completely fine: that site's front
page is a **static page**, so its `h1` is the page title "Welcome!" rather than the site
title. TTT's front page is the blog index, so there the site title *is* the `h1`.

👉 **Check `show_on_front`. If it is `page`, use a word from THAT PAGE'S title.** Measured
2026-08-26 — `sterlingcreations.com`: `Home`; `sterlingcreations.ca`: `Welcome!`.

### ⛔ One token cannot serve both pages

A post's `h1` is its own title, not the site's. `SR_BRAND=welcome` passed the home spec and
failed the issue spec, correctly. Set `SR_ISSUE_BRAND` to a word from the post at
`SR_ISSUE_PATH`. TTT never needed this only because its newsletters happen to carry the site
name in their headings.

### Locally

```bash
SR_BASE_URL=https://example.com \
SR_ISSUE_PATH=/some-post/ \
SR_BRAND="example site" \
SR_EXPECT_BACKTOTOP=0 \
npx playwright test
```

📌 A fuller operational guide — how to pull the spoken log out of a run artifact, how to tell
a cold-buffer flake from a real regression, and the still-open stale-buffer bug — lives
outside this repo at `C:\Main Docs\Braillewright\docs\BRAILLEWRIGHT-SCREENREADER-CI-PLAYBOOK.md`.
