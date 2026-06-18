# Provenance

Braillewright is an in-house fork of two **GPLv2-or-later** packages by Compete Themes:

| Upstream | Version | Imported to |
|---|---|---|
| Period (theme) | 1.750 | `theme/braillewright/` |
| Period Pro (plugin) | 1.16 | `plugin/braillewright-companion/` |

## Upstream source artifacts (integrity)

| File | Size (bytes) | SHA-256 |
|---|---|---|
| period.1.750.zip | 1782565 | 5273a2008ce81788291e81726882a8f9d9953e99591c3f55786166f2752a7cd5 |
| period-pro.zip | 802075 | 41111d70071c8d34e270e35256bbbc9af666bf94fc645b84ba163d9c9e8a5348 |

Imported 2026-06-17 from Aaron's local copies. The **initial commit**
(tag `upstream-import-2026-06-17`) contains these sources **verbatim and
unmodified**, placed under the Braillewright directory layout. Every later
commit is our work (rebrand, de-license, accessibility remediation);
`git diff upstream-import-2026-06-17..HEAD` shows the full delta from upstream.

## License & attribution

Both upstream packages are GNU General Public License v2 or later, so this fork
is likewise **GPL-2.0-or-later**. Upstream copyright (Compete Themes) is retained
in file headers. Our modifications are additionally
Copyright (c) 2026 Aaron Di Blasi / MVS Ltd  *(confirm preferred holder)*.
"Period" and "Compete Themes" are the upstream author's marks and are removed
from Braillewright's own branding (trademark, separate from the code license).

## Future upstream merges

Re-import a newer upstream version onto a dedicated `vendor` branch and merge,
to keep clean three-way diffs against our changes.
