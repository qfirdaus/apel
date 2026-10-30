<?php
declare(strict_types=1);

$apelPdo = $GLOBALS['dbMySQL'] ?? null;
$apelUsers = [];
$apelGroups = [];
$apelLoadError = '';
$apelCanViewAs = (bool)($GLOBALS['isSuperAdmin'] ?? false);

try {
    if (!$apelPdo instanceof PDO || !apel_user_ownership_table_exists($apelPdo)) {
        throw new RuntimeException('Struktur pemilikan pengguna APEL belum tersedia.');
    }

    $stmt = $apelPdo->query(
        "SELECT u.f_userID, u.f_loginID, u.f_stafID, u.f_nama,
                COALESCE(u.f_email, '') AS f_email,
                COALESCE(u.f_namajabatan, '') AS f_namajabatan,
                COALESCE(u.f_flag, 1) AS f_flag,
                u.f_groupID, TRIM(COALESCE(u.f_groupKod, '')) AS f_groupKod,
                COALESCE(NULLIF(TRIM(g.f_groupName), ''), TRIM(COALESCE(u.f_groupKod, ''))) AS f_groupName
           FROM tbl_apel_user au
           JOIN tbl_m_user u ON u.f_userID = au.f_userID
      LEFT JOIN tbl_m_group g ON g.f_groupID = u.f_groupID
          WHERE au.f_active = 1
            AND UPPER(TRIM(COALESCE(u.f_categoryUser, ''))) = 'PELAJAR'
            AND COALESCE(u.f_statusID, 0) <> 9
       ORDER BY u.f_nama, au.f_student_identifier"
    );
    $apelUsers = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $groupStmt = $apelPdo->query(
        "SELECT f_groupID, f_groupKod, f_groupName
           FROM tbl_m_group
          WHERE UPPER(TRIM(COALESCE(f_categoryUser, ''))) = 'PELAJAR'
       ORDER BY f_groupName"
    );
    $apelGroups = $groupStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $apelLoadError = 'Data APEL tidak dapat dimuatkan buat masa ini.';
    error_log('[apel-user-list] ' . $e->getMessage());
}
?>
<div class="tab-pane fade" id="tab-apel-access" role="tabpanel"
  aria-labelledby="tab-apel-access-tab" tabindex="0">
  <div class="row">
    <div class="col-12">
      <div class="card apel-user-card">
        <div class="card-body">
      <?php if ($apelLoadError !== ''): ?>
        <div class="alert alert-danger mb-3"><?= h($apelLoadError) ?></div>
      <?php endif; ?>
      <div class="table-responsive dt-standard">
        <table class="table table-bordered align-middle user-access-table user-access-table--sixcol apel-user-table" id="userDTApel">
          <thead><tr>
            <th class="col-bil"><?= h((string)__('userList_col_no')) ?></th>
            <th class="col-nama"><?= h((string)__('apelUser_col_name_id')) ?></th>
            <th class="col-jabatan"><?= h((string)__('apelUser_col_unit_program')) ?></th>
            <th class="col-group"><?= h((string)__('userList_col_group')) ?></th>
            <th class="col-akses"><?= h((string)__('userList_col_access')) ?></th>
            <th class="col-actions"><?= h((string)__('userList_col_actions')) ?></th>
          </tr></thead>
          <tbody>
          <?php foreach ($apelUsers as $index => $apelUser):
              $apelId = trim((string)($apelUser['f_loginID'] ?: $apelUser['f_stafID']));
              $enabled = (int)$apelUser['f_flag'] === 1;
          ?>
            <tr data-user-id="<?= (int)$apelUser['f_userID'] ?>">
              <td class="col-bil"><?= $index + 1 ?></td>
              <td class="col-nama"><div class="user-name-shell"><span class="truncate-1line cell-tooltip-text" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= h($apelUser['f_nama'] . ' (' . $apelId . ')') ?>"><?= h($apelUser['f_nama']) ?> (<?= h($apelId) ?>)</span></div></td>
              <td class="col-jabatan"><span class="truncate-1line cell-tooltip-text" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= h($apelUser['f_namajabatan']) ?>"><?= h($apelUser['f_namajabatan'] ?: '-') ?></span></td>
              <td class="col-group"><span class="cell-inline"><span class="group-chip cell-tooltip-text" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= h($apelUser['f_groupName'] ?: '-') ?>"><?= h($apelUser['f_groupName'] ?: '-') ?></span></span></td>
              <td class="col-akses"><span class="access-chip <?= $enabled ? 'is-allowed' : 'is-blocked' ?> cell-tooltip-text" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= h($enabled ? (string)__('userList_access_granted') : (string)__('userList_access_blocked')) ?>"><?= h($enabled ? (string)__('userList_access_granted') : (string)__('userList_access_blocked')) ?></span></td>
              <td class="col-actions">
                <?php if ($apelCanViewAs && $enabled && $apelId !== ''): ?>
                <button type="button" class="btn btn-outline-warning btn-sm icon-btn apel-view-as-user"
                  data-login-id="<?= h($apelId) ?>" data-user-name="<?= h($apelUser['f_nama']) ?>"
                  title="<?= h((string)__('apelUser_action_view_as')) ?>" aria-label="<?= h((string)__('apelUser_action_view_as')) ?>"><i class="ri-eye-line"></i></button>
                <?php endif; ?>
                <button type="button" class="btn btn-outline-primary btn-sm icon-btn apel-edit-user<?= $apelCanViewAs && $enabled && $apelId !== '' ? ' ms-1' : '' ?>"
                  data-user='<?= h(json_encode($apelUser, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>'
                  title="<?= h((string)__('apelUser_action_edit')) ?>" aria-label="<?= h((string)__('apelUser_action_edit')) ?>"><i class="ri-pencil-line"></i></button>
                <button type="button" class="btn btn-outline-danger btn-sm icon-btn apel-delete-user ms-1"
                  data-user-id="<?= (int)$apelUser['f_userID'] ?>" data-user-name="<?= h($apelUser['f_nama']) ?>"
                  title="<?= h((string)__('apelUser_action_delete')) ?>" aria-label="<?= h((string)__('apelUser_action_delete')) ?>"><i class="ri-delete-bin-line"></i></button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="apelSyncLogModal" tabindex="-1" aria-hidden="true" aria-labelledby="apelSyncLogModalTitle">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="apelSyncLogModalTitle"><i class="ri-history-line me-2"></i><?= h((string)__('apelUser_cron_log_title')) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h((string)__('userList_modal_btn_close')) ?>"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger d-none" id="apelSyncLogError"></div>
        <div class="table-responsive dt-standard">
          <table class="table table-bordered align-middle w-100" id="apelSyncLogTable">
            <thead><tr>
              <th><?= h((string)__('apelUser_log_time')) ?></th>
              <th><?= h((string)__('apelUser_log_trigger')) ?></th>
              <th><?= h((string)__('apelUser_log_status')) ?></th>
              <th><?= h((string)__('apelUser_log_source')) ?></th>
              <th><?= h((string)__('apelUser_log_created')) ?></th>
              <th><?= h((string)__('apelUser_log_updated')) ?></th>
              <th><?= h((string)__('apelUser_log_deactivated')) ?></th>
              <th><?= h((string)__('apelUser_log_duration')) ?></th>
            </tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="ri-close-line me-1"></i><?= h((string)__('userList_modal_btn_close')) ?></button></div>
    </div></div>
  </div>

  <div class="modal fade" id="apelUserModal" tabindex="-1" aria-hidden="true" aria-labelledby="apelUserModalTitle">
    <div class="modal-dialog"><div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="apelUserModalTitle"><i class="ri-user-add-line me-2" id="apelUserModalIcon"></i><span id="apelUserModalTitleText"><?= h((string)__('apelUser_modal_add_title')) ?></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h((string)__('userList_modal_btn_close')) ?>"></button>
      </div>
      <div class="modal-body">
        <ul class="nav nav-pills au-modal-tabs" id="apelModalTabs" role="tablist">
          <li class="nav-item" role="presentation"><button class="nav-link active" id="apel-info-tab" data-bs-toggle="tab" data-bs-target="#apel-info-pane" type="button" role="tab" aria-controls="apel-info-pane" aria-selected="true"><i class="ri-user-line me-1"></i><?= h((string)__('apelUser_section_info')) ?></button></li>
          <li class="nav-item" role="presentation"><button class="nav-link" id="apel-settings-tab" data-bs-toggle="tab" data-bs-target="#apel-settings-pane" type="button" role="tab" aria-controls="apel-settings-pane" aria-selected="false"><i class="ri-settings-3-line me-1"></i><?= h((string)__('apelUser_section_settings')) ?></button></li>
        </ul>
        <div class="alert alert-danger d-none" id="apelUserError"></div>
        <input type="hidden" id="apelUserId" value="0">
        <div class="tab-content">
          <div class="tab-pane fade show active au-tab-pane" id="apel-info-pane" role="tabpanel" aria-labelledby="apel-info-tab" tabindex="0">
            <div class="form-section"><div class="form-section-title"><i class="ri-user-line me-1"></i><?= h((string)__('apelUser_section_info')) ?></div>
              <div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="apelIdentifier"><i class="ri-id-card-line"></i><?= h((string)__('apelUser_label_id')) ?> <span class="text-danger">*</span></label><input class="form-control" id="apelIdentifier" maxlength="10" required><div class="form-text"><?= h((string)__('apelUser_id_help')) ?></div></div>
                <div class="col-md-6"><label class="form-label" for="apelName"><i class="ri-user-3-line"></i><?= h((string)__('apelUser_label_name')) ?> <span class="text-danger">*</span></label><input class="form-control" id="apelName" maxlength="255" required></div>
                <div class="col-md-6"><label class="form-label" for="apelEmail"><i class="ri-mail-line"></i><?= h((string)__('apelUser_label_email')) ?></label><input class="form-control" id="apelEmail" type="email" maxlength="255"></div>
                <div class="col-md-6"><label class="form-label" for="apelUnit"><i class="ri-building-line"></i><?= h((string)__('apelUser_label_unit')) ?></label><input class="form-control" id="apelUnit" maxlength="255"></div>
              </div>
            </div>
          </div>
          <div class="tab-pane fade au-tab-pane" id="apel-settings-pane" role="tabpanel" aria-labelledby="apel-settings-tab" tabindex="0">
            <div class="form-section"><div class="form-section-title"><i class="ri-settings-3-line me-1"></i><?= h((string)__('apelUser_section_settings')) ?></div>
              <div class="mb-3"><label class="form-label" for="apelGroup"><i class="ri-group-line"></i><?= h((string)__('apelUser_label_group')) ?> <span class="text-danger">*</span></label><select class="form-select" id="apelGroup" required><option value=""><?= h((string)__('apelUser_select_group')) ?></option><?php foreach ($apelGroups as $group): ?><option value="<?= (int)$group['f_groupID'] ?>" data-code="<?= h($group['f_groupKod']) ?>"><?= h($group['f_groupName']) ?></option><?php endforeach; ?></select></div>
              <div class="mb-0"><label class="form-label" for="apelAccess"><i class="ri-shield-check-line"></i><?= h((string)__('apelUser_label_access')) ?></label><select class="form-select" id="apelAccess"><option value="1"><?= h((string)__('userList_access_granted')) ?></option><option value="0"><?= h((string)__('userList_access_blocked')) ?></option></select></div>
              <div class="alert alert-info py-2 px-3 mb-0 mt-3 small"><i class="ri-information-line me-1"></i><?= h((string)__('apelUser_managed_help')) ?></div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="ri-close-line me-1"></i><?= h((string)__('userList_modal_btn_cancel')) ?></button><button type="button" class="btn btn-primary" id="apelSaveUser"><i class="ri-save-3-line me-1"></i><?= h((string)__('userList_modal_btn_save')) ?></button></div>
    </div></div>
  </div>
</div>
