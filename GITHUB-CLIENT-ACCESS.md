# Treevolution GitHub / Client Update Setup

Recommended repository:

`https://github.com/sccwebdesignwork/treevolution`

Create it as a **private, empty repository** under `sccwebdesignwork` (do not initialise with a README, licence or .gitignore). Then push/upload the contents of this package.

## Required GitHub Actions secrets

- `TREEVOLUTION_PUBLIC_HTML_DIR` — staging path, for example `/home/u288464186/domains/treevolution.uk/public_html/test`
- `TREEVOLUTION_SSH_HOST`
- `TREEVOLUTION_SSH_PORT`
- `TREEVOLUTION_SSH_USERNAME`
- `TREEVOLUTION_SSH_PASSWORD`
- `UPLOAD_USERNAME`
- `UPLOAD_PASSWORD`
- `TREEVOLUTION_GITHUB_TOKEN`

Optional repository variable:
- `TREEVOLUTION_DEPLOY_URL` — `https://treevolution.uk/test/`

Optional Maps values for the later custom map:
- `GOOGLE_MAPS_API_KEY`
- `GOOGLE_MAPS_MAP_ID`

## Client access

After deployment:

`https://treevolution.uk/test/client-update/`

The client signs in with `UPLOAD_USERNAME` and `UPLOAD_PASSWORD`.

The updater now:
1. previews the selected photograph;
2. requires explicit confirmation that it is upright;
3. normalises standard JPEG EXIF orientation before conversion;
4. converts/resizes to WebP;
5. commits the image + story to GitHub;
6. triggers the staging deploy workflow.

## Deployment safety

The included workflow refuses to deploy if `TREEVOLUTION_PUBLIC_HTML_DIR` does not end in `/test`. This prevents an accidental live-root deployment while the site is still being approved.
