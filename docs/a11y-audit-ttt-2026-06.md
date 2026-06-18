# Braillewright - TTT Accessibility Audit (2026-06-17)

Automated baseline via PageSpeed Insights / Lighthouse (**axe-core** engine), mobile strategy. Automated tools catch only ~a third of WCAG issues, so the **manual AT checklist** at the end MUST be run by the blind-user testing network (JAWS/NVDA/VoiceOver + keyboard-only + zoom/reflow) for full **WCAG 2.2 AA** coverage. Each automated defect's owning codebase (theme vs plugin) is tagged during remediation by locating the markup.

## Page: https://toptechtidbits.com/

**Accessibility score: 94/100** | 2 failing automated check(s)

### Background and foreground colors do not have a sufficient contrast ratio.  (`color-contrast`, impact weight 7) - 1 element(s)
Low-contrast text is difficult or impossible for many users to read. [Learn how to provide sufficient color contrast](https://dequeuniversity.com/rules/axe/4.12/color-contrast).

- `<div class="blog-credits">`
  - selector: `body#period > div#infinite-footer > div.container > div.blog-credits`

### Links rely on color to be distinguishable.  (`link-in-text-block`, impact weight 7) - 1 element(s)
Low-contrast text is difficult or impossible for many users to read. Link text that is discernible improves the experience for users with low vision. [Learn how to make links distinguishable](https://dequeuniversity.com/rules/axe/4.12/link-in-text-block).

- `<a href="https://toptechtidbits.com/sponsorship-packages/">`
  - selector: `section#custom_html-4 > div.textwidget > p > a`

## Page: https://toptechtidbits.com/newsletter-06-11-2026/

**Accessibility score: 94/100** | 3 failing automated check(s)

### Background and foreground colors do not have a sufficient contrast ratio.  (`color-contrast`, impact weight 7) - 12 element(s)
Low-contrast text is difficult or impossible for many users to read. [Learn how to provide sufficient color contrast](https://dequeuniversity.com/rules/axe/4.12/color-contrast).

- `<span style="background-color: rgb(51, 51, 51); color: rgb(0, 0, 0); border-radius: 0.25em;">`
  - selector: `div.post-container > div.post-content > p > span`
- `<span style="background-color: rgb(51, 51, 51); color: rgb(0, 0, 0); border-radius: 0.25em;">`
  - selector: `div.post-container > div.post-content > p > span`
- `<a href="#section-jumper" style="text-decoration: none;">`
  - selector: `div.post-container > div.post-content > h2#sponsors > a`
- `<a href="#section-jumper" style="text-decoration: none;">`
  - selector: `div.post-container > div.post-content > h2#featured-advertisement > a`
- `<a href="#section-jumper" style="text-decoration: none;">`
  - selector: `div.post-container > div.post-content > h2#news > a`
- `<a href="#section-jumper" style="text-decoration: none;">`
  - selector: `div.post-container > div.post-content > h2#featured-events > a`

### Links rely on color to be distinguishable.  (`link-in-text-block`, impact weight 7) - 1 element(s)
Low-contrast text is difficult or impossible for many users to read. Link text that is discernible improves the experience for users with low vision. [Learn how to make links distinguishable](https://dequeuniversity.com/rules/axe/4.12/link-in-text-block).

- `<a href="https://toptechtidbits.com/sponsorship-packages/">`
  - selector: `section#custom_html-4 > div.textwidget > p > a`

### `<td>` elements in a large `<table>` do not have table headers.  (`td-has-header`, impact weight 0) - 1 element(s)
Screen readers have features to make navigating tables easier. Ensuring that `<td>` elements in a large table (3 or more cells in width and height) have an associated table header may improve the experience for screen reader users. [Learn more about table headers](https://dequeuniversity.com/rules/a

- `<table id="sponsor-wall" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse; border-spacing: 0px; border: none !important;">`
  - selector: `div.post-container > div.post-content > div#border-container-1 > table#sponsor-wall`

## Manual checks Lighthouse cannot automate (assign to AT testers)

- **Custom controls have associated labels** (`custom-controls-labels`)
- **Custom controls have ARIA roles** (`custom-controls-roles`)
- **User focus is not accidentally trapped in a region** (`focus-traps`)
- **Interactive controls are keyboard focusable** (`focusable-controls`)
- **Interactive elements indicate their purpose and state** (`interactive-element-affordance`)
- **The page has a logical tab order** (`logical-tab-order`)
- **The user's focus is directed to new content added to the page** (`managed-focus`)
- **Offscreen content is hidden from assistive technology** (`offscreen-content-hidden`)
- **HTML5 landmark elements are used to improve navigation** (`use-landmarks`)
- **Visual order on the page follows DOM order** (`visual-order-follows-dom`)

## Manual AT test matrix (run per template: home + issue page)

- [ ] **Keyboard-only**: tab through every interactive element; visible focus; logical order; no traps; skip-link works
- [ ] **JAWS** (Windows): headings/landmarks navigation; menu state announced; link purpose clear
- [ ] **NVDA** (Windows): same as JAWS, cross-check
- [ ] **VoiceOver** (macOS/iOS): rotor landmarks/headings; mobile menu operable
- [ ] **200% zoom / 400% reflow**: no loss of content/function; no horizontal scroll at 320px
- [ ] **Reduced motion / animations**: featured sliders/videos respect prefers-reduced-motion
