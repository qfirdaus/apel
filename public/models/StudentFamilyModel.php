<?php
declare(strict_types=1);

class StudentFamilyModel
{
    private PDO $dbMysql;

    public function __construct(PDO $dbMysql)
    {
        $this->dbMysql = $dbMysql;
    }

    private function setAuditUser(string $userId): void
    {
        $stmt = $this->dbMysql->prepare("SET @app_user = ?");
        $stmt->execute([$userId]);
    }

    public function getStudentFamily(string $studentId): ?array
    {
        $studentId = trim($studentId);
        if ($studentId === '') {
            return null;
        }

        try {
            $sql = "SELECT f.*, 
                           p.full_name, 
                           p.ic_no, 
                           p.official_email, 
                           p.marital_status 
                    FROM apeldb.apelq_student_profiles p
                    LEFT JOIN apeldb.apelq_student_families f ON TRIM(p.student_id) = TRIM(f.student_id)
                    WHERE TRIM(p.student_id) = :sid
                    LIMIT 1";

            $stmt = $this->dbMysql->prepare($sql);
            $stmt->execute([':sid' => $studentId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            return $res ?: null;
        } catch (\Throwable $e) {
            error_log("StudentFamilyModel::getStudentFamily Error: " . $e->getMessage());
            return null;
        }
    }

    public function updateStudentFamily(string $studentId, array $data): bool
    {
        $studentId = trim($studentId);
        if ($studentId === '') {
            return false;
        }

        try {
            $this->dbMysql->beginTransaction();
            $this->setAuditUser($studentId);

            $sql = "INSERT INTO apeldb.apelq_student_families (
                        student_id, 
                        father_phone, father_job, father_income,
                        mother_phone, mother_job, mother_income,
                        spouse_name, spouse_phone, spouse_relationship,
                        siblings_count, dependents_count, 
                        created_by, created_at
                    ) VALUES (
                        :sid, 
                        :f_phone, :f_job, :f_inc,
                        :m_phone, :m_job, :m_inc,
                        :s_name, :s_phone, :s_rel,
                        :siblings, :dependents, 
                        :created_by, NOW()
                    )
                    ON DUPLICATE KEY UPDATE 
                        father_phone        = VALUES(father_phone),
                        father_job          = VALUES(father_job),
                        father_income       = VALUES(father_income),
                        mother_phone        = VALUES(mother_phone),
                        mother_job          = VALUES(mother_job),
                        mother_income       = VALUES(mother_income),
                        spouse_name         = VALUES(spouse_name),
                        spouse_phone        = VALUES(spouse_phone),
                        spouse_relationship = VALUES(spouse_relationship),
                        siblings_count      = VALUES(siblings_count),
                        dependents_count    = VALUES(dependents_count),
                        updated_by          = :updated_by,
                        updated_at          = NOW()";

            $stmt = $this->dbMysql->prepare($sql);
            $stmt->execute([
                ':sid'        => $studentId,
                ':f_phone'    => !empty($data['father_phone']) ? trim((string)$data['father_phone']) : null,
                ':f_job'      => !empty($data['father_job']) ? trim((string)$data['father_job']) : null,
                ':f_inc'      => !empty($data['father_income']) ? trim((string)$data['father_income']) : null,
                ':m_phone'    => !empty($data['mother_phone']) ? trim((string)$data['mother_phone']) : null,
                ':m_job'      => !empty($data['mother_job']) ? trim((string)$data['mother_job']) : null,
                ':m_inc'      => !empty($data['mother_income']) ? trim((string)$data['mother_income']) : null,
                ':s_name'     => !empty($data['spouse_name']) ? trim((string)$data['spouse_name']) : null,
                ':s_phone'    => !empty($data['spouse_phone']) ? trim((string)$data['spouse_phone']) : null,
                ':s_rel'      => !empty($data['spouse_relationship']) ? trim((string)$data['spouse_relationship']) : null,
                ':siblings'   => isset($data['siblings_count']) && $data['siblings_count'] !== '' ? (int)$data['siblings_count'] : 0,
                ':dependents' => isset($data['dependents_count']) && $data['dependents_count'] !== '' ? (int)$data['dependents_count'] : 0,
                ':created_by' => $studentId,
                ':updated_by' => $studentId
            ]);

            $this->dbMysql->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->dbMysql->inTransaction()) {
                $this->dbMysql->rollBack();
            }
            error_log("StudentFamilyModel::updateStudentFamily Error: " . $e->getMessage());
            return false;
        }
    }
}