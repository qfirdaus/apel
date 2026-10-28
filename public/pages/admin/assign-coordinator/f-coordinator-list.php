<!-- ROLE INDICATOR -->
<div class="role-indicator-wrapper">
    <span class="role-indicator-label">
        <i class="ri-shield-user-line"></i> Mod Peranan
    </span>
    <span class="role-indicator-name">
        <span class="role-dot"></span>
        <?= h($roleInfo['name'] ?? 'Pentadbir') ?>
    </span>
</div>

<div class="d-flex justify-content-end mb-3">
    <button type="button" 
            class="btn btn-primary px-3 shadow-sm d-inline-flex align-items-center" 
            data-bs-toggle="modal" 
            data-bs-target="#modalAddFacultyCoordinator">
        <i class="ri-user-add-line me-1"></i> Tambah Rekod
    </button>
</div>

<div class="dt-standard">
    <table class="table table-striped table-bordered align-middle w-100 text-center" id="tblFacultyCoordinators">
        <thead class="bg-light">
            <tr>
                <th width="5%">BIL</th>
                <th width="20%" class="text-start">FAKULTI / JABATAN</th>
                <th width="45%" class="text-start">NAMA PENYELARAS</th>
                <th width="15%">TARIKH DILANTIK</th>
                <th width="15%">TINDAKAN</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($coordinatorList)): $bil = 1; foreach ($coordinatorList as $row): ?>
                <tr>
                    <td><?= $bil++ ?></td>
                    <td class="text-start">
                        <strong class="text-dark"><?= h($row['faculty_name'] ?? $row['faculty_code']) ?></strong><br>
                        <small class="badge bg-info-subtle px-2 text-info border"><?= h($row['faculty_code']) ?></small>
                    </td>
                    <td class="text-start">
                        <strong class="text-dark"><?= h($row['coordinator_name'] ?? $row['coordinator_id']) ?></strong><br>
                        <small class="badge bg-info-subtle px-2 text-info border"><?= h($row['coordinator_id']) ?> | <?= h($row['coordinator_position'] ?? '-') ?></small>
                    </td>
                    <td><?= !empty($row['assigned_at']) ? date('d-M-Y', strtotime($row['assigned_at'])) : '-' ?></td>
                    <td>
                        <button type="button" 
                                class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-remove-coord"
                                data-id="<?= (int)$row['id'] ?>"
                                data-name="<?= h($row['coordinator_name'] ?? $row['coordinator_id']) ?>"
                                data-faculty="<?= h($row['faculty_name'] ?? $row['faculty_code']) ?>"
                                title="Gugurkan Lantikan">
                            <i class="ri-delete-bin-line me-1"></i> 
                        </button>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>