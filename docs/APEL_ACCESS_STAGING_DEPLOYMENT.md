# APEL Access staging deployment

Framework baseline: `1.11.2` (no downstream version release)

Date: `2026-10-30`

Target: `iqs@172.16.2.153:/var/www/apel`

This record preserves the commands used to validate, publish and deploy the
APEL Access user-list update. APEL is a downstream project, so this change does
not increment the IQS Framework release version. The record contains no
credentials or environment secrets.

## Local validation

```bash
cd /var/www/app/apel
php -l public/extensions/project/user-list/panel.php
php -l public/extensions/project/user-list/nav.php
php -l public/extensions/project/user-list/assets.php
php -l public/extensions/project/user-list/action.php
php -l public/lang/custom/en.php
php -l public/lang/custom/ms.php
php tests/apel-user-list-ui-regression.php
php tests/apel-user-ownership-regression.php
git diff --check
```

## Publish

```bash
cd /var/www/app/apel
git add VERSION README.md CHANGELOG.md docs/APEL_ACCESS_STAGING_DEPLOYMENT.md \
  public/extensions/project/user-list/assets.php \
  public/extensions/project/user-list/nav.php \
  public/extensions/project/user-list/panel.php \
  public/lang/custom/en.php public/lang/custom/ms.php \
  tests/apel-user-list-ui-regression.php
git commit -m "Fix APEL access administration workflow"
git push origin main
```

## Staging deployment

```bash
ssh iqs@172.16.2.153
git -C /var/www/apel status --short
git -C /var/www/apel pull --ff-only origin main
php /var/www/apel/tests/apel-user-list-ui-regression.php
php /var/www/apel/tests/apel-user-ownership-regression.php
curl -fsSIL --max-time 20 https://apel-uat.upnm.edu.my/
git -C /var/www/apel status --short
git -C /var/www/apel rev-parse HEAD
```

No database migration, configuration change or service restart is required for
this UI and client-side behaviour release.
