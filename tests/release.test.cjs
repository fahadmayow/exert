const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs/promises');
const { readFileSync } = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const { release } = require('../release.js');

async function fixture(t, { answers, changelog, fail = false }) {
  const cwd = await fs.mkdtemp(path.join(os.tmpdir(), 'exert-release-test-'));
  t.after(() => fs.rm(cwd, { recursive: true, force: true }));
  const original = JSON.stringify({ name: 'exert', version: '0.1.9', scripts: { release: 'node release.js' } }, null, 2) + '\n';
  await fs.writeFile(path.join(cwd, 'package.json'), original);
  if (changelog !== undefined) await fs.writeFile(path.join(cwd, 'CHANGELOG.md'), changelog);
  const calls = [];
  const prompts = [];
  let publishedNotes;
  const execute = () => release({
    cwd,
    question: async prompt => {
      prompts.push(prompt);
      assert.ok(answers.length, 'Unexpected prompt');
      return answers.shift();
    },
    log: () => {},
    run: (command, args) => {
      calls.push([command, ...args]);
      if (command === 'git') return 'abc123';
      if (args[0] === 'auth') return '';
      assert.equal(readFileSync(path.join(cwd, 'package.json'), 'utf8'), original);
      publishedNotes = readFileSync(args[args.indexOf('--notes-file') + 1], 'utf8');
      if (fail) throw new Error('GitHub rejected release');
      return 'https://github.com/example/exert/releases/tag/' + args[2];
    },
  });
  return { execute, calls, prompts, original,
    contents: () => fs.readFile(path.join(cwd, 'package.json'), 'utf8'),
    notes: () => publishedNotes };
}

for (const [bump, version] of [['', '0.1.10'], ['minor', '0.2.0'], ['major', '1.0.0']]) {
  test(`${bump || 'default patch'} publishes only matching notes and then updates version`, async t => {
    const f = await fixture(t, { answers: [bump], changelog:
      `# Changelog\n\n## Unreleased\nFuture work\n\n## [v${version}] — 2026-09-11\n\n### Fixed\n- Fix middleware.\n\n## v0.1.9\nOlder notes\n` });
    await f.execute();
    assert.equal(f.notes(), '### Fixed\n- Fix middleware.');
    assert.equal(JSON.parse(await f.contents()).version, version);
    assert.equal(JSON.parse(await f.contents()).scripts.release, 'node release.js');
    assert.deepEqual(f.calls[2].slice(0, 8), ['gh', 'release', 'create', `v${version}`, '--target', 'abc123', '--title', `v${version}`]);
    assert.equal(f.prompts.length, 1);
  });
}

test('missing notes default to cancellation without invoking GitHub or changing files', async t => {
  const f = await fixture(t, { answers: ['', ''] });
  await f.execute();
  assert.deepEqual(f.calls, []);
  assert.equal(await f.contents(), f.original);
});

test('empty matching section requires confirmation and yes permits an empty release', async t => {
  const f = await fixture(t, { answers: ['invalid', 'patch', 'yes'], changelog: '## 0.1.10\n\n## 0.1.9\nOld notes' });
  await f.execute();
  assert.equal(f.notes(), '');
  assert.equal(JSON.parse(await f.contents()).version, '0.1.10');
  assert.equal(f.prompts.length, 3);
});

test('GitHub failure leaves the package version unchanged', async t => {
  const f = await fixture(t, { answers: ['patch'], changelog: '## v0.1.10\nFix.', fail: true });
  await assert.rejects(f.execute(), /GitHub rejected release/);
  assert.equal(await f.contents(), f.original);
});
