# APEL UAT PHP 8.4 Migration

Tarikh: 7 Oktober 2026

Sasaran: PHP 8.3.35 kepada PHP 8.4.26

## Baseline

- Commit: `20583682714fc9a4018df134e2e95f2a9e6d8d94`
- Versi aplikasi: `1.10.0`
- HTTP baseline: 200
- Nginx baseline socket: `/run/php/php8.3-fpm.sock`
- Backup: `/var/www/_migration_backups/apel-php84-20261007-224022`

## Artifak

- Pool: `docs/php84/apel84.pool.conf`
- Nginx candidate: `docs/php84/apel.nginx-php84.conf`
- Runtime probe: `docs/php84/runtime-version-probe.php`
- Cutover/rollback: `tools/php84-cutover-apel.sh`

## Status

- Baseline dan backup: selesai
- Code/dependency audit: lulus
- Nullable parameter remediation: selesai
- Dual-runtime lint: lulus
- Pool PHP 8.4: dipasang dan aktif
- Pool template match: lulus
- Socket: `/run/php/apel84.sock`, `www-data:www-data`, mode `0660`
- Pre-cutover HTTP baseline: 200
- Cutover: selesai; runtime probe mengesahkan PHP 8.4.26
- Nginx handlers: kedua-duanya menggunakan `/run/php/apel84.sock`
- Post-cutover HTTP smoke: 200, halaman login lengkap
- Post-cutover Nginx error log: tiada error baharu
- Temporary runtime probe cleanup: lulus
- PHP 8.3-FPM: kekal aktif untuk rollback
- Acceptance test: lulus, disahkan oleh pentadbir selepas feature testing

## Keputusan

Migrasi APEL UAT kepada PHP 8.4.26 selesai dan diterima. PHP 8.3-FPM dikekalkan aktif sebagai rollback sepanjang tempoh pemerhatian.
