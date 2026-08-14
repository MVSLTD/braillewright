#!/usr/bin/env python3
"""
check-enqueued-assets.py -- fail if the theme enqueues an asset that does not exist.

Why this exists
---------------
On 2026-08-14 we found that theme/braillewright/features/inc/scripts.php enqueues
    BRAILLEWRIGHT_FEATURES_URL . 'styles/rtl.min.css'
for right-to-left sites, and that file is absent from the source tree AND from the
shipped release ZIP. It went unnoticed since the 2026-06-19 fusion because both live
sites are left-to-right, so is_rtl() never fires and the URL is never requested.

Nothing in CI could have caught it: php -l only parses, PHPStan does not resolve a
runtime URL constant to a path on disk, and pa11y only ever loads an LTR page.

What it does
------------
Scans the theme's PHP for wp_enqueue_style / wp_enqueue_script / wp_register_* calls
whose URL is built from a known theme-root helper, resolves each to a path on disk,
and reports any that are missing.

Resolved prefixes:
    get_template_directory_uri()   . '/<rel>'   -> theme/braillewright/<rel>
    get_stylesheet_directory_uri() . '/<rel>'   -> theme/braillewright/<rel>
    BRAILLEWRIGHT_FEATURES_URL     . '<rel>'    -> theme/braillewright/features/<rel>

Anything it cannot resolve statically is listed as SKIPPED rather than silently
ignored -- a checker that hides what it could not check is worse than no checker.

Exit codes: 0 = every resolvable enqueue exists, 1 = at least one is missing.

Usage:  py -3 tools/check-enqueued-assets.py [--verbose]
"""

import re
import sys
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parent.parent
THEME_ROOT = REPO_ROOT / "theme" / "braillewright"

# BRAILLEWRIGHT_FEATURES_URL is defined in features/bootstrap.php as
#   trailingslashit( get_template_directory_uri() ) . 'features/'
PREFIXES = {
    "get_template_directory_uri": THEME_ROOT,
    "get_stylesheet_directory_uri": THEME_ROOT,
    "BRAILLEWRIGHT_FEATURES_URL": THEME_ROOT / "features",
}

ENQUEUE_CALL = re.compile(
    r"wp_(?:enqueue|register)_(?:style|script)\s*\(", re.IGNORECASE
)

# get_template_directory_uri() . '/js/foo.js'   |   BRAILLEWRIGHT_FEATURES_URL . 'js/foo.js'
RESOLVABLE = re.compile(
    r"(get_template_directory_uri\(\)|get_stylesheet_directory_uri\(\)|BRAILLEWRIGHT_FEATURES_URL)"
    r"\s*\.\s*'(?P<rel>[^']+)'"
)

# A quoted asset-looking literal, used only to spot calls we could NOT resolve.
ASSETY = re.compile(r"\.(?:css|js)(?:\?|$)", re.IGNORECASE)


def iter_php_files():
    """Every theme PHP file except the vendored update-checker library."""
    for p in sorted(THEME_ROOT.rglob("*.php")):
        rel = p.relative_to(THEME_ROOT).as_posix()
        if rel.startswith("lib/plugin-update-checker/"):
            continue
        yield p


def extract_call(text, start):
    """Return the source of a balanced-paren call beginning at the '(' index."""
    depth = 0
    for i in range(start, len(text)):
        c = text[i]
        if c == "(":
            depth += 1
        elif c == ")":
            depth -= 1
            if depth == 0:
                return text[start : i + 1]
    return text[start : start + 400]


def main():
    verbose = "--verbose" in sys.argv

    if not THEME_ROOT.is_dir():
        print(f"ERROR: theme root not found: {THEME_ROOT}")
        return 1

    missing, found, skipped = [], [], []

    for php in iter_php_files():
        text = php.read_text(encoding="utf-8", errors="replace")
        rel_php = php.relative_to(REPO_ROOT).as_posix()

        for m in ENQUEUE_CALL.finditer(text):
            call = extract_call(text, m.end() - 1)
            line_no = text.count("\n", 0, m.start()) + 1

            hit = RESOLVABLE.search(call)
            if hit:
                prefix = hit.group(1).replace("()", "")
                rel = hit.group("rel").lstrip("/")
                target = PREFIXES[prefix] / rel
                entry = (rel_php, line_no, f"{prefix} + {rel}", target)
                if target.is_file():
                    found.append(entry)
                else:
                    missing.append(entry)
            elif ASSETY.search(call):
                # An asset-looking enqueue we could not resolve statically.
                skipped.append((rel_php, line_no, " ".join(call.split())[:110]))

    print("=" * 78)
    print("ENQUEUED ASSET CHECK")
    print("=" * 78)
    print(f"theme root : {THEME_ROOT}")
    print(f"resolved   : {len(found)} present, {len(missing)} MISSING")
    print(f"unresolved : {len(skipped)} (listed below -- not checked, not ignored)")
    print()

    if verbose and found:
        print("--- present ---")
        for rel_php, line_no, expr, target in found:
            print(f"  OK   {rel_php}:{line_no}  {expr}")
        print()

    if missing:
        print("--- MISSING (build fails) ---")
        for rel_php, line_no, expr, target in missing:
            print(f"  FAIL {rel_php}:{line_no}")
            print(f"       enqueues : {expr}")
            print(f"       expected : {target.relative_to(REPO_ROOT).as_posix()}")
        print()

    if skipped:
        print("--- could NOT resolve statically (review by hand) ---")
        for rel_php, line_no, snippet in skipped:
            print(f"  SKIP {rel_php}:{line_no}  {snippet}")
        print()

    if missing:
        print(f"RESULT: FAIL -- {len(missing)} enqueued asset(s) do not exist on disk.")
        return 1

    print("RESULT: PASS -- every resolvable enqueued asset exists.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
