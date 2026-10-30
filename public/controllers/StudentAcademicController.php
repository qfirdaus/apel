<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/StudentAcademicModel.php';

class StudentAcademicController
{
    private StudentAcademicModel $model;
    private PDO $pdoMysql;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->pdoMysql = Database::pdoMysql();
        $this->pdoMysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->model = new StudentAcademicModel($this->pdoMysql);
    }

    public function getAcademic(string $studentId): ?array
    {
        if (empty($studentId)) {
            return null;
        }
        return $this->model->getStudentAcademic($studentId);
    }
}