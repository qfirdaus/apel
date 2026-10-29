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
  <div class="card apel-user-card">
    <div class="card-header d-flex align-items-center justify-content-between gap-3 flex-wrap">
      <div>
        <h5 class="mb-1">Pengguna APEL</h5>
        <p class="text-muted mb-0">Rekod pelajar milik APEL. Tiada sambungan atau sync ke sumber Pelajar IQS Framework.</p>
      </div>
      <button type="button" class="btn btn-primary" id="apelAddUserBtn">
        <i class="ri-user-add-line me-1"></i> Tambah pengguna APEL
      </button>
    </div>
    <div class="card-body">
      <?php if ($apelLoadError !== ''): ?>
        <div class="alert alert-danger mb-3"><?= h($apelLoadError) ?></div>
      <?php endif; ?>
      <div class="table-responsive dt-standard">
        <table class="table table-bordered align-middle apel-user-table" id="userDTApel">
          <thead><tr>
            <th class="apel-col-no">Bil.</th><th>Nama / ID APEL</th><th>E-mel</th>
            <th>Unit / Program</th><th>Kumpulan</th><th>Akses</th><th class="apel-col-actions">Tindakan</th>
          </tr></thead>
          <tbody>
          <?php foreach ($apelUsers as $index => $apelUser):
              $apelId = trim((string)($apelUser['f_loginID'] ?: $apelUser['f_stafID']));
              $enabled = (int)$apelUser['f_flag'] === 1;
          ?>
            <tr data-user-id="<?= (int)$apelUser['f_userID'] ?>">
              <td><?= $index + 1 ?></td>
              <td><span class="apel-ellipsis" title="<?= h($apelUser['f_nama'] . ' (' . $apelId . ')') ?>"><?= h($apelUser['f_nama']) ?> <small>(<?= h($apelId) ?>)</small></span></td>
              <td><span class="apel-ellipsis" title="<?= h($apelUser['f_email']) ?>"><?= h($apelUser['f_email'] ?: '-') ?></span></td>
              <td><span class="apel-ellipsis" title="<?= h($apelUser['f_namajabatan']) ?>"><?= h($apelUser['f_namajabatan'] ?: '-') ?></span></td>
              <td><span class="badge bg-light text-dark"><?= h($apelUser['f_groupName'] ?: '-') ?></span></td>
              <td><span class="badge <?= $enabled ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>"><?= $enabled ? 'Dibenarkan' : 'Disekat' ?></span></td>
              <td>
                <?php if ($apelCanViewAs && $enabled && $apelId !== ''): ?>
                <button type="button" class="btn btn-outline-warning btn-sm apel-view-as-user"
                  data-login-id="<?= h($apelId) ?>" data-user-name="<?= h($apelUser['f_nama']) ?>"
                  title="Lihat sebagai pengguna APEL"><i class="ri-eye-line"></i></button>
                <?php endif; ?>
                <button type="button" class="btn btn-outline-primary btn-sm apel-edit-user"
                  data-user='<?= h(json_encode($apelUser, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>'
                  title="Edit pengguna APEL"><i class="ri-pencil-line"></i></button>
                <button type="button" class="btn btn-outline-danger btn-sm apel-delete-user"
                  data-user-id="<?= (int)$apelUser['f_userID'] ?>" data-user-name="<?= h($apelUser['f_nama']) ?>"
                  title="Padam pengguna APEL"><i class="ri-delete-bin-line"></i></button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="modal fade" id="apelUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
      <div class="modal-header"><div><h5 class="modal-title mb-1">Pengguna APEL</h5><small class="text-muted">Data ini diurus oleh projek APEL sahaja.</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="alert alert-danger d-none" id="apelUserError"></div>
        <input type="hidden" id="apelUserId" value="0">
        <div class="mb-3"><label class="form-label" for="apelIdentifier">ID APEL</label><input class="form-control" id="apelIdentifier" maxlength="10" required><div class="form-text">Maksimum 10 aksara dan unik dalam sistem.</div></div>
        <div class="mb-3"><label class="form-label" for="apelName">Nama penuh</label><input class="form-control" id="apelName" maxlength="255" required></div>
        <div class="mb-3"><label class="form-label" for="apelEmail">E-mel</label><input class="form-control" id="apelEmail" type="email" maxlength="255"></div>
        <div class="mb-3"><label class="form-label" for="apelUnit">Unit / Program</label><input class="form-control" id="apelUnit" maxlength="255"></div>
        <div class="row g-3"><div class="col-md-8"><label class="form-label" for="apelGroup">Kumpulan</label><select class="form-select" id="apelGroup" required><option value="">Pilih kumpulan</option><?php foreach ($apelGroups as $group): ?><option value="<?= (int)$group['f_groupID'] ?>" data-code="<?= h($group['f_groupKod']) ?>"><?= h($group['f_groupName']) ?></option><?php endforeach; ?></select></div><div class="col-md-4"><label class="form-label" for="apelAccess">Akses</label><select class="form-select" id="apelAccess"><option value="1">Dibenarkan</option><option value="0">Disekat</option></select></div></div>
      </div>
      <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary" id="apelSaveUser"><i class="ri-save-line me-1"></i> Simpan</button></div>
    </div></div>
  </div>
</div>
