=== Braillewright ===
Requires at least: 5.2
Tested up to: 6.7
Stable tag: 2.0.9
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: accessibility-ready, custom-logo, custom-menu, featured-images, two-columns, left-sidebar, right-sidebar

Accessibility-first WordPress theme, maintained in-house.

== Description ==

Braillewright is an accessibility-first WordPress theme maintained in-house by Aaron Di Blasi. It is a fork of the GPL-licensed Period theme (1.750), kept and remediated for WCAG 2.2 AA conformance. Its layout, color, font, header-image, and display features (forked from Period Pro 1.16) are built directly into the theme.

== Provenance ==

Forked from Period 1.750 (GPLv2-or-later) Source integrity hashes and full attribution are in docs/PROVENANCE.md in the Braillewright repository. "Period" and "Compete Themes" are the upstream author's marks and are not used in Braillewright's branding.

== Credits ==

Braillewright is created and maintained by Aaron Di Blasi of Mind Vault Solutions, Ltd. on behalf of Top Tech Tidbits, with engineering support from Claude Code.

== Changelog ==

= 2.0.9 =
* Fixed the theme's own right-to-left stylesheet cancelling the settings you chose in the Customizer. On a right-to-left site WordPress loaded rtl.css AFTER the Customizer's own styles, so 30 of 31 overlapping settings lost - including the link colour, which fell back to the same colour as body text. The stylesheet is now loaded in the proper place in the queue so your settings win.
* Added a build check that fails if theme code ever attaches Customizer styles to a stylesheet handle that was never registered. That is what let this go unnoticed: doing so fails silently, with no notice and no error anywhere.

= 2.0.8 =
* The theme name in the Infinite Scroll footer credit is now a link to the Braillewright page at https://toptechtidbits.com/braillewright/. Only the words Proudly powered by WordPress were linked before. It opens in the same tab rather than a new window.

= 2.0.7 =
* The scroll-to-top arrow no longer covers the credit line in the Infinite Scroll footer bar. At a 1280px window the text ran 40px underneath the button and the theme name was unreadable. Room is now reserved for the arrow, and only when the arrow is switched on.
* The arrow no longer covers the theme's own footer credit either. That line is centred, so it only reached the arrow once it grew long enough: at a 790px window it ran 14px underneath the button. Room is now reserved on both sides, which keeps the line centred and works the same way on right-to-left sites.
* Raised the contrast of that credit line. It shipped at #888 on a near-white bar, which measures 3.43 to 1 and fails the 4.5 to 1 that 12px text needs. It is now 15 to 1, and the WordPress link is underlined so it is still recognisable as a link.

= 2.0.6 =
* Added a second LinkedIn slot, so a site can show a company page and a personal profile side by side. Only one LinkedIn icon was available before.
* Screen readers now announce the two LinkedIn icons as LinkedIn Business Page and LinkedIn Personal Profile instead of both reading as linkedin.

= 2.0.5 =
* Fixed a missing stylesheet on right-to-left sites. The theme asked for features/styles/rtl.min.css on every right-to-left page and that file did not exist, so those sites lost a whole layer of styling. It has been missing since the June feature merge.
* Rebuilt every minified stylesheet from its source. Several had drifted: the root style.min.css was two months stale and still carried an old focus-outline defect, and the features stylesheet was missing a line-height its source specifies.
* Removed five dead links from the Braillewright dashboard in WordPress admin and pointed the Changelog link at the GitHub releases page. They led nowhere and opened in a new tab.
* Added two build checks so neither problem can return quietly: minified stylesheets are now verified against their sources, and every enqueued asset is verified to exist.

= 2.0.4 =
* Fixed the "no search results" message rendering on the dark masthead instead of in the page body, and added a search form so a visitor can retry without going back.
* Fixed a class-name collision with WordPress' own body class that added unintended padding and margin to every no-results search page.

= 2.0.3 =
* Updated the dashboard Support link to the renamed GitHub organization (MVSLTD).

= 2.0.2 =
* Added the Braillewright logo to the admin dashboard, with a full descriptive alt text.
* Refreshed the theme screenshot (theme card).

= 2.0.1 =
* Added the project authorship/credit line (no functional change).

= 2.0.0 =
* First self-maintained versioned release of Braillewright.
* Built-in automatic updates: the theme keeps itself updated for your security and ongoing accessibility (self-hosted update channel, no third-party phone-home).
* Single fused theme - the former Pro feature set (layouts, colors, fonts, header image, display controls) is built in; no companion plugin.
* WCAG 2.2 AA remediation: landmark labels, visible focus indicators, accessible search and navigation, and ongoing fixes.
* Continuously verified by automated screen-reader (NVDA + VoiceOver) and accessibility checks.

= Fork - 2026 =
* Forked from Period 1.750 and Period Pro 1.16 (the Pro feature set is merged into the theme).
* Removed the EDD Software Licensing / auto-updater (no vendor phone-home).
* Rebranded to Braillewright; the former Pro plugin is now built in.
* Ongoing accessibility remediation.
