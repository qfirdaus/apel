<?php
declare(strict_types=1);

class ApelAdminOfficerModel
{
    private PDO $dbMysql;
    private PDO $dbSybase;

    public function __construct(PDO $dbMysql, PDO $dbSybase)
    {
        $this->dbMysql  = $dbMysql;
        $this->dbSybase = $dbSybase;
    }

    private function setAuditUser(?string $userId = null): void
    {
        if (empty($userId)) {
            $userId = (string)($_SESSION['f_stafID'] ?? $_SESSION['user_id'] ?? 'Sistem');
        }
        $stmt = $this->dbMysql->prepare("SET @app_user = ?");
        $stmt->execute([$userId]);
    }

    /** get current role */
    public function getActiveRoleInfo(): array 
    {
        $groupId   = (int)($_SESSION['f_groupID'] ?? 0);
        $groupCode = trim((string)($_SESSION['f_groupKod'] ?? ''));

        try {
            $stmt = $this->dbMysql->prepare("SELECT f_groupID, f_groupKod, f_groupName, f_badge_class, f_color 
                                        FROM tbl_m_group 
                                        WHERE f_groupID = :gid OR f_groupKod = :gkod 
                                        LIMIT 1");
            $stmt->execute([
                ':gid'  => $groupId,
                ':gkod' => $groupCode
            ]);
            $group = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($group && !empty($group['f_groupName'])) {
                return [
                    'id'    => (int)$group['f_groupID'],
                    'code'  => (string)$group['f_groupKod'],
                    'name'  => (string)$group['f_groupName'],
                    'badge' => !empty($group['f_badge_class']) ? (string)$group['f_badge_class'] : 'bg-primary-subtle text-primary border border-primary-subtle',
                    'color' => !empty($group['f_color']) ? (string)$group['f_color'] : '#3b82f6'
                ];
            }
        } catch (\Throwable $e) {
            error_log("AdvisorModel::getActiveRoleInfo Error: " . $e->getMessage());
        }

        return [
            'id'    => $groupId,
            'code'  => $groupCode,
            'name'  => !empty($groupCode) ? $groupCode : 'Penasihat Portfolio',
            'badge' => 'bg-primary-subtle text-primary border border-primary-subtle',
            'color' => '#3b82f6'
        ];
    }

    /** Pastikan pengguna wujud di tbl_m_user dan set role ke tbl_ref_access (Multiple Roles)  */
    public function ensureUserRoleAccess(string $stafId, int $groupId, string $groupKod, string $adminId): void
    {
        $stafId = trim($stafId);
        if ($stafId === '') return;

        $stmt = $this->dbMysql->prepare("SELECT f_userID, f_groupID, f_flag FROM tbl_m_user WHERE TRIM(f_stafID) = :sid OR TRIM(f_loginID) = :lid LIMIT 1");
        $stmt->execute([':sid' => $stafId, ':lid' => $stafId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $userId = 0;
        if ($user) {
            $userId = (int)$user['f_userID'];
            if ((int)$user['f_flag'] === 0) {
                $this->dbMysql->prepare("UPDATE tbl_m_user SET f_flag = 1, f_updateby = ?, f_updatedt = NOW() WHERE f_userID = ?")
                              ->execute([$adminId, $userId]);
            }
        } else {
            $sybStmt = $this->dbSybase->prepare("SELECT TOP 1 idpekerja, nopekerja, gelar_nama, nama, nokp, email, handphone,
                                                        kdjwtsemasa, jawatansemasa, kdjenis, jenis, kdjbtnsemasa, jabatansemasa, 
                                                        kodstatus, status 
                                                 FROM ehrmdb.dbo.v630staf_service_skim_aktif 
                                                 WHERE LTRIM(RTRIM(nopekerja)) = :sid");
            $sybStmt->execute([':sid' => $stafId]);
            $syb = $sybStmt->fetch(PDO::FETCH_ASSOC);

            $gelarNama = trim((string)($syb['gelar_nama'] ?? ''));
            $namaHanya = trim((string)($syb['nama'] ?? ''));
            $namaPenuh = ($gelarNama !== '' && $namaHanya !== '') ? $gelarNama : ($gelarNama ?: ($namaHanya ?: $stafId));
            $nokp = trim((string)($syb['nokp'] ?? ''));
            $hashedPass = ($nokp !== '') ? password_hash($nokp, PASSWORD_DEFAULT) : password_hash('123456', PASSWORD_DEFAULT);

            $insUser = "INSERT INTO tbl_m_user (
                            f_loginID, f_stafID, f_categoryUser, f_nopekerja, f_nama, f_nickname, 
                            f_nokp, f_password, f_email, f_handphone, f_jawatanKod, f_jawatan, 
                            f_jenisID, f_jenis, f_jabatanKod, f_namajabatan, 
                            f_verified_at, f_must_change_password, f_statusID, f_status, 
                            f_groupID, f_groupKod, f_flag, f_insertdt, f_updatedt, f_updateby, 
                            f_remarks, f_isAutoProvisioned
                        ) VALUES (
                            :login, :stafID, 'STAF', :nopekerja, :nama, :nickname, 
                            :nokp, :pass, :email, :phone, :jwtKod, :jwt, 
                            :jnsID, :jns, :jbtnKod, :jbtn, 
                            NOW(), 0, :statusID, :status, 
                            :gid, :gkod, 1, NOW(), NOW(), :upby, 
                            'Added via APEL.Q Role Assign', 1
                        )";
            $stmtIns = $this->dbMysql->prepare($insUser);
            $stmtIns->execute([
                ':login'     => $stafId,
                ':stafID'    => $stafId,
                ':nopekerja' => !empty($syb['idpekerja']) ? trim((string)$syb['idpekerja']) : $stafId,
                ':nama'      => $namaPenuh,
                ':nickname'  => ($namaHanya !== '') ? $namaHanya : $namaPenuh,
                ':nokp'      => ($nokp !== '') ? $nokp : null,
                ':pass'      => $hashedPass,
                ':email'     => !empty($syb['email']) ? trim((string)$syb['email']) : null,
                ':phone'     => !empty($syb['handphone']) ? trim((string)$syb['handphone']) : null,
                ':jwtKod'    => !empty($syb['kdjwtsemasa']) ? trim((string)$syb['kdjwtsemasa']) : null,
                ':jwt'       => !empty($syb['jawatansemasa']) ? trim((string)$syb['jawatansemasa']) : null,
                ':jnsID'     => !empty($syb['kdjenis']) ? (int)$syb['kdjenis'] : 1,
                ':jns'       => !empty($syb['jenis']) ? trim((string)$syb['jenis']) : 'Akademik',
                ':jbtnKod'   => !empty($syb['kdjbtnsemasa']) ? trim((string)$syb['kdjbtnsemasa']) : null,
                ':jbtn'      => !empty($syb['jabatansemasa']) ? trim((string)$syb['jabatansemasa']) : null,
                ':statusID'  => !empty($syb['kodstatus']) ? (int)$syb['kodstatus'] : 1,
                ':status'    => !empty($syb['status']) ? trim((string)$syb['status']) : 'Aktif',
                ':gid'       => $groupId,
                ':gkod'      => $groupKod,
                ':upby'      => $adminId
            ]);
            $userId = (int)$this->dbMysql->lastInsertId();
        }

        $accStmt = $this->dbMysql->prepare("SELECT f_accessID, f_status FROM tbl_ref_access WHERE TRIM(f_stafID) = :sid AND f_groupID = :gid LIMIT 1");
        $accStmt->execute([':sid' => $stafId, ':gid' => $groupId]);
        $acc = $accStmt->fetch(PDO::FETCH_ASSOC);

        if (!$acc) {
            $inAcc = "INSERT INTO tbl_ref_access (f_stafID, f_userID, f_groupID, f_status, f_createdby, f_createddt) 
                      VALUES (:sid, :uid, :gid, 1, :crby, NOW())";
            $this->dbMysql->prepare($inAcc)->execute([
                ':sid'  => $stafId,
                ':uid'  => $userId,
                ':gid'  => $groupId,
                ':crby' => $adminId
            ]);
        } elseif ((int)$acc['f_status'] === 0) {
            $this->dbMysql->prepare("UPDATE tbl_ref_access SET f_status = 1, f_updatedby = ?, f_updateddt = NOW() WHERE f_accessID = ?")
                          ->execute([$adminId, $acc['f_accessID']]);
        }
    }

    /**Admin set staf sebagai Penyelaras Fakulti*/
    public function assignFacultyCoordinator(array $stafIds, string $facultyCode, string $facultyName, string $adminId): bool
    {
        try {
            if (empty($stafIds) || empty($facultyCode)) {
                return false;
            }

            $this->dbMysql->beginTransaction();
            $this->setAuditUser($adminId);

            if (trim($facultyName) === '') {
                try {
                    $stmtF = $this->dbSybase->prepare("SELECT TOP 1 jabatansemasa FROM ehrmdb.dbo.v630staf_service_skim_aktif WHERE LTRIM(RTRIM(kdjbtnsemasa)) = :fcode");
                    $stmtF->execute([':fcode' => trim($facultyCode)]);
                    $facultyName = (string)($stmtF->fetchColumn() ?: '');
                } catch (\Throwable $eSyb) {
                    $facultyName = '';
                }

                if ($facultyName === '') {
                    $stmtM = $this->dbMysql->prepare("SELECT f_namajabatan FROM tbl_m_user WHERE TRIM(f_jabatanKod) = :fcode LIMIT 1");
                    $stmtM->execute([':fcode' => trim($facultyCode)]);
                    $facultyName = (string)($stmtM->fetchColumn() ?: $facultyCode);
                }
            }

            $sql = "INSERT INTO apelq_faculty_coordinators (faculty_code, faculty_name, coordinator_id, assigned_by, assigned_at, status)
                    VALUES (:fcode, :fname, :cid, :by, NOW(), 1)
                    ON DUPLICATE KEY UPDATE 
                        faculty_name = VALUES(faculty_name),
                        status       = 1, 
                        assigned_by  = VALUES(assigned_by), 
                        assigned_at  = NOW()";
            
            $stmt = $this->dbMysql->prepare($sql);

            foreach ($stafIds as $stafId) {
                $stafId = trim((string)$stafId);
                if ($stafId === '') continue;

                // 35: ADM-COORDINATOR (Pastikan akaun staf aktif dan role dalam tbl_ref_access)
                $this->ensureUserRoleAccess($stafId, 35, 'ADM-COORDINATOR', $adminId);

                $stmt->execute([
                    ':fcode' => trim($facultyCode),
                    ':fname' => trim($facultyName),
                    ':cid'   => $stafId,
                    ':by'    => $adminId
                ]);
            }

            $this->dbMysql->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->dbMysql->inTransaction()) {
                $this->dbMysql->rollBack();
            }
            error_log("assignFacultyCoordinator Error: " . $e->getMessage());
            return false;
        }
    }

    /**Set Penasihat Pelajar*/
    public function assignAdvisorToStudent(int $appId, string $studentId, string $advisorId, string $assignedBy): bool
    {
        try {
            $this->dbMysql->beginTransaction();
            $this->setAuditUser($assignedBy);

            // 34: ADM-ADVISOR
            if (!empty($advisorId)) {
                $this->ensureUserRoleAccess($advisorId, 34, 'ADM-ADVISOR', $assignedBy);
            }

            // Simpan advisor_id 
            $sql = "INSERT INTO apelq_application_advisors (
                        application_id, student_id, advisor_id, assigned_by, assigned_at
                    ) VALUES (
                        :app_id, :student_id, :adv_id, :assigned_by, NOW()
                    )
                    ON DUPLICATE KEY UPDATE 
                        advisor_id  = VALUES(advisor_id),
                        assigned_by = VALUES(assigned_by),
                        assigned_at = NOW()";

            $stmt = $this->dbMysql->prepare($sql);
            $stmt->execute([
                ':app_id'      => $appId,
                ':student_id'  => $studentId,
                ':adv_id'      => !empty($advisorId) ? $advisorId : null,
                ':assigned_by' => $assignedBy
            ]);

            $this->dbMysql->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->dbMysql->inTransaction()) $this->dbMysql->rollBack();
            error_log("assignAdvisorToStudent Error: " . $e->getMessage());
            return false;
        }
    }

    /** Senarai permohonan berserta Penyelaras Auto-Match berasaskan Fakulti Pelajar*/
    public function getApplicationsForAssignment(?string $filterFacultyCode = null): array
    {
        $params = [];
        $whereFaculty = '';
        if (!empty($filterFacultyCode)) {
            $whereFaculty = 'WHERE u_stu.f_jabatanKod = :fcode';
            $params[':fcode'] = $filterFacultyCode;
        }

        $sql = "SELECT a.id AS application_id, 
                       a.student_id, 
                       u_stu.f_nama AS student_name,
                       u_stu.f_jabatanKod AS student_faculty_code,
                       u_stu.f_namajabatan AS student_faculty_name,
                       a.portfolio_status_id, 
                       a.portfolio_submitted_at,
                       s.status_student, 
                       s.status_student_EN,
                       ass.advisor_id, 
                       u_adv.f_nama AS advisor_name,
                       ass.assigned_at,
                       -- Gabungkan semua ID dan Nama Penyelaras jika fakulti ada lebih dari 1 penyelaras
                       GROUP_CONCAT(DISTINCT fc.coordinator_id SEPARATOR ', ') AS coordinator_id,
                       GROUP_CONCAT(DISTINCT u_coor.f_nama SEPARATOR ', ') AS coordinator_name
                FROM apelq_applications a
                LEFT JOIN tbl_m_user u_stu 
                       ON a.student_id COLLATE utf8mb4_unicode_ci = u_stu.f_loginID
                LEFT JOIN lp_status s 
                       ON s.module_type = 'portfolio' 
                      AND s.status_code = a.portfolio_status_id
                LEFT JOIN apelq_application_advisors ass 
                       ON a.id = ass.application_id
                LEFT JOIN tbl_m_user u_adv 
                       ON ass.advisor_id COLLATE utf8mb4_unicode_ci = u_adv.f_stafID
                -- Padanan Penyelaras Fakulti aktif
                LEFT JOIN apelq_faculty_coordinators fc 
                       ON u_stu.f_jabatanKod = fc.faculty_code AND fc.status = 1
                LEFT JOIN tbl_m_user u_coor 
                       ON fc.coordinator_id COLLATE utf8mb4_unicode_ci = u_coor.f_stafID
                {$whereFaculty}
                GROUP BY a.id, a.student_id, u_stu.f_nama, u_stu.f_jabatanKod, u_stu.f_namajabatan,
                         a.portfolio_status_id, a.portfolio_submitted_at, s.status_student, 
                         s.status_student_EN, ass.advisor_id, u_adv.f_nama, ass.assigned_at
                ORDER BY a.id DESC";

        $stmt = $this->dbMysql->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getOfficerUserByStafId(string $stafId): ?array
    {
        $stmt = $this->dbMysql->prepare("SELECT f_nama, f_email, f_jabatanKod, f_namajabatan FROM tbl_m_user WHERE TRIM(f_stafID) = :sid LIMIT 1");
        $stmt->execute([':sid' => trim($stafId)]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getStudentUserById(string $studentId): ?array
    {
        $stmt = $this->dbMysql->prepare("SELECT f_nama, f_email, f_jabatanKod, f_namajabatan FROM tbl_m_user WHERE TRIM(f_loginID) = :lid OR TRIM(f_stafID) = :sid LIMIT 1");
        $stmt->execute([':lid' => trim($studentId), ':sid' => trim($studentId)]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getActiveEmailTemplate(string $templateCode): ?array
    {
        $stmt = $this->dbMysql->prepare("SELECT f_subjectTemplate, f_bodyHtml, f_bodyText FROM tbl_m_email_template WHERE f_templateCode = ? AND (f_status = 'ACTIVE' OR f_status = 'PUBLISHED') LIMIT 1");
        $stmt->execute([$templateCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getAllAcademicStaffSybase(): array 
    {
        try {
            $sql = "SELECT DISTINCT 
                           nopekerja AS f_stafID, 
                           gelar_nama AS f_nama, 
                           jawatansemasa AS f_jawatan, 
                           jabatansemasa AS f_namajabatan,
                           kdjbtnsemasa AS f_jabatanKod
                    FROM ehrmdb.dbo.v630staf_service_skim_aktif
                    WHERE CONVERT(INT, kdjenis) = 1
                    ORDER BY f_nama ASC";
            $stmt = $this->dbSybase->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log("getAllAcademicStaffSybase Error: " . $e->getMessage());
            return [];
        }
    }

    /** Dapatkan senarai fakulti unik daripada Sybase / tbl_m_user */
    public function getAllFaculties(): array
    {
        try {
            $sql = "SELECT DISTINCT 
                           kdjbtnsemasa AS faculty_code, 
                           jabatansemasa AS faculty_name 
                    FROM ehrmdb.dbo.v630staf_service_skim_aktif
                    WHERE kdjbtnsemasa IS NOT NULL 
                      AND LTRIM(RTRIM(kdjbtnsemasa)) != ''
                    ORDER BY jabatansemasa ASC";
            $stmt = $this->dbSybase->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log("getAllFaculties Sybase Error: " . $e->getMessage());
            
            $sql = "SELECT DISTINCT f_jabatanKod AS faculty_code, f_namajabatan AS faculty_name 
                    FROM tbl_m_user 
                    WHERE f_jabatanKod IS NOT NULL AND f_jabatanKod != '' 
                    ORDER BY f_namajabatan ASC";
            return $this->dbMysql->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    }

    /** Senarai pemetaan Penyelaras Fakulti semasa (1 fakulti boleh ada banyak penyelaras) */
    public function getFacultyCoordinatorsList(): array
    {
        $sql = "SELECT fc.id, 
                       fc.faculty_code, 
                       fc.faculty_name, 
                       fc.coordinator_id, 
                       u.f_nama AS coordinator_name,
                       u.f_email AS coordinator_email,
                       u.f_jawatan AS coordinator_position,
                       fc.assigned_at,
                       fc.status
                FROM apelq_faculty_coordinators fc
                LEFT JOIN tbl_m_user u ON fc.coordinator_id COLLATE utf8mb4_unicode_ci = u.f_stafID
                WHERE fc.status = 1
                ORDER BY fc.faculty_name ASC, u.f_nama ASC";
        $stmt = $this->dbMysql->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Padam / Nyahaktif peranan Penyelaras daripada Fakulti */
    public function removeFacultyCoordinator(int $mappingId, string $adminId): bool
    {
        try {
            $this->dbMysql->beginTransaction();
            $this->setAuditUser($adminId);

            $stmt = $this->dbMysql->prepare("UPDATE apelq_faculty_coordinators SET status = 0 WHERE id = :id");
            $stmt->execute([':id' => $mappingId]);

            $this->dbMysql->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->dbMysql->inTransaction()) $this->dbMysql->rollBack();
            error_log("removeFacultyCoordinator Error: " . $e->getMessage());
            return false;
        }
    }    
}