# Braillewright CI/CD (Phase 2)

Continuous integration for the Braillewright theme + Braillewright Pro plugin.
Every push to `main` and every pull request runs the workflow in
[`.github/workflows/ci.yml`](../.github/workflows/ci.yml). A separate weekly
[`security-scan.yml`](../.github/workflows/security-scan.yml) watches for
compatibility/advisory drift.

## What runs

| Job | Tool | Gate | Notes |
|---|---|---|---|
| **PHP syntax lint** | `php -l` on PHP 8.3 | **Blocking** | All 56 PHP files must parse on the Atomic runtime. |
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
| `phpcs.xml.dist` | PHPCS ruleset: WordPress-Extra + security; text domains + kept prefixes whitelisted; `tgm/`, `languages/`, min assets excluded. |
| `phpcompat.xml.dist` | PHPCompatibility ruleset, `testVersion 8.3-`. |
| `phpstan.neon.dist` | PHPStan level 5; WP stubs via `szepeviktor/phpstan-wordpress`; `tgm/` + `woocommerce.php` excluded; baseline enabled. |
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
- They test the **live TTT staging deploy** (real menus, widgets and issue
  content) — not a PR's code diff. The `pa11y` job above already gates
  theme/plugin code on every PR; this verifies what staging actually *announces*.

**Trigger:** on demand (`workflow_dispatch`, with an optional `url` input) and
nightly (`schedule`, 07:00 UTC). **Scope (starter smoke):** skip link → `#main`;
the `banner` / `Primary` navigation / `main` / `contentinfo` landmarks; the
mobile primary-nav expand/collapse state; and on an issue page the `Post`
landmark + headings + the `Back to top` link labels. The header search control
is checked but auto-skips (it does not render on the live TTT templates).

The suite has its **own** `tests/screenreader/package.json` (Guidepup +
Playwright) so it never affects the root a11y toolchain or PR-gating CI. See
[`tests/screenreader/README.md`](../tests/screenreader/README.md) to run it
locally on a Mac/Windows box.

## Scope reminder

CI gates the **theme + plugin code we maintain**. The vendored
`theme/braillewright/tgm/` (TGM Plugin Activation) is excluded from our linting;
keep it updated from upstream TGMPA separately. The accessibility job validates
template-level a11y on a clean WordPress install — it is **not** a substitute for
the manual AT testing in
[`a11y-audit-ttt-2026-06.md`](a11y-audit-ttt-2026-06.md), and it does not see
TTT's content/widget defects (those are an editorial, content-safety concern).
