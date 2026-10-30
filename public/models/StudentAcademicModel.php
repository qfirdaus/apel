<?php
declare(strict_types=1);

class StudentAcademicModel
{
    private PDO $dbMysql;

    public function __construct(PDO $dbMysql)
    {
        $this->dbMysql = $dbMysql;
    }

    public function getStudentAcademic(string $studentId): ?array
    {
        $studentId = trim($studentId);
        if ($studentId === '') {
            return null;
        }

        try {
            $sql = "SELECT a.*, 
                           p.full_name, 
                           p.ic_no, 
                           p.official_email, 
                           p.student_status 
                    FROM apeldb.apelq_student_profiles p
                    LEFT JOIN apeldb.apelq_student_academics a ON TRIM(p.student_id) = TRIM(a.student_id)
                    WHERE TRIM(p.student_id) = :sid
                    LIMIT 1";

            $stmt = $this->dbMysql->prepare($sql);
            $stmt->execute([':sid' => $studentId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            return $res ?: null;
        } catch (\Throwable $e) {
            error_log("StudentAcademicModel::getStudentAcademic Error: " . $e->getMessage());
            return null;
        }
    }
}