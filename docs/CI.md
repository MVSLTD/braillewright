# Braillewright CI/CD (Phase 2)

Continuous integration for the Braillewright theme + Braillewright Pro plugin.
Every push to `main` and every pull request runs the workflow in
[`.github/workflows/ci.yml`](../.github/workflows/ci.yml). A separate weekly
[`security-scan.yml`](../.github/workflows/security-scan.yml) watches for
compatibility/advisory drift.

## What runs

| Job | Tool | Gate | Notes |
|---|---|---|---|
| **PHP syntax lint** | `php -l` on PHP 8.3 | **Blocking** | Every PHP file in `theme/` + `tools/` must parse on the Atomic runtime (includes the vendored PUC). |
| **Security sniffs** | PHPCS `EscapeOutput` + `NonceVerification` + `ValidatedSanitizedInput` | **Blocking** | 0 after the Phase 3 passes (2026-06-18). |
| **Coding standards (style)** | PHPCS `WordPress-Extra` | Advisory | ~4,900 cosmetic findings; `phpcs-report` artifact. |
| **PHP 8.3+ compatibility** | PHPCompatibility (`testVersion 8.3-`) | **Blocking** | Verified 0 findings on 8.3 (2026-06-18). `phpcompat-report` artifact. |
| **Static analysis** | PHPStan level 5 + WordPress stubs | **Blocking (new issues)** | 45 inherited findings in `phpstan-baseline.neon`; fails only on regressions. |
| **Accessibility** | wp-env + pa11y-ci (axe + HTML_CodeSniffer) + Lighthouse CI | **pa11y Blocking**; Lighthouse advisory | Scans a clean WP install with the theme + plugin active. pa11y promoted to blocking 2026-06-18 (0 errors across Phase 2 + PRs #2–#4). |

## Why "advisory → blocking"

Braillewright is a fork of ~12,300 LOC of upstream code. Hard-gating everything on
day one would just paint the pipeline red against inherited debt and hide real
regressions. So jobs were introduced in report mode and promoted as their findings
were understood. As of 2026-06-18: **php-lint, PHPCompatibility, PHPStan
(baselined), the PHPCS security sniffs, and the pa11y-ci accessibility check are
blocking**; the **full PHPCS style check and Lighthouse remain advisory**
(`continue-on-error: true`) and
upload their full findings as artifacts to triage.

### The tightening path (status)

1. **PHPCompatibility — DONE (2026-06-18).** The first CI run reported 0 findings
   on 8.3, so `continue-on-error` was removed; it is now a blocking gate.
2. **PHPStan — DONE (2026-06-18).** The first run's 45 findings were committed as
   `phpstan-baseline.neon` and the baseline `include` enabled; analysis now fails
   only on **new** issues. After remediation, refresh with `composer analyse:baseline`.
3. **Security sniffs — DONE (2026-06-18).** Two Phase 3 passes resolved all
   `EscapeOutput` (XSS), `NonceVerification` (CSRF), and `ValidatedSanitizedInput`
   (`$_POST` unslash+sanitize, 13 spots across 6 save handlers) findings, so all
   three sniffs are now blocking (regression protection). The full WordPress-Extra
   **style** check stays advisory (~4,900 cosmetic findings); `composer lint:fix`
   (phpcbf) chips at the mechanically-fixable subset incrementally.
4. **Accessibility — pa11y DONE (2026-06-18).** After 0 errors across Phase 2 +
   PRs #2/#3/#4, `continue-on-error` was dropped from the pa11y-ci step, so the
   axe + HTML_CodeSniffer WCAG2AA check now blocks. Lighthouse stays advisory
   (its a11y score can vary by environment/run).

## Running it locally

Requires PHP 8.3 + Composer, and Node 20+ with Docker (for wp-env). Aaron's
workstation currently has neither PHP nor Composer, so in practice these run in
CI; the commands below are for any machine that does have them.

```
# PHP toolchain
composer install
composer lint        # PHPCS (WordPress-Extra + security)
composer compat      # PHPCompatibility 8.3+
composer analyse     # PHPStan
composer lint:fix    # auto-fix the safely-fixable PHPCS findings

# Accessibility toolchain
npm ci
npm run env:start
npm run env:activate
npm run a11y         # pa11y-ci + Lighthouse CI
npm run env:stop
```

## Configuration files

| File | Purpose |
|---|---|
| `composer.json` | Dev-only PHP toolchain + convenience scripts. The theme/plugin have **no runtime Composer deps**. |
| `phpcs.xml.dist` | PHPCS ruleset: WordPress-Extra + security; text domains + kept prefixes whitelisted; `tgm/`, `lib/plugin-update-checker/` (vendored PUC), `languages/`, min assets excluded. |
| `phpcompat.xml.dist` | PHPCompatibility ruleset, `testVersion 8.3-`; `tgm/` + `lib/plugin-update-checker/` excluded (php-lint still covers PUC syntax). |
| `phpstan.neon.dist` | PHPStan level 5; WP stubs via `szepeviktor/phpstan-wordpress`; `tgm/`, `woocommerce.php` + `lib/plugin-update-checker/` excluded from analysis (still scanned for symbols, so `PucFactory` resolves); baseline enabled. |
| `phpstan-baseline.neon` | The 45 inherited PHPStan findings, so analysis blocks only on regressions. |
| `package.json` | Node a11y toolchain (`@wordpress/env`, `pa11y-ci`, `@lhci/cli`, `@axe-core/cli`). |
| `.wp-env.json` | wp-env: latest WP, **PHP 8.3**, theme + plugin mounted. |
| `.pa11yci` | pa11y-ci: WCAG2AA, axe + HTML_CodeSniffer runners, home + a post + a page. |
| `lighthouserc.json` | Lighthouse CI: accessibility category, `minScore 0.9` (warn). |
| `.github/dependabot.yml` | Weekly Composer + npm + Actions update PRs. |

## Pinning notes (why these versions)

- **PHP_CodeSniffer is pinned to the `3.x` line (`^3.13.4`), not the `4.0`
  latest.** WPCS 3.3 (`squizlabs/php_codesniffer: ^3.13.4`) and
  PHPCompatibilityWP 2.1 (`^3.3`) do not yet support PHPCS 4.0; requiring `^4.0`
  would break `composer install`.
- **PHPStan is `2.x`** to match `szepeviktor/phpstan-wordpress ^2.0`, which pulls
  the matching `php-stubs/wordpress-stubs` itself (so we don't pin it separately).
- **`dealerdirect/phpcodesniffer-composer-installer`** is the maintained installer
  package name (the `phpcsstandards/...` rename is not published on Packagist).
  It is allow-listed in `composer.json` `config.allow-plugins` so Composer lets it
  register the WPCS/PHPCompatibility standards.
- GitHub Action majors are the current releases as of 2026-06-18:
  `actions/checkout@v6`, `actions/setup-node@v6`, `actions/upload-artifact@v7`,
  `shivammathur/setup-php@v2`.

## Screen-reader checks (separate workflow)

Real screen-reader smoke tests (NVDA + VoiceOver) live in their own workflow,
[`.github/workflows/screenreader.yml`](../.github/workflows/screenreader.yml),
driven by [Guidepup](https://www.guidepup.dev/) via `@guidepup/playwright`. They
are deliberately **not** part of `ci.yml` and **do not gate pull requests**:

- NVDA needs a **Windows** runner and VoiceOver needs a **macOS** runner (macOS
  bills at 10× Actions minutes), so they can't ride the Ubuntu `wp-env` matrix.
- They test a **live deploy** (real menus, widgets and issue content) — not a
  PR's code diff. By default that is TTT staging in one job and TTT production in
  the other. The `pa11y` job above already gates theme/plugin code on every PR;
  this verifies what a deployed site actually *announces*.

**Trigger:** on demand (`workflow_dispatch`) and nightly (`schedule`, 07:00 UTC).
**Scope (starter smoke):** skip link → `#main`; the `banner` / `Primary`
navigation / `main` / `contentinfo` landmarks; the mobile primary-nav
expand/collapse state; and on an issue page the `Post` landmark + headings + the
`Back to top` link labels. The header search control is checked but auto-skips
(it does not render on the live TTT templates).

**Since 2026-08-26 the suite can target any Braillewright site, not just TTT.**
It takes five optional dispatch inputs — `url`, `issue_path`, `brand`,
`issue_brand` and `expect_backtotop` — and **every default is the Top Tech
Tidbits value, so a scheduled run or a dispatch with everything blank reproduces
the nightly run byte-for-byte.** Full table and the two traps that cost a run
(the brand token must be a phrase the HEADING WALK reaches, and one token cannot
serve both the home page and a post) are in
[`tests/screenreader/README.md`](../tests/screenreader/README.md).

⚠️ **The `Back to top` assertion above is NOT a theme check — it tests TTT
newsletter CONTENT**, and `expect_backtotop=0` turns it off for an ordinary blog.
Measured 2026-08-26: a TTT issue carries 12 such section-jumper links, added
editorially; an ordinary blog post carries zero. It is not the theme's floating
`#scroll-to-top` arrow, which every site renders.

⚠️ **`SR_BASE_URL` is a repository SECRET, not a repository variable.** GitHub
masks secrets in logs and does **not** mask variables, and this repo is public —
while it was a variable the staging hostname appeared in plaintext 16 times in a
single nightly run's log. Changed 2026-08-14; do not move it back.

⛔ **`concurrency` is keyed to the ref with `cancel-in-progress: true`**, so two
dispatches against the same ref cancel each other. Run them sequentially.

The suite has its **own** `tests/screenreader/package.json` (Guidepup +
Playwright) so it never affects the root a11y toolchain or PR-gating CI. See
[`tests/screenreader/README.md`](../tests/screenreader/README.md) to run it
locally on a Mac/Windows box.

## Dependency updates & auto-merge

Dependabot opens **grouped** weekly PRs for the PHP toolchain (Composer), the
accessibility toolchain (npm), and the GitHub Actions themselves — minor + patch
bumps are bundled into one PR per ecosystem; **major** updates come separately.

`main` is a **protected branch**: it requires all five CI checks above to pass
before any merge (admin override on, so it never locks the maintainer out; no
required human reviewer).

### ⛔ Auto-merge was REMOVED on 2026-08-27 — every Dependabot PR is merged by a human

There used to be a `dependabot-auto-merge.yml` workflow that enabled GitHub
auto-merge on Dependabot **patch/minor** PRs. **It was deleted**, because of a
defect that only surfaced the first time it actually fired:

> **A merge performed with the built-in `GITHUB_TOKEN` does not create new
> workflow runs.** GitHub suppresses that deliberately, to prevent a workflow
> from re-triggering itself.

The workflow ran `gh pr merge --auto --squash` with `GH_TOKEN: ${{ secrets.GITHUB_TOKEN }}`,
so when its merge landed on `main`, **`ci.yml` never ran** — and `ci.yml` is where
the `package-theme` job lives, which publishes the `wpcom` artifact that
WordPress.com deploys the staging site from. So a Dependabot merge that touched
theme code would have put `main` ahead of staging **silently**: no error, no log
line, and simply no new row in the Deployments tab.

**Proven as a differential on 2026-08-27**, same repo, same workflow, same
`on: push: branches: [main]` trigger — only the merging identity differed:

| Merge | Merged by | Workflow runs on `main` |
|---|---|---|
| `0f271d2` (Dependabot PR #37) | `app/github-actions` | **0** |
| `d634d8b` (PR #44) | a real user account | **1** (`33041868922`, event `push`) |

So the trigger configuration was never wrong. **Any merge a human performs
behaves correctly; only the token-driven auto-merge was invisible to Actions.**

👉 **What happens now.** Dependabot still opens its grouped weekly PRs and the
five required checks still gate them — nothing about safety changed. A human
merges each one once those checks are green, which starts `ci.yml` on `main`
normally and therefore keeps staging in step. **Major** updates were always
manual and still are.

⚠️ **If auto-merge is ever wanted back, it must NOT use `GITHUB_TOKEN`** — it
needs a fine-grained PAT or a GitHub App installation token, or the same silent
staging drift returns.

## Scope reminder

CI gates the **theme + plugin code we maintain**. The vendored
`theme/braillewright/tgm/` (TGM Plugin Activation) and
`theme/braillewright/lib/plugin-update-checker/` (the YahnisElsts PUC self-update
library) are excluded from our linting; keep them updated from upstream
separately. The accessibility job validates
template-level a11y on a clean WordPress install — it is **not** a substitute for
the manual AT testing in
[`a11y-audit-ttt-2026-06.md`](a11y-audit-ttt-2026-06.md), and it does not see
TTT's content/widget defects (those are an editorial, content-safety concern).
