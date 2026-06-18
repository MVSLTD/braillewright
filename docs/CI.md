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
| **Coding standards + security** | PHPCS `WordPress-Extra` (incl. `WordPress.Security.*`) | Advisory → blocking | Full report uploaded as the `phpcs-report` artifact. |
| **PHP 8.3+ compatibility** | PHPCompatibility (`testVersion 8.3-`) | Advisory → blocking | `phpcompat-report` artifact. Likely the first to flip to blocking. |
| **Static analysis** | PHPStan level 5 + WordPress stubs | Advisory → blocking | Emits a regenerated `phpstan-baseline` artifact each run. |
| **Accessibility** | wp-env + pa11y-ci (axe + HTML_CodeSniffer) + Lighthouse CI | Advisory | Scans a clean WP install with the theme + plugin active. |

## Why "advisory → blocking"

Braillewright is a fork of ~12,300 LOC of upstream code. Hard-gating PHPCS and
PHPStan on day one would just paint the pipeline red against inherited debt and
hide real regressions. So the four code-quality jobs run with
`continue-on-error: true`: the pipeline goes **green**, but each job uploads its
full findings as an artifact to triage. The PHP-lint gate **is** blocking from
day one because the tree already lints clean on 8.3.

### How to make a job blocking (the tightening path)

1. **PHPCompatibility** — run the job, confirm zero (or a small, fixed set of)
   findings, then delete `continue-on-error: true` from the `php-compatibility`
   job. This is the highest-value first gate.
2. **PHPStan** — download the `phpstan-baseline` artifact from a run (or run
   `composer analyse:baseline` once a PHP/Composer env is available), commit it
   as `phpstan-baseline.neon`, uncomment the baseline `include` in
   `phpstan.neon.dist`, then drop `continue-on-error` from `static-analysis`.
   Analysis then fails only on **new** issues.
3. **Coding standards / security** — work the `phpcs-report` findings down
   (the Phase 2 pre-ship security pass targets the ~14 unescaped-output spots +
   the plugin's input handling), then drop `continue-on-error` from
   `coding-standards`. Consider `composer lint:fix` (phpcbf) for the
   mechanically-fixable subset first.

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
| `phpstan.neon.dist` | PHPStan level 5; WP stubs via `szepeviktor/phpstan-wordpress`; `tgm/` + `woocommerce.php` excluded from reporting; baseline include ready to enable. |
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

## Scope reminder

CI gates the **theme + plugin code we maintain**. The vendored
`theme/braillewright/tgm/` (TGM Plugin Activation) is excluded from our linting;
keep it updated from upstream TGMPA separately. The accessibility job validates
template-level a11y on a clean WordPress install — it is **not** a substitute for
the manual AT testing in
[`a11y-audit-ttt-2026-06.md`](a11y-audit-ttt-2026-06.md), and it does not see
TTT's content/widget defects (those are an editorial, content-safety concern).
