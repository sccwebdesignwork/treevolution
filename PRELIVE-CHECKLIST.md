# Pre-live checklist

## Current staging state
- All public HTML pages contain `noindex,nofollow,noarchive`.
- `.htaccess` sends an `X-Robots-Tag` noindex header and uses `/test/404.html`.
- `robots.txt` blocks all crawling.

## Before production root deployment
1. Change all public page robots meta tags to `index,follow,max-image-preview:large`.
2. Remove the `X-Robots-Tag` staging header from `.htaccess`.
3. Change `ErrorDocument 404 /test/404.html` to `/404.html`.
4. Replace `robots.txt` with allow + sitemap directives.
5. Validate canonical URLs and sitemap.
6. Verify `G-V335WJVEVF` consent behaviour.
7. Verify Google Business Profile link/rating before adding review schema.
8. Test `contact/send.php` email delivery on Hostinger.
9. Test client updater and GitHub deployment.
10. Run Lighthouse/PageSpeed on the production host.
