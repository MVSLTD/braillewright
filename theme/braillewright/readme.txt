=== Braillewright ===
Requires at least: 5.2
Tested up to: 6.7
Stable tag: 2.0.16
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: accessibility-ready, custom-logo, custom-menu, featured-images, two-columns, left-sidebar, right-sidebar

Accessibility-first WordPress theme, maintained in-house.

== Description ==

Braillewright is an accessibility-first WordPress theme maintained in-house by Aaron Di Blasi. It is a fork of the GPL-licensed Period theme (1.750), remediated against the WCAG 2.2 AA success criteria and checked on every change by automated accessibility tests and nightly screen-reader runs. Its layout, color, font, header-image, and display features (forked from Period Pro 1.16) are built directly into the theme.

== Provenance ==

Forked from Period 1.750 (GPLv2-or-later) Source integrity hashes and full attribution are in docs/PROVENANCE.md in the Braillewright repository. "Period" and "Compete Themes" are the upstream author's marks and are not used in Braillewright's branding.

== Credits ==

Braillewright is created and maintained by Aaron Di Blasi of Mind Vault Solutions, Ltd. on behalf of Top Tech Tidbits, with engineering support from Claude Code.

== Changelog ==

= 2.0.16 =
* With Bold the Current Page in the Menu switched on, the current page's item in a dropdown menu is no longer drawn twice as heavy. The theme has always shown that item in bold type, and the option's heavier outline was added on top of it. Now the outline alone marks the current page, in the dropdown as in the top row.
* One scroll bar in the Customizer. At some display scaling settings, WordPress let the open settings section scroll inside the settings panel, which already scrolls, so two scroll bars appeared side by side. Now only the panel scrolls.

= 2.0.15 =
* When the front page shows your latest posts (Settings, Reading, Your latest posts), breadcrumbs now show Home, then Blog, on it, and Home, then Blog, then Page 2 on its later pages. A static front page still shows no breadcrumbs. As before, nothing shows until breadcrumbs are switched on under Appearance, Customize, Breadcrumbs.

= 2.0.14 =
* The logo spacing setting is now Space Above and Below the Menu. On pages without breadcrumbs it also sets the space from the bottom of the menu's words to the page below, so the space above and below the menu is always the same. Before this, a page without breadcrumbs, such as the home page, kept the theme's fixed 30 pixel gap under the menu, 38 pixels as seen on toptechtidbits.com. Pages with breadcrumbs keep the breadcrumbs' own spacing.

= 2.0.13 =
* No more empty space under an image logo. The logo used to sit on a line of text sized for the site title, and the room that line keeps for letters like g and y stayed empty below it, 8 to 16 pixels on the sites measured. A tagline beside the logo does not move. The logo's link now wraps the image exactly, so the keyboard focus outline fits the logo.
* New Space Between the Logo and the Menu setting in the Customizer's Logo section, measured as you see it, from the bottom of the logo to the tops of the menu's words. It starts at 48 pixels.
* New Primary Menu Links (Current Page) color in Colors, Menus: the text of the link to the page being viewed. Left empty, it keeps your link color where that is easy to read on the current-page background, and uses black or white where it is not. Before this, the current page's link kept the ordinary link color on any background, for example white on yellow at 1.51 to 1.
* New Bold the Current Page in the Menu and Bold Menu Links on Hover options, both off by default. The letters are drawn heavier without getting wider, so the menu never shifts.
* The Customizer warns under the current-page and hover colors when either measures below 4.5 to 1 on its menu background.

= 2.0.12 =
* New Breadcrumb Page setting on each category's edit screen (Posts, Categories, Edit). Choose a page, and posts in that category show that page in their breadcrumbs, linked, instead of the category. For example, a site whose newsletters are filed under a category named Newsletter, while visitors know the section as its Newsletters page, now shows Home, then Newsletters, then the issue. The page brings its own parent pages with it, and the blog page is not added in front of it. It is empty by default, so updating changes nothing until you choose a page.

= 2.0.11 =
* Breadcrumbs are now built into the theme, with no plugin needed. Switch them on under Appearance, Customize, Breadcrumbs. They are off by default, so updating changes nothing until you choose to.
* The trail sits in a box above the content, in your site's link color, so it reads the same on any header color. The old breadcrumbs were white text on the header, and a long title ran off the header onto the gray page, where it could not be read.
* Screen readers find the trail as a labeled navigation region and a list, hear which item is the current page, and do not hear the separators. The "skip to content" link moves past it the same way it moves past the menu.
* Posts show their category, and on sites with a separate blog page, the blog page too. When an SEO plugin has stored a post's main category, that category is used.
* Emoji are left out of the trail by default, because screen readers read each one aloud. Your page titles are not changed.
* Search engines get the same trail as structured data. With Yoast SEO active, the trail is handed to Yoast, so its structured data matches what visitors see instead of listing a different one.
* Every breadcrumb setting is in the one Breadcrumbs section, with Title Case names such as Link Color on Hover: the background (a box like the theme's other boxes, the header color, the page color, or a color you choose), the text size, the text, link and hover colors, and the margin around the trail. Colors left empty are picked to contrast with the background, and the Customizer warns when a color you choose measures below 4.5 to 1.
* One setting, Margin Around the Breadcrumbs, sets the space around the trail. Inside the box the space is the same on all four sides as you see it: the extra height of each line of text is taken off the top and bottom, which otherwise looked twice as roomy as the sides at large text sizes. Outside the box, the space above the trail, under the menu, equals the space below it; the theme's usual 30 pixel gap under the menu is reduced on pages that show a trail, allowing for the space the menu's own text already leaves.
* A long page title continues on the same line as the links before it and wraps from there, instead of dropping to a line of its own. A very long trail is cut off on screen after three lines; screen readers still read all of it.
* The theme's text uses American spelling throughout, for example color and gray.
* Theme files now carry the time they last changed in their web address, so an update reaches visitors whose browsers had saved the previous version. WordPress.com tells browsers to keep theme files for ten years, and the address used to change only when the theme's version number did.
* Yoast SEO's own breadcrumbs, for sites that turned them on in Yoast, now use the same box and settings.

= 2.0.10 =
* Featured videos render again. The featured-image slot ran through wp_kses_post(), which does not allow iframe, so every featured video shipped as an empty div. Measured on WordPress 7.1: 448 bytes in, 218 out. The slot now uses wp_kses() with an allowlist that adds iframe and source; script is still not allowed.
* Migration tools: theme mods are read from the active stylesheet instead of a hardcoded theme_mods_period, a destination holding only the theme's first-boot keys is no longer mistaken for an already-migrated site, and a refusal now exits non-zero instead of reading as success.
* New tools to carry per-post settings and editor panel positions across a Period to Braillewright cutover.

= 2.0.9 =
* Fixed the theme's own right-to-left stylesheet canceling the settings you chose in the Customizer. On a right-to-left site WordPress loaded rtl.css AFTER the Customizer's own styles, so 30 of 31 overlapping settings lost - including the link color, which fell back to the same color as body text. The stylesheet is now loaded in the proper place in the queue so your settings win.
* Added a build check that fails if theme code ever attaches Customizer styles to a stylesheet handle that was never registered. That is what let this go unnoticed: doing so fails silently, with no notice and no error anywhere.

= 2.0.8 =
* The theme name in the Infinite Scroll footer credit is now a link to the Braillewright page at https://toptechtidbits.com/braillewright/. Only the words Proudly powered by WordPress were linked before. It opens in the same tab rather than a new window.

= 2.0.7 =
* The scroll-to-top arrow no longer covers the credit line in the Infinite Scroll footer bar. At a 1280px window the text ran 40px underneath the button and the theme name was unreadable. Room is now reserved for the arrow, and only when the arrow is switched on.
* The arrow no longer covers the theme's own footer credit either. That line is centered, so it only reached the arrow once it grew long enough: at a 790px window it ran 14px underneath the button. Room is now reserved on both sides, which keeps the line centered and works the same way on right-to-left sites.
* Raised the contrast of that credit line. It shipped at #888 on a near-white bar, which measures 3.43 to 1 and fails the 4.5 to 1 that 12px text needs. It is now 15 to 1, and the WordPress link is underlined so it is still recognizable as a link.

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
