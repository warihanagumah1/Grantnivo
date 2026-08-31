#!/usr/bin/env python3
"""Package application source and built assets, never local secrets or user data."""
import argparse
import hashlib
import json
from pathlib import Path
import zipfile

root = Path(__file__).resolve().parents[1]
parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument('--output', type=Path, default=root / 'dist/grantnivo-release.zip')
args = parser.parse_args()
manifest_path = root / 'public/build/manifest.json'
if not manifest_path.is_file():
    raise SystemExit('Build assets first: pnpm run build')
manifest = json.loads(manifest_path.read_text())
for entry in manifest.values():
    for asset in [entry['file'], *entry.get('css', []), *entry.get('assets', [])]:
        if not (manifest_path.parent / asset).is_file():
            raise SystemExit(f'Missing built asset: {asset}')

files = set()
for directory in ['app', 'bootstrap', 'config', 'database/migrations', 'database/seeders', 'database/factories', 'public', 'resources', 'routes']:
    for path in (root / directory).rglob('*'):
        relative = path.relative_to(root)
        if not path.is_file() or any(part.is_symlink() for part in [path, *path.parents]):
            continue
        if relative.parts[:2] in [('public', 'storage'), ('bootstrap', 'cache')]:
            continue
        if path.name in {'.DS_Store', 'hot', 'fonts-manifest.dev.json'} or path.suffix in {'.log', '.map'}:
            continue
        files.add(relative)
for name in ['artisan', 'composer.json', 'composer.lock', 'package.json', 'pnpm-lock.yaml', 'vite.config.js', '.npmrc', '.env.example', '.env.production.example', 'DEPLOYMENT.md', 'POLICY_REVIEW.md', 'README.md']:
    files.add(Path(name))
# Retain writable directory skeletons without their runtime contents.
for name in ['bootstrap/cache/.gitignore', 'storage/.gitignore']:
    if (root / name).is_file():
        files.add(Path(name))
for path in (root / 'storage').rglob('.gitignore'):
    files.add(path.relative_to(root))

args.output.parent.mkdir(parents=True, exist_ok=True)
with zipfile.ZipFile(args.output, 'w', zipfile.ZIP_DEFLATED) as archive:
    for relative in sorted(files):
        archive.write(root / relative, str(Path('grantnivo') / relative))
args.output.with_suffix(args.output.suffix + '.sha256').write_text(
    hashlib.sha256(args.output.read_bytes()).hexdigest() + '  ' + args.output.name + '\n'
)
print(f'Created {args.output} ({args.output.stat().st_size:,} bytes, {len(files)} files)')
print('Source + built assets only. Install production PHP dependencies on the server; see DEPLOYMENT.md.')
