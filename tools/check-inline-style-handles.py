#!/usr/bin/env python3
"""
check-inline-style-handles.py -- fail if the theme attaches inline CSS or JS to a
handle it never registers.

Why this exists
---------------
The sibling check, tools/check-enqueued-assets.py, asks "does every enqueued FILE
exist?". This one asks the inverse, and it is the inverse that was missing.

On 2026-08-25 we found `braillewright-style-rtl` used SEVEN times as the target of
wp_add_inline_style() -- background, colors, display-controls, font-sizes, fonts,
header-image and functions.php -- while nothing anywhere called wp_enqueue_style() or
wp_register_style() for it. wp_add_inline_style() returns false and does NOTHING on an
unregistered handle: no notice, no warning, no error, even with WP_DEBUG on. The seven
calls had been dead since the upstream Period 1.750 import, where the same handle
(ct-period-style-rtl) was equally dead.

That instance was harmless only by accident -- every one of the seven sat directly
beside an identical call on the registered `braillewright-style` handle, so the CSS
still reached the page. Move a payload to the -rtl call alone, or add an eighth call
site that only targets the dead handle, and the CSS vanishes silently on every site.
Nothing in CI could have caught it: php -l only parses, PHPStan sees a valid call to a
real WordPress function with a plain string argument, and pa11y never loads a
right-to-left page.

What it does
------------
1. Strips PHP comments first. Without that, the prose in this very docblock -- and in
   the fix's own explanatory comments -- registers as a call site. A checker that
   trips over documentation about itself is a checker nobody will trust.
2. Collects every handle the theme registers (wp_enqueue_style / wp_register_style and
   the _script equivalents).
3. Checks every wp_add_inline_style / wp_add_inline_script call against that set,
   INCLUDING calls whose handle comes from a theme helper function: the helper's body
   is read and every string literal it can return must be registered. Without that
   step, replacing a literal handle with a helper would silently switch this check off
   -- which is exactly what happened the first time it was written.

Only theme-owned handles are failed -- those starting with a THEME_PREFIXES entry.
Attaching inline CSS to a WordPress core or plugin handle (wp-block-library,
jetpack-*) is legitimate, so those are reported as EXTERNAL for review rather than
failed. A guard that fires on correct code is a guard that gets ignored on the run
where it is right.

Anything still unresolvable is listed as UNRESOLVED rather than silently passed,
because a checker that hides what it could not check is worse than no checker.

Exit codes: 0 = every theme-owned inline-style handle is registered, 1 = at least one
is not.

Usage:  python -X utf8 tools/check-inline-style-handles.py [--verbose]
"""

import re
import sys
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parent.parent
THEME_ROOT = REPO_ROOT / "theme" / "braillewright"

# A handle starting with one of these is ours, so we are entitled to insist it exists.
THEME_PREFIXES = ("braillewright",)

REGISTERS = re.compile(
    r"wp_(?:enqueue|register)_(?:style|script)\s*\(\s*'(?P<handle>[^']+)'", re.IGNORECASE
)
REGISTERS_DYNAMIC = re.compile(
    r"wp_(?:enqueue|register)_(?:style|script)\s*\(\s*'(?P<prefix>[^']*)'\s*\.", re.IGNORECASE
)
INLINE_ANY = re.compile(r"wp_add_inline_(?:style|script)\s*\(", re.IGNORECASE)
INLINE_LITERAL = re.compile(
    r"wp_add_inline_(?:style|script)\s*\(\s*'(?P<handle>[^']+)'", re.IGNORECASE
)
INLINE_HELPER = re.compile(
    r"wp_add_inline_(?:style|script)\s*\(\s*(?P<fn>[A-Za-z_][A-Za-z0-9_]*)\s*\(\s*\)", re.IGNORECASE
)
FUNC_DEF = re.compile(r"function\s+(?P<name>[A-Za-z_][A-Za-z0-9_]*)\s*\(")
STRING_LITERAL = re.compile(r"'([^']*)'|\"([^\"$]*)\"")

# Literals inside a helper body that are WordPress status keywords, not handles.
# braillewright_customizer_style_handle() calls wp_style_is( $handle, 'registered' );
# without this the second argument gets reported as a mystery external handle.
NOT_A_HANDLE = {"registered", "enqueued", "to_do", "done"}


def strip_php_comments(src):
    """Remove // # and /* */ comments, leaving string literals intact.

    Character-by-character rather than regex, because a regex that strips '//' will
    happily eat the middle of 'https://example.com' inside a quoted URL.
    """
    out = []
    i = 0
    n = len(src)
    quote = None
    while i < n:
        c = src[i]
        nxt = src[i + 1] if i + 1 < n else ""
        if quote:
            out.append(c)
            if c == "\\" and quote in ("'", '"'):
                if i + 1 < n:
                    out.append(nxt)
                    i += 2
                    continue
            elif c == quote:
                quote = None
            i += 1
            continue
        if c in ("'", '"'):
            quote = c
            out.append(c)
            i += 1
            continue
        if c == "/" and nxt == "*":
            end = src.find("*/", i + 2)
            i = n if end == -1 else end + 2
            out.append(" ")
            continue
        if (c == "/" and nxt == "/") or c == "#":
            end = src.find("\n", i)
            i = n if end == -1 else end
            out.append(" ")
            continue
        out.append(c)
        i += 1
    return "".join(out)


def balanced_body(text, open_index):
    """Source of the {...} block starting at the first '{' at or after open_index."""
    start = text.find("{", open_index)
    if start == -1:
        return ""
    depth = 0
    for i in range(start, len(text)):
        if text[i] == "{":
            depth += 1
        elif text[i] == "}":
            depth -= 1
            if depth == 0:
                return text[start: i + 1]
    return text[start:]


def iter_php_files():
    """Every theme PHP file except the vendored update-checker library."""
    for p in sorted(THEME_ROOT.rglob("*.php")):
        rel = p.relative_to(THEME_ROOT).as_posix()
        if rel.startswith("lib/plugin-update-checker/"):
            continue
        yield p


def is_theme_handle(handle):
    return any(handle.startswith(p) for p in THEME_PREFIXES)


def main():
    verbose = "--verbose" in sys.argv

    if not THEME_ROOT.is_dir():
        print("ERROR: theme root not found: " + str(THEME_ROOT))
        return 1

    sources = {}
    for php in iter_php_files():
        raw = php.read_text(encoding="utf-8", errors="replace")
        sources[php] = strip_php_comments(raw)

    registered = set()
    dynamic_prefixes = []
    func_bodies = {}

    for php, text in sources.items():
        for m in REGISTERS.finditer(text):
            registered.add(m.group("handle"))
        for m in REGISTERS_DYNAMIC.finditer(text):
            if m.group("prefix"):
                dynamic_prefixes.append(m.group("prefix"))
        for m in FUNC_DEF.finditer(text):
            func_bodies.setdefault(m.group("name"), balanced_body(text, m.end()))

    inline_uses = []   # (file, line, handle, via)
    unresolved = []    # (file, line, snippet)

    for php, text in sources.items():
        rel_php = php.relative_to(REPO_ROOT).as_posix()
        for m in INLINE_ANY.finditer(text):
            line_no = text.count("\n", 0, m.start()) + 1
            tail = text[m.start(): m.start() + 260]

            lit = INLINE_LITERAL.match(tail)
            if lit:
                inline_uses.append((rel_php, line_no, lit.group("handle"), "literal"))
                continue

            helper = INLINE_HELPER.match(tail)
            if helper:
                fn = helper.group("fn")
                body = func_bodies.get(fn)
                if body:
                    literals = set()
                    for sm in STRING_LITERAL.finditer(body):
                        val = sm.group(1) if sm.group(1) is not None else sm.group(2)
                        if val and val not in NOT_A_HANDLE:
                            literals.add(val)
                    if literals:
                        for val in sorted(literals):
                            inline_uses.append((rel_php, line_no, val, fn + "()"))
                        continue
                unresolved.append(
                    (rel_php, line_no, "helper " + fn + "() -- no resolvable return literals")
                )
                continue

            unresolved.append((rel_php, line_no, " ".join(tail.split())[:110]))

    missing, ok, external = [], [], []
    for rel_php, line_no, handle, via in inline_uses:
        if handle in registered or any(handle.startswith(p) for p in dynamic_prefixes):
            ok.append((rel_php, line_no, handle, via))
        elif is_theme_handle(handle):
            missing.append((rel_php, line_no, handle, via))
        else:
            external.append((rel_php, line_no, handle, via))

    print("=" * 78)
    print("INLINE STYLE / SCRIPT HANDLE CHECK")
    print("=" * 78)
    print("theme root      : " + str(THEME_ROOT))
    print("handles registered by this theme : " + str(len(registered)))
    print("inline attachments resolved      : " + str(len(inline_uses)))
    print("  registered   : " + str(len(ok)))
    print("  UNREGISTERED : " + str(len(missing)) + "  (theme-owned -- build fails)")
    print("  external     : " + str(len(external)) + "  (core/plugin handles -- not checked)")
    print("  unresolved   : " + str(len(unresolved)) + "  (listed below -- not checked, not ignored)")
    print()

    if verbose and registered:
        print("--- handles this theme registers ---")
        for h in sorted(registered):
            print("  " + h)
        if dynamic_prefixes:
            print("--- dynamic registration prefixes ---")
            for p in sorted(set(dynamic_prefixes)):
                print("  " + p + "<runtime>")
        print()

    if verbose and ok:
        print("--- inline attachments on registered handles ---")
        for rel_php, line_no, handle, via in ok:
            print("  OK   " + rel_php + ":" + str(line_no) + "  " + handle + "   [via " + via + "]")
        print()

    if missing:
        print("--- UNREGISTERED THEME HANDLES (build fails) ---")
        for rel_php, line_no, handle, via in missing:
            print("  FAIL " + rel_php + ":" + str(line_no))
            print("       attaches inline CSS/JS to : '" + handle + "'   [via " + via + "]")
            print("       ...which is never passed to wp_enqueue_style/wp_register_style.")
            print("       wp_add_inline_* silently does nothing on an unregistered handle.")
        print()

    if external:
        print("--- external handles (core or plugin -- review by hand) ---")
        for rel_php, line_no, handle, via in external:
            print("  EXT  " + rel_php + ":" + str(line_no) + "  " + handle + "   [via " + via + "]")
        print()

    if unresolved:
        print("--- could NOT resolve statically (review by hand) ---")
        for rel_php, line_no, snippet in unresolved:
            print("  SKIP " + rel_php + ":" + str(line_no) + "  " + snippet)
        print()

    if missing:
        print("RESULT: FAIL -- " + str(len(missing))
              + " inline attachment(s) target a handle this theme never registers.")
        return 1

    print("RESULT: PASS -- every theme-owned inline-style handle is registered.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
