# APEL student profile synchronization

The project-owned command `tools/cron/sync-apel-students.php` synchronizes
eligible APEL profiles into the shared authentication table without using the
IQS Framework Student directory sync.

## Source and eligibility

- Source: `apelq_student_profiles`.
- Eligible status: `student_status = '02'` only.
- Identity key: normalized `student_id`.
- Authentication identity: OneID (`f_identitySource = ONEID`).
- Student group: resolved by `ADM-STUDENT`; no environment-specific numeric
  group ID is hard-coded.

## Data boundaries

- `tbl_m_user` stores the authentication identity, profile fields, group and
  access state.
- `tbl_apel_user` stores permanent APEL ownership and uses
  `APEL_PROFILE_SYNC` as the source marker.
- Ownership remains active when a source profile becomes ineligible. The
  authentication account is disabled with `APEL INACTIVE`, preventing it from
  moving into the generic Student workflow. A later status `02` reactivates an
  account disabled by this sync.
- Manually owned APEL records are not automatically disabled unless they first
  match an eligible source profile and are adopted by the profile sync.
- No user or ownership record is physically deleted.

## Safety

- The command uses a non-blocking internal file lock.
- Each profile is processed in its own database transaction.
- Duplicate authentication identities are rejected.
- An empty eligible source never triggers bulk deactivation.
- More than one deactivation, or a deactivation ratio above 20 percent, stops
  reconciliation unless `--force-deactivate` is explicitly supplied.
- Operational errors contain a SHA-256 identifier fingerprint rather than the
  student ID or personal data.

## Commands

Dry-run:

```bash
php tools/cron/sync-apel-students.php --dry-run
```

Apply:

```bash
php tools/cron/sync-apel-students.php
```

Regression checks:

```bash
php tests/apel-student-sync-regression.php
php tests/apel-user-ownership-regression.php
php tests/apel-user-list-ui-regression.php
```

## Staging schedule

The staging job runs at minutes 02, 12, 22, 32, 42 and 52 to avoid the
existing minute 00, 05/15/25/35/45/55, 09/39 and 17 schedules:

```cron
2-52/10 * * * * /usr/bin/flock -n /var/www/apel/storage/locks/apel-student-sync.cron.lock /usr/bin/php8.4 /var/www/apel/tools/cron/sync-apel-students.php >> /var/www/apel/storage/logs/apel-student-sync.log 2>&1
```

Production must be audited and scheduled separately; installing the staging
schedule does not authorize a production cron change.
