# Braillewright

**Braillewright** is an in-house, **accessibility-first** WordPress theme — a GPL
fork of the [Period](https://wordpress.org/themes/period/) theme (1.750) and the
Period Pro plugin (1.16) by Compete Themes, brought in-house and maintained so the
accessibility of the sites that run it can be remediated on demand rather than
waiting on an upstream that stopped responding to accessibility requests.

As of the 2026 fusion, Braillewright is a **single theme** — the former Period Pro
plugin's features (layouts, colors, fonts, header image, featured sliders/videos,
widget areas, display controls) are folded into the theme itself, and the entire
upstream `period` / `ct_period` namespace has been renamed to Braillewright. No
companion plugin is required.

## Layout

- `theme/braillewright/` — the theme (the folded-in feature modules live under `theme/braillewright/features/`)
- `tools/` — data-migration scripts (theme mods + the renamed option/meta keys)
- `docs/` — provenance, the CI harness doc, and the accessibility audit / defect register
- `.github/workflows/` — CI and the screen-reader smoke suite
- `tests/screenreader/` — Guidepup (NVDA + VoiceOver) smoke tests

## Accessibility & quality gates

Every push and pull request runs, as **blocking** gates: PHP 8.3 syntax lint,
PHPCompatibility (8.3+), PHPStan (level 5, baselined), the PHPCS WordPress security
sniffs (output escaping / nonce verification / input sanitization), and an
axe + pa11y **WCAG 2.2 AA** accessibility check against a live WordPress install.

A separate workflow runs real **NVDA + VoiceOver** screen-reader smoke tests (via
[Guidepup](https://www.guidepup.dev/)) against a deployed site. The base theme
carries WordPress.org's `accessibility-ready` tag; this fork extends it (landmark
labelling, focus visibility, link affordance, form labelling) and is verified by
both automated and manual assistive-technology testing.

The base URL the screen-reader suite tests is supplied at run time via the
`SR_BASE_URL` environment variable (in CI, a repository variable) — it is not
hard-coded in the repository.

## License & provenance

GPL-2.0-or-later, inherited from the upstream GPL packages. Upstream copyright
notices are preserved; see [`docs/PROVENANCE.md`](docs/PROVENANCE.md) for the exact
upstream versions, integrity hashes, and attribution. The names "Period" and
"Compete Themes" appear only where the GPL requires the original copyright notices
to be kept.

## Status & support

Maintained for the publisher's own sites (starting with Top Tech Tidbits) and
shared as a **community-maintained** project under the GPL. It is provided
**as-is, with no support or warranty** — issues and forks are welcome, but there is
no commitment to triage reports or to release on any particular schedule.
