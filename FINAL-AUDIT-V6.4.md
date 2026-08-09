# Treevolution V6.4 Final Staging Audit

- Corrected pollarding image pixels to upright orientation.
- Renamed corrected pollarding files so browsers cannot reuse the cached sideways assets.
- Replaced the homepage's wide pollarding feature with a naturally landscape tree-reduction image.
- Rebalanced the Our Work grid and retained pollarding only as an upright portrait feature.
- Removed legacy phone-screenshot hedge assets and other unused media.
- Renamed CSS/JS to V6.4 for cache safety.
- Repaired client updater and contact response stylesheet references.
- Added client-side orientation preview and required confirmation.
- Added server-side JPEG EXIF orientation normalisation before WebP conversion.
- Corrected GitHub Actions required-file names.
- Added automated site/media audit to Actions.
- Added staging-only `/test` deployment guard.

This remains a staging build: `noindex,nofollow,noarchive`.
