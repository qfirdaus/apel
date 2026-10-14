<?php
declare(strict_types=1);
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/NonFormalLearning.php';

class NonFormalController {
    private NonFormalLearning $model;

    public function __construct() {        
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $pdoApel = Database::pdoMysql();
        $pdoApel->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->model = new NonFormalLearning($pdoApel);   
    }

    public function getDurationTypes(): array {
        return $this->model->getDurationTypes();
    }

    public function getOutcomes(): array {
        try {
            return $this->model->getOutcomes();
        } catch (Throwable $e) {
            return [];
        }
    }

    public function getTrainingCourses(string $studentId): array {
        try {
            return $this->model->getTrainingCourses($studentId);
        } catch (Throwable $e) {
            return [];
        }
    }

    public function getEvidenceFiles(string $moduleType, int $recordId): array {
        try {
            return $this->model->getEvidenceFiles($moduleType, $recordId);
        } catch (Throwable $e) {
            return [];
        }
    }   

    public function addTraining(array $post, array $files): array {
        try {
            $studentId = trim((string)($_SESSION['f_stafID'] ?? ''));
            if (empty($studentId)) return ['status' => 'error', 'message' => ''];

            // Format Tarikh
            $courseDate = null;
            if (!empty($post['course_date'])) {
                $d = DateTime::createFromFormat('d/m/Y', trim($post['course_date']));
                if ($d) $courseDate = $d->format('Y-m-d');
            }

            // Tukar array skills dari checkbox ke JSON string
            $skillsJson = json_encode($post['skills'] ?? []);

            $data = [
                'student_id'       => $studentId,
                'course_name'      => trim($post['course_name'] ?? ''),
                'organizer'        => trim($post['organizer'] ?? ''),
                'course_date'      => $courseDate,
                'duration'         => (int)($post['duration'] ?? 0),
                'duration_type_id' => (int)($post['duration_type'] ?? 0),
                'knowledge_desc'   => trim($post['knowledge_desc'] ?? ''),
                'skills_learned'   => $skillsJson
            ];

            $recordId = $this->model->saveTraining($data);

            if (!empty($files['evidence_files']['name'][0])) {
                $this->model->saveEvidenceFiles($studentId, 'TRAINING', $recordId, $files['evidence_files']);
            }

            return ['status' => 'success', 'message' => ''];
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updateTraining(array $post, array $files): array {
        try {
            $studentId = trim((string)($_SESSION['f_stafID'] ?? ''));
            $recordId  = (int)($post['record_id'] ?? 0);
            
            $courseDate = null;
            if (!empty($post['course_date'])) {
                $d = DateTime::createFromFormat('d/m/Y', trim($post['course_date']));
                if ($d) $courseDate = $d->format('Y-m-d');
            }

            $skillsJson = json_encode($post['skills'] ?? []);

            $data = [
                'record_id'        => $recordId,
                'student_id'       => $studentId,
                'course_name'      => trim($post['course_name'] ?? ''),
                'organizer'        => trim($post['organizer'] ?? ''),
                'course_date'      => $courseDate,
                'duration'         => (int)($post['duration'] ?? 0),
                'duration_type_id' => (int)($post['duration_type'] ?? 0),
                'knowledge_desc'   => trim($post['knowledge_desc'] ?? ''),
                'skills_learned'   => $skillsJson
            ];

            $this->model->updateTraining($data);

            if (!empty($files['evidence_files']['name'][0])) {
                $this->model->saveEvidenceFiles($studentId, 'TRAINING', $recordId, $files['evidence_files']);
            }

            return ['status' => 'success', 'message' => ''];
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleteTraining(int $id): array {
        try {
            $studentId = trim((string)($_SESSION['f_stafID'] ?? ''));
            $this->model->deleteTraining($id, $studentId);
            return ['status' => 'success', 'message' => ''];
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleteSingleEvidenceFile(int $fileId): array {
        $this->model->deleteEvidenceFileById($fileId);
        return ['status' => 'success', 'message' => ''];
    }
}