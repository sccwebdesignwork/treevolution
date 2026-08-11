# Treevolution V6.7 client portal

New protected client portal at `/client/`.

## Included
- branded responsive login and dashboard
- direct access to test website
- links into existing add/edit/delete project CMS
- dedicated website-performance page
- Looker Studio embed placeholder
- favicon.ico, 16px favicon, 32px favicon, Apple touch icon and 192/512 app icons
- client-specific web app manifest
- noindex/noarchive response protection and directory listing disabled
- SCC Webdesign footer attribution

## Looker Studio connection
1. In Looker Studio create **Treevolution — Website Performance**.
2. Add the Treevolution GA4 property as the Google Analytics data source.
3. Design the first report page for desktop/tablet with: Users, Sessions, Views, New users, 30-day trend, traffic channels, top pages and devices.
4. Add a second page for Search Console when connected: Clicks, Impressions, CTR, Average position, top queries and top landing pages.
5. Share the report only with the intended client/account access model.
6. Use **File / Embed report** and enable embedding.
7. Copy only the Looker Studio embed URL beginning `https://lookerstudio.google.com/embed/reporting/`.
8. Replace the single placeholder line in `client/looker-embed-url.txt` with that URL and commit it.
9. Test `/client/performance.php` while logged into the Treevolution client portal, on desktop and mobile.

The Looker embed URL is not a password or API secret. Do not put Google service-account credentials or GA private credentials into this file.

## Test before live
Keep GitHub Actions deploying to the `/test` directory until the client authorises the live replacement. Do not change the live deployment target as part of this package.
