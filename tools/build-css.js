#!/usr/bin/env node
/**
 * build-css.js -- regenerate every minified theme stylesheet from its source,
 * and (with --check) fail the build if a committed .min.css has drifted.
 *
 * WHY THIS EXISTS
 * ---------------
 * Before 2026-08-14 nothing rebuilt these files. They were hand-edited, or
 * de-branded by text replacement, and they drifted from their sources:
 *
 *   - theme/braillewright/style.min.css and rtl.min.css were two months stale
 *     and did not contain the search-no-results fix shipped in 2.0.4.
 *   - theme/braillewright/features/styles/style.min.css -- which IS enqueued on
 *     every left-to-right front-end page -- was missing a `line-height:1.333`
 *     declaration that its own source specifies.
 *   - theme/braillewright/features/styles/rtl.min.css did not exist at all, yet
 *     features/inc/scripts.php enqueues it on every right-to-left page.
 *
 * `--check` is wired into CI so none of that can happen quietly again.
 *
 * USAGE
 *   node tools/build-css.js            # rewrite every .min.css from its source
 *   node tools/build-css.js --check    # build in memory, exit 1 on any drift
 */

const fs = require('fs');
const path = require('path');
const CleanCSS = require('clean-css');

const REPO_ROOT = path.resolve(__dirname, '..');
const THEME = path.join(REPO_ROOT, 'theme', 'braillewright');

/**
 * Sources that must have a minified counterpart.
 *
 * Deliberately an explicit list rather than a glob, because two non-vendor
 * stylesheets must NOT be minified:
 *   - styles/editor-style.css  -- loaded by add_editor_style(), never as .min
 *   - anything under assets/font-awesome/ or lib/plugin-update-checker/, which
 *     are vendored third-party code we do not rebuild.
 */
const SOURCES = [
  'style.css',
  'rtl.css',
  'styles/admin.css',
  'styles/customizer.css',
  'styles/rtl.css',
  'features/styles/style.css',
  'features/styles/rtl.css',
  'features/styles/admin.css',
  'features/styles/customizer.css',
];

// Level 1 only. Level 2 does structural merging across rules, which can change
// cascade behaviour in edge cases -- not a trade worth making on an
// accessibility theme for a few hundred bytes.
const MINIFIER = new CleanCSS({ level: 1, returnPromise: false });

function build(rel) {
  const srcPath = path.join(THEME, rel);
  const outRel = rel.replace(/\.css$/, '.min.css');
  const outPath = path.join(THEME, outRel);

  if (!fs.existsSync(srcPath)) {
    return { rel, outRel, status: 'MISSING_SOURCE' };
  }

  const src = fs.readFileSync(srcPath, 'utf8');
  const result = MINIFIER.minify(src);

  if (result.errors && result.errors.length) {
    return { rel, outRel, status: 'ERROR', errors: result.errors };
  }

  const built = result.styles;
  const existed = fs.existsSync(outPath);
  const current = existed ? fs.readFileSync(outPath, 'utf8') : null;

  return {
    rel,
    outRel,
    outPath,
    built,
    existed,
    changed: current !== built,
    srcBytes: Buffer.byteLength(src),
    outBytes: Buffer.byteLength(built),
    warnings: result.warnings || [],
    status: 'OK',
  };
}

function main() {
  const check = process.argv.includes('--check');
  const results = SOURCES.map(build);

  const failures = results.filter((r) => r.status !== 'OK');
  const drifted = results.filter((r) => r.status === 'OK' && r.changed);

  console.log('='.repeat(74));
  console.log(check ? 'CSS BUILD CHECK (no files written)' : 'CSS BUILD');
  console.log('='.repeat(74));

  for (const r of results) {
    if (r.status === 'MISSING_SOURCE') {
      console.log(`  ERROR  ${r.rel} -- source file does not exist`);
      continue;
    }
    if (r.status === 'ERROR') {
      console.log(`  ERROR  ${r.rel}`);
      r.errors.forEach((e) => console.log(`         ${e}`));
      continue;
    }

    let state;
    if (!r.existed) state = 'WOULD CREATE (missing!)';
    else if (r.changed) state = 'WOULD REWRITE (drifted)';
    else state = 'up to date';

    console.log(
      `  ${r.changed || !r.existed ? '!' : ' '} ${r.outRel.padEnd(38)}` +
        `${String(r.srcBytes).padStart(7)} -> ${String(r.outBytes).padStart(6)}  ${state}`
    );
    r.warnings.slice(0, 3).forEach((w) => console.log(`         warning: ${w}`));
  }

  console.log('');

  if (failures.length) {
    console.log(`RESULT: FAIL -- ${failures.length} stylesheet(s) could not be built.`);
    process.exit(1);
  }

  if (check) {
    if (drifted.length) {
      console.log(
        `RESULT: FAIL -- ${drifted.length} minified stylesheet(s) do not match their source.`
      );
      console.log('        Run:  npm run build:css   then commit the result.');
      process.exit(1);
    }
    console.log('RESULT: PASS -- every minified stylesheet matches its source.');
    return;
  }

  let written = 0;
  for (const r of results) {
    if (r.changed || !r.existed) {
      fs.writeFileSync(r.outPath, r.built, 'utf8');
      written += 1;
    }
  }
  console.log(`RESULT: wrote ${written} file(s), ${results.length - written} already current.`);
}

main();
