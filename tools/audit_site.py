#!/usr/bin/env python3
from pathlib import Path
from urllib.parse import urlsplit
import hashlib
import os
import re
import sys
from PIL import Image

ROOT = Path(__file__).resolve().parents[1]
errors = []

allowed_root_files = {
    '.gitignore', '.htaccess', '404.html', 'README.md', 'favicon.ico',
    'index.html', 'robots.txt', 'site.webmanifest', 'sitemap.xml'
}
allowed_root_dirs = {
    '.github', 'about', 'accessibility', 'areas', 'assets', 'client', 'contact',
    'content', 'cookies', 'our-work', 'privacy', 'reviews', 'services', 'terms', 'tools'
}
for p in ROOT.iterdir():
    if p.is_file() and p.name not in allowed_root_files:
        errors.append(f'unexpected root file: {p.name}')
    elif p.is_dir() and p.name not in allowed_root_dirs and p.name != '.git':
        errors.append(f'unexpected root directory: {p.name}')

for forbidden in ['client-update', 'assets/video', 'assets/img/v7']:
    if (ROOT / forbidden).exists():
        errors.append(f'legacy path must not exist: {forbidden}')

runtime_files = [p for p in ROOT.rglob('*') if p.is_file() and p.suffix.lower() in {'.html','.php','.css','.js','.xml','.json','.webmanifest'}]
for p in runtime_files:
    text = p.read_text(encoding='utf-8', errors='ignore')
    for marker in ['treevolution-v6-', 'treevolution-v7', 'client-update/', 'assets/img/v7/']:
        if marker in text:
            errors.append(f'{p.relative_to(ROOT)}: legacy runtime reference {marker}')

public_html = [p for p in ROOT.rglob('*.html') if 'client' not in p.parts]
attr_re = re.compile(r'(?:href|src|poster|action)=["\']([^"\'#]+)["\']', re.I)
srcset_re = re.compile(r'srcset=["\']([^"\']+)["\']', re.I)
site_mode = os.environ.get('TREEVOLUTION_ENV', 'staging').strip().lower()

for page in public_html:
    text = page.read_text(encoding='utf-8')
    h1_count = len(re.findall(r'<h1\b', text, re.I))
    if h1_count != 1:
        errors.append(f'{page.relative_to(ROOT)}: expected 1 H1, found {h1_count}')
    rel = page.relative_to(ROOT).as_posix()
    if site_mode == 'production':
        expected = 'name="robots" content="noindex,follow"' if rel == '404.html' else 'name="robots" content="index,follow"'
        if expected not in text:
            errors.append(f'{page.relative_to(ROOT)}: production robots meta incorrect; expected {expected}')
    elif 'name="robots" content="noindex,nofollow,noarchive"' not in text:
        errors.append(f'{page.relative_to(ROOT)}: staging noindex meta missing')

    vals = attr_re.findall(text)
    for group in srcset_re.findall(text):
        vals.extend(item.strip().split()[0] for item in group.split(','))
    for val in vals:
        if val.startswith(('http://','https://','mailto:','tel:','data:','javascript:')):
            continue
        clean = urlsplit(val).path
        target = (page.parent / clean).resolve()
        if clean.endswith('/'):
            target = target / 'index.html'
        if not target.exists():
            errors.append(f'{page.relative_to(ROOT)}: missing local reference {val}')

image_files = []
for base in [ROOT/'assets', ROOT/'content/uploads']:
    if base.exists():
        image_files += [p for p in base.rglob('*') if p.is_file() and p.suffix.lower() in {'.webp','.jpg','.jpeg','.png','.ico'}]
for img in image_files:
    try:
        with Image.open(img) as im:
            im.verify()
    except Exception as exc:
        errors.append(f'{img.relative_to(ROOT)}: unreadable image ({exc})')

hashes = {}
for img in image_files:
    digest = hashlib.sha256(img.read_bytes()).hexdigest()
    if digest in hashes:
        errors.append(f'duplicate media: {img.relative_to(ROOT)} duplicates {hashes[digest]}')
    else:
        hashes[digest] = img.relative_to(ROOT)

if errors:
    print('AUDIT FAILED')
    for err in errors:
        print(' -', err)
    sys.exit(1)
print(f'AUDIT PASSED: clean repository; {len(public_html)} public HTML pages; local links and {len(image_files)} media files validated.')
