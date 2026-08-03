/**
 * Block-grammar audit.
 *
 * Every non-self-closing `<!-- wp:x -->` must have a matching, correctly-nested
 * `<!-- /wp:x -->`. Catches the invisible missing/mismatched block closers that
 * only surface later as an editor "invalid content" error. Scans patterns/,
 * templates/, and parts/. Exits non-zero on any imbalance.
 *
 * Run: npm run lint:blocks
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const themeRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

function audit(file) {
  const lines = fs.readFileSync(file, 'utf8').split('\n');
  const stack = [];
  const errors = [];

  lines.forEach((line, i) => {
    const ln = i + 1;
    const re = /<!--\s*([\s\S]*?)\s*-->/g;
    let m;
    while ((m = re.exec(line)) !== null) {
      const inner = m[1].trim();
      if (inner.startsWith('/wp:')) {
        const name = inner.slice(4).split(/\s/)[0];
        const top = stack[stack.length - 1];
        if (!top) {
          errors.push(`L${ln}: closing </wp:${name}> with empty stack`);
        } else if (top.name !== name) {
          errors.push(`L${ln}: closing </wp:${name}> but open block is <wp:${top.name}> (opened L${top.line})`);
        } else {
          stack.pop();
        }
      } else if (inner.startsWith('wp:')) {
        if (inner.endsWith('/')) continue; // self-closing
        stack.push({ name: inner.slice(3).split(/[\s{]/)[0], line: ln });
      }
    }
  });

  stack.forEach((b) => errors.push(`UNCLOSED <wp:${b.name}> opened at L${b.line}`));
  return errors;
}

const targets = [];
for (const sub of ['patterns', 'templates', 'parts']) {
  const dir = path.join(themeRoot, sub);
  if (!fs.existsSync(dir)) continue;
  for (const f of fs.readdirSync(dir)) {
    if (/\.(php|html)$/.test(f)) targets.push(path.join(sub, f));
  }
}

let bad = 0;
for (const t of targets.sort()) {
  const errs = audit(path.join(themeRoot, t));
  if (errs.length) {
    bad++;
    console.log(`\n✗ ${t}`);
    errs.forEach((e) => console.log(`    ${e}`));
  }
}
console.log(`\nAudited ${targets.length} files — ${bad ? `${bad} with imbalance` : 'ALL BALANCED ✅'}`);
process.exit(bad ? 1 : 0);
