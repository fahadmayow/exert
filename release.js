const fs = require('node:fs/promises');
const path = require('node:path');
const os = require('node:os');
const { execFileSync } = require('node:child_process');
const readline = require('node:readline/promises');

function nextVersion(current, bump) {
  if (!/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/.test(current)) {
    throw new Error('package.json version must be a stable major.minor.patch version.');
  }
  const parts = current.split('.').map(BigInt);
  const index = ['major', 'minor', 'patch'].indexOf(bump);
  if (index === -1) throw new Error('Choose major, minor, or patch.');
  parts[index] += 1n;
  for (let i = index + 1; i < parts.length; i++) parts[i] = 0n;
  return parts.join('.');
}

function releaseNotes(changelog, version) {
  const lines = changelog.split(/\r?\n/);
  let start = -1;
  let fence = null;
  for (let i = 0; i < lines.length; i++) {
    const marker = lines[i].match(/^ {0,3}(`{3,}|~{3,})/);
    if (marker) {
      if (!fence) fence = marker[1];
      else if (marker[1][0] === fence[0] && marker[1].length >= fence.length) fence = null;
      continue;
    }
    if (fence) continue;
    if (!/^##\s+/.test(lines[i])) continue;
    if (start !== -1) return lines.slice(start, i).join('\n').trim();
    const heading = lines[i].match(/^##\s+(?:\[v?(\d+\.\d+\.\d+)\]|v?(\d+\.\d+\.\d+))(?=\s|$)/);
    if (heading && (heading[1] || heading[2]) === version) start = i + 1;
  }
  return start === -1 ? '' : lines.slice(start).join('\n').trim();
}

async function release({ cwd = __dirname, question, log = console.log, run } = {}) {
  run ||= (command, args) => execFileSync(command, args, {
    cwd, encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'],
  }).trim();
  const packagePath = path.join(cwd, 'package.json');
  const source = await fs.readFile(packagePath, 'utf8');
  const pkg = JSON.parse(source);
  let bump;
  do {
    bump = (await question('Release type (major/minor/patch) [patch]: ')).trim().toLowerCase() || 'patch';
    if (!['major', 'minor', 'patch'].includes(bump)) log('Choose major, minor, or patch.');
  } while (!['major', 'minor', 'patch'].includes(bump));

  const version = nextVersion(pkg.version, bump);
  const tag = `v${version}`;
  log(`Version: ${pkg.version} → ${version}`);
  const changelog = await fs.readFile(path.join(cwd, 'CHANGELOG.md'), 'utf8').catch(error => {
    if (error.code === 'ENOENT') return '';
    throw error;
  });
  const notes = releaseNotes(changelog, version);
  if (!notes) {
    const answer = (await question(`No release notes found for ${tag}. Release without details? [y/N]: `)).trim();
    if (!/^(y|yes)$/i.test(answer)) {
      log('Release cancelled. No changes made.');
      return;
    }
  }

  // Target the current committed code. Push it to GitHub before running this script.
  const target = run('git', ['rev-parse', 'HEAD']);
  run('gh', ['auth', 'status']);
  const temp = await fs.mkdtemp(path.join(os.tmpdir(), 'exert-release-'));
  try {
    const notesPath = path.join(temp, 'notes.md');
    await fs.writeFile(notesPath, notes);
    const url = run('gh', ['release', 'create', tag, '--target', target,
      '--title', tag, '--notes-file', notesPath, '--latest']);
    log(`Published ${tag}${url ? `: ${url}` : ''}`);

    pkg.version = version;
    const indent = source.match(/\n([\t ]+)"/)?.[1] || '  ';
    try {
      await fs.writeFile(packagePath, JSON.stringify(pkg, null, indent) + '\n');
    } catch (error) {
      throw new Error(`${tag} was published, but package.json could not be updated. Set its version to ${version} manually. ${error.message}`);
    }
    log(`Updated package.json to ${version}. Commit this version change before your next release.`);
  } finally {
    await fs.rm(temp, { recursive: true, force: true });
  }
}

if (require.main === module) {
  const rl = readline.createInterface({ input: process.stdin, output: process.stdout });
  release({ question: prompt => rl.question(prompt) })
    .catch(error => {
      console.error(`Release failed: ${error.stderr?.toString().trim() || error.message}`);
      process.exitCode = 1;
    })
    .finally(() => rl.close());
}

module.exports = { release, nextVersion, releaseNotes };
