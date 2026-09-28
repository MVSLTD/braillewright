#!/usr/bin/env node
/**
 * build-js.js -- regenerate every minified theme script from its sources, and
 * (with --check) fail the build if a committed .min.js has drifted.
 *
 * WHY THIS EXISTS
 * ---------------
 * Aaron, 2026-09-27: "Why don't you have a working JS build tool?" The theme's
 * minified scripts were built by Period's gulp pipeline, which never came over in
 * the fork, and nothing replaced it. tools/build-css.js was built on 2026-08-14,
 * when the stylesheets turned out to have drifted from their sources; the scripts
 * never got the same. The first change that needed a script edited (removing the
 * "PRO" tags from the Customizer, 2.0.18) had no way to rebuild
 * features/js/build/customizer.min.js.
 *
 * The first rebuild (2026-09-27) was checked file by file against the committed
 * copies, re-minifying both sides through the same terser pass and diffing them:
 * five matched apart from how each minifier ordered and named things, and ONE had
 * really drifted. The 2026-06-17 rebrand (a34ed5b) edited features/js/postMessage.js
 * and its .min.js by separate text replacements, so the Customizer preview showed a
 * placeholder "Braillewright WordPress Theme" link (href="#") when the footer text
 * was emptied, while the page itself prints nothing (footer.php). The rebuild makes
 * the preview match the page. ⚠️ A `git log` of today's paths does not show that
 * commit: the files lived under plugin/braillewright-pro/ before the fusion (dbf9e75).
 * --compare-committed re-runs the equivalence check.
 *
 * USAGE
 *   node tools/build-js.js                        # rewrite every .min.js from its sources
 *   node tools/build-js.js --check                # build in memory, exit 1 on any drift
 *   node tools/build-js.js --compare-committed    # the equivalence proof described above
 */

const fs = require('fs');
const path = require('path');
const { minify } = require('terser');

const REPO_ROOT = path.resolve(__dirname, '..');
const THEME = path.join(REPO_ROOT, 'theme', 'braillewright');

/**
 * Every minified script and the sources it is built from, in order. A list, not a
 * glob, because two outputs combine two sources each (FitVids, then the menu code),
 * joined by one line break, as Period built them (js/build/production.js measured
 * byte-identical to fitvids.js + "\n" + functions.js). js/build/production.js is
 * that joined, unminified file; it is regenerated too so it cannot drift.
 */
const TARGETS = [
  { out: 'js/build/production.min.js', plain: 'js/build/production.js', sources: ['js/fitvids.js', 'js/functions.js'] },
  { out: 'js/build/customizer.min.js', sources: ['js/customizer.js'] },
  { out: 'js/build/postMessage.min.js', sources: ['js/postMessage.js'] },
  // functions.js ALONE: the committed functions.min.js calls .fitVids() but does not contain the plugin
  // (the theme's production.min.js already loads it), measured 2026-09-27 by diffing the two.
  { out: 'features/js/build/functions.min.js', sources: ['features/js/functions.js'] },
  { out: 'features/js/build/admin.min.js', sources: ['features/js/admin.js'] },
  { out: 'features/js/build/customizer.min.js', sources: ['features/js/customizer.js'] },
  { out: 'features/js/build/postMessage.min.js', sources: ['features/js/postMessage.js'] },
];

// terser is pinned to one exact version in package.json, so the same source always
// gives the same bytes and --check cannot fail on a minifier upgrade nobody chose.
// Licence comments (/*! ... */, FitVids' MIT notice) are kept.
const OPTIONS = { compress: true, mangle: true, format: { comments: /^!/ } };

function read(rel) {
  return fs.readFileSync(path.join(THEME, rel), 'utf8');
}

async function build(target) {
  const missing = target.sources.filter((s) => !fs.existsSync(path.join(THEME, s)));
  if (missing.length) {
    return { target, status: 'MISSING_SOURCE', missing };
  }
  const joined = target.sources.map(read).join('\n');
  let result;
  try {
    result = await minify(joined, OPTIONS);
  } catch (e) {
    return { target, status: 'ERROR', error: String(e && e.message ? e.message : e) };
  }
  const outPath = path.join(THEME, target.out);
  const existed = fs.existsSync(outPath);
  const current = existed ? fs.readFileSync(outPath, 'utf8') : null;
  const plainPath = target.plain ? path.join(THEME, target.plain) : null;
  const plainCurrent = plainPath && fs.existsSync(plainPath) ? fs.readFileSync(plainPath, 'utf8') : null;
  return {
    target,
    status: 'OK',
    joined,
    built: result.code,
    existed,
    current,
    changed: current !== result.code || (plainPath !== null && plainCurrent !== joined),
    outPath,
    plainPath,
  };
}

async function compareCommitted(r) {
  // Re-minify what is committed, with the same options, and compare with the build from source.
  if (!r.existed) return 'no committed file';
  let again;
  try {
    again = (await minify(r.current, OPTIONS)).code;
  } catch (e) {
    return 'the committed file does not parse: ' + e.message;
  }
  const fromSource = (await minify(r.built, OPTIONS)).code;
  return again === fromSource ? 'SAME' : 'DIFFERENT';
}

async function main() {
  const check = process.argv.includes('--check');
  const compare = process.argv.includes('--compare-committed');
  const results = [];
  for (const t of TARGETS) {
    results.push(await build(t));
  }

  console.log('='.repeat(74));
  console.log(compare ? 'JS EQUIVALENCE (committed .min.js vs a build from source)' : check ? 'JS BUILD CHECK (no files written)' : 'JS BUILD');
  console.log('='.repeat(74));

  let failures = 0;
  let different = 0;
  for (const r of results) {
    if (r.status === 'MISSING_SOURCE') {
      console.log(`  ERROR  ${r.target.out} -- missing source: ${r.missing.join(', ')}`);
      failures++;
      continue;
    }
    if (r.status === 'ERROR') {
      console.log(`  ERROR  ${r.target.out} -- ${r.error}`);
      failures++;
      continue;
    }
    if (compare) {
      const verdict = await compareCommitted(r);
      if (verdict !== 'SAME') different++;
      console.log(`  ${verdict === 'SAME' ? ' ' : '!'} ${r.target.out.padEnd(40)} ${verdict}`);
      continue;
    }
    const state = !r.existed ? 'WOULD CREATE (missing!)' : r.changed ? 'WOULD REWRITE (drifted)' : 'up to date';
    console.log(`  ${r.changed || !r.existed ? '!' : ' '} ${r.target.out.padEnd(40)}${String(Buffer.byteLength(r.joined)).padStart(7)} -> ${String(Buffer.byteLength(r.built)).padStart(6)}  ${check ? state : r.changed ? 'written' : 'up to date'}`);
    if (!check && r.changed) {
      fs.writeFileSync(r.outPath, r.built);
      if (r.plainPath) fs.writeFileSync(r.plainPath, r.joined);
    }
  }
  console.log('');
  if (failures) {
    console.log(`RESULT: FAIL -- ${failures} script(s) could not be built.`);
    process.exit(1);
  }
  if (compare) {
    console.log(different ? `RESULT: ${different} file(s) differ from a build of their source.` : 'RESULT: every committed .min.js does the same as a build of its source.');
    process.exit(different ? 1 : 0);
  }
  const drifted = results.filter((r) => r.changed);
  if (check) {
    if (drifted.length) {
      console.log(`RESULT: FAIL -- ${drifted.length} minified script(s) differ from their sources. Run: node tools/build-js.js`);
      process.exit(1);
    }
    console.log('RESULT: PASS -- every minified script matches its sources.');
    return;
  }
  console.log(`RESULT: wrote ${drifted.length} file(s), ${results.length - drifted.length} already current.`);
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
