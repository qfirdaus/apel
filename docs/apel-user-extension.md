# APEL user-list extension

The APEL user tab is project-owned and intentionally isolated from the IQS
Framework Student tab.

## Data boundary

- Authentication records remain in `tbl_m_user` with
  `f_categoryUser = 'PELAJAR'` for compatibility with existing access control.
- Ownership is explicit in `tbl_apel_user`.
- The framework Student query excludes active `tbl_apel_user` records.
- Framework Student add and sync operations reject APEL-owned identifiers.
- APEL never reads from or synchronises with the IQS Student directory source.

## Project-owned components

- `public/extensions/project/user-list.php` registers the tab, filters and
  sync guards through the generic framework contract.
- `public/extensions/project/user-list/` contains the navigation, panel,
  assets and the dedicated CRUD endpoint.
- Deletion archives ownership and disables the authentication record; it does
  not physically delete historical application data.
- Create, update and archive actions are written to the audit trail with the
  `apel_user` target type.

## Upgrade safety

Framework synchronisation excludes `public/extensions/project/`. After every
framework upgrade, run:

```bash
php tests/apel-user-ownership-regression.php
php tests/apel-user-list-ui-regression.php
```

The second test also detects removal of the three generic render slots and any
accidental dependency on framework Student endpoints or directory sources.

## Installation

Run the ownership migration once in each environment:

```bash
php tools/install-apel-user-ownership.php
```

The installer is idempotent and verifies that every ownership row points to an
existing `tbl_m_user` record.
