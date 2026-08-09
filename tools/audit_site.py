#!/usr/bin/env python3
from pathlib import Path
from urllib.parse import urlsplit
import re
import sys
from PIL import Image

ROOT = Path(__file__).resolve().parents[1]
errors = []
public_html = [p for p in ROOT.rglob("*.html") if "client-update" not in p.parts]

attr_re = re.compile(r'(?:href|src|poster)=["\']([^"\'#]+)["\']', re.I)
srcset_re = re.compile(r'srcset=["\']([^"\']+)["\']', re.I)

for page in public_html:
    text = page.read_text(encoding="utf-8")
    h1_count = len(re.findall(r"<h1\b", text, re.I))
    if h1_count != 1:
        errors.append(f"{page.relative_to(ROOT)}: expected 1 H1, found {h1_count}")
    if 'name="robots" content="noindex,nofollow,noarchive"' not in text:
        errors.append(f"{page.relative_to(ROOT)}: staging noindex meta missing")
    if "assets/css/site.css" in text or "assets/js/site.js" in text or "treevolution-v6-2" in text or "treevolution-v6-3" in text:
        errors.append(f"{page.relative_to(ROOT)}: stale pre-V6.4/legacy asset reference")

    vals = attr_re.findall(text)
    for group in srcset_re.findall(text):
        vals.extend(item.strip().split()[0] for item in group.split(","))
    for val in vals:
        if val.startswith(("http://","https://","mailto:","tel:","data:","javascript:")):
            continue
        clean = urlsplit(val).path
        target = (page.parent / clean).resolve()
        if clean.endswith("/"):
            target = target / "index.html"
        if not target.exists():
            errors.append(f"{page.relative_to(ROOT)}: missing local reference {val}")

for img in (ROOT / "assets/img").iterdir():
    if img.suffix.lower() not in {".webp",".jpg",".jpeg",".png"}:
        continue
    try:
        with Image.open(img) as im:
            im.verify()
    except Exception as exc:
        errors.append(f"{img.relative_to(ROOT)}: unreadable image ({exc})")

contracts = {
    "treevolution-pollarding-project-upright-1080.webp": "portrait",
    "treevolution-pollarding-project-upright-720.webp": "portrait",
    "treevolution-van-sussex-1920.webp": "landscape",
    "treevolution-van-sussex-1440.webp": "landscape",
    "treevolution-hedge-cutting-clean-sussex.webp": "landscape",
    "tree-reduction-project.webp": "landscape",
}
for name, expected in contracts.items():
    path = ROOT / "assets/img" / name
    if not path.exists():
        errors.append(f"orientation contract missing asset: {name}")
        continue
    with Image.open(path) as im:
        w, h = im.size
    actual = "portrait" if h > w else "landscape" if w > h else "square"
    if actual != expected:
        errors.append(f"{name}: expected {expected}, got {w}x{h} ({actual})")

for forbidden in [
    "treevolution-pollarding-project-1440.webp",
    "treevolution-pollarding-project-960.webp",
    "treevolution-hedge-cutting-project-1170.webp",
    "treevolution-hedge-cutting-project-960.webp",
    "treevolution-hedge-cutting-project-640.webp",
    "treevolution-team-tree-removal-sussex-1440.webp",
]:
    if (ROOT / "assets/img" / forbidden).exists():
        errors.append(f"forbidden legacy asset still present: {forbidden}")

if errors:
    print("AUDIT FAILED")
    for err in errors:
        print(" -", err)
    sys.exit(1)

print(f"AUDIT PASSED: {len(public_html)} public HTML pages; links, staging noindex, image decoding and orientation contracts validated.")
