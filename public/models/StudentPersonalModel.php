<?php
declare(strict_types=1);

class StudentPersonalModel
{
    private PDO $dbMysql;

    public function __construct(PDO $dbMysql)
    {
        $this->dbMysql = $dbMysql;
    }

    /** Set @app_user untuk trigger audit log */
    private function setAuditUser(string $userId): void
    {
        $stmt = $this->dbMysql->prepare("SET @app_user = ?");
        $stmt->execute([$userId]);
    }

    /**
     * Dapatkan maklumat profil daripada apelq_student_profiles
     */
    public function getStudentProfile(string $studentId): ?array
    {
        $studentId = trim($studentId);
        if ($studentId === '') {
            return null;
        }

        try {
            // 1. Kueri UTAMA: Tarik terus data lengkap daripada apelq_student_profiles
            //    (Jadual ini sendiri sudah mengandungi program_name, faculty_name, ic_no, dll.)
            $sql = "SELECT p.* FROM apelq_student_profiles p 
                    WHERE TRIM(p.student_id) = :sid 
                    LIMIT 1";

            $stmt = $this->dbMysql->prepare($sql);
            $stmt->execute([':sid' => $studentId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($res) {
                return $res;
            }

            // 2. Kueri KEDUA: Jika $studentId dalam sesi adalah login ID atau staf ID dari tbl_m_user
            $sqlUser = "SELECT p.* 
                        FROM tbl_m_user u
                        JOIN apelq_student_profiles p 
                          ON (TRIM(u.f_loginID) = TRIM(p.student_id) 
                           OR TRIM(u.f_stafID)  = TRIM(p.student_id) 
                           OR TRIM(u.f_nopekerja) = TRIM(p.student_id))
                        WHERE TRIM(u.f_loginID) = :sid 
                           OR TRIM(u.f_stafID) = :sid 
                           OR TRIM(u.f_nopekerja) = :sid
                           OR CAST(u.f_userID AS CHAR) = :sid
                        LIMIT 1";

            $stmtUser = $this->dbMysql->prepare($sqlUser);
            $stmtUser->execute([':sid' => $studentId]);
            $resUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

            return $resUser ?: null;

        } catch (\Throwable $e) {
            error_log("StudentPersonalModel::getStudentProfile Error: " . $e->getMessage());
            return null;
        }
    }

    public function ensureStudentProfileExists(string $studentId): ?array
    {
        $studentId = trim($studentId);
        if ($studentId === '') {
            return null;
        }

        // Ambil data profil sedia ada
        $profile = $this->getStudentProfile($studentId);
        if ($profile !== null) {
            return $profile;
        }

        return null;
    }

    public function updateStudentProfile(string $studentId, array $data): bool
    {
        $studentId = trim($studentId);
        if ($studentId === '') {
            return false;
        }

        try {
            $this->dbMysql->beginTransaction();

            $this->setAuditUser($studentId);

            $sql = "UPDATE apeldb.apelq_student_profiles 
                    SET address_1   = :address_1,
                        address_2   = :address_2,
                        postcode    = :postcode,
                        city        = :city,
                        state       = :state,
                        phone_no    = :phone_no,
                        updated_by  = :updated_by,
                        updated_at  = NOW()
                    WHERE TRIM(student_id) = :sid";

            $stmt = $this->dbMysql->prepare($sql);
            $stmt->execute([
                ':address_1'  => !empty($data['address_1']) ? trim((string)$data['address_1']) : null,
                ':address_2'  => !empty($data['address_2']) ? trim((string)$data['address_2']) : null,
                ':postcode'   => !empty($data['postcode']) ? trim((string)$data['postcode']) : null,
                ':city'       => !empty($data['city']) ? trim((string)$data['city']) : null,
                ':state'      => !empty($data['state']) ? trim((string)$data['state']) : null,
                ':phone_no'   => !empty($data['phone_no']) ? trim((string)$data['phone_no']) : null,
                ':updated_by' => $studentId,
                ':sid'        => $studentId
            ]);

            $this->dbMysql->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->dbMysql->inTransaction()) {
                $this->dbMysql->rollBack();
            }
            error_log("StudentPersonalModel::updateStudentProfile Error: " . $e->getMessage());
            return false;
        }
    }
}