<?php
declare(strict_types=1);
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/LanguageCompetency.php';

class LanguageController {
    private LanguageCompetency $model;

    public function __construct() {        
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $pdoApel = Database::pdoMysql();
        $pdoApel->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->model = new LanguageCompetency($pdoApel);   
    }

    public function getLanguageList(): array {
        try { return $this->model->getLanguageList(); } catch (Throwable $e) { return []; }
    }

    public function getSkillLevels(): array {
        try { return $this->model->getSkillLevels(); } catch (Throwable $e) { return []; }
    }

    public function getLanguageRecords(string $studentId, int $applicationId = 0): array {
        try { 
            return $this->model->getLanguageRecords($studentId, $applicationId); 
        } catch (Throwable $e) { 
            return []; 
        }
    }

    public function addLanguage(array $post): array {
        try {
            $studentId = trim((string)($_SESSION['f_stafID'] ?? ''));
            if (empty($studentId)) return ['status' => 'error', 'message' => 'Sesi tamat.'];

            $this->model->saveLanguage([
                'student_id'     => $studentId,
                'application_id' => (int)($post['application_id'] ?? 0), 
                'language_id'    => (int)($post['language_id'] ?? 0),
                'reading_level'  => (int)($post['reading_level'] ?? 0),
                'speaking_level' => (int)($post['speaking_level'] ?? 0),
                'writing_level'  => (int)($post['writing_level'] ?? 0)
            ]);
            return ['status' => 'success', 'message' => ''];
        } catch (Throwable $e) { 
            return ['status' => 'error', 'message' => $e->getMessage()]; 
        }
    }

    public function updateLanguage(array $post): array {
        try {
            $studentId = trim((string)($_SESSION['f_stafID'] ?? ''));
            $this->model->updateLanguage([
                'record_id'      => (int)($post['record_id'] ?? 0),
                'student_id'     => $studentId,
                'language_id'    => (int)($post['language_id'] ?? 0),
                'reading_level'  => (int)($post['reading_level'] ?? 0),
                'speaking_level' => (int)($post['speaking_level'] ?? 0),
                'writing_level'  => (int)($post['writing_level'] ?? 0)
            ]);
            return ['status' => 'success', 'message' => ''];
        } catch (Throwable $e) { return ['status' => 'error', 'message' => $e->getMessage()]; }
    }

    public function deleteLanguage(int $id): array {
        try {
            $studentId = trim((string)($_SESSION['f_stafID'] ?? ''));
            $this->model->deleteLanguage($id, $studentId);
            return ['status' => 'success', 'message' => ''];
        } catch (Throwable $e) { return ['status' => 'error', 'message' => $e->getMessage()]; }
    }
}