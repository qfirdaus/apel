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
            if ($id <= 0 || $studentId === '') {
                return ['status' => 'error', 'message' => 'ID rekod tidak sah.'];
            }

            $files = $this->model->getEvidenceFiles('TRAINING', $id);
            $pathsToDelete = [];

            if (!empty($files)) {
                foreach ($files as $f) {
                    $fileId = (int)($f['id'] ?? 0);
                    if ($fileId > 0) {
                        $fileInfo = $this->model->getEvidenceFileById($fileId);
                        if ($fileInfo && !empty($fileInfo['file_path'])) {
                            $pathsToDelete[] = $fileInfo['file_path'];
                        }
                    }
                }
            }

            $deleted = $this->model->deleteTraining($id, $studentId);

            if ($deleted) {
                foreach ($pathsToDelete as $path) {
                    $physicalPath = __DIR__ . '/../pages/student/portfolio/non-formal/' . ltrim($path, '/');
                    if (file_exists($physicalPath) && is_file($physicalPath)) {
                        @unlink($physicalPath);
                    }
                }
                return ['status' => 'success', 'message' => 'Rekod dan lampiran berjaya dipadam.'];
            }
            return ['status' => 'error', 'message' => 'Gagal memadam rekod.'];

        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleteSingleEvidenceFile(int $fileId): array {
        try {
            $fileInfo = $this->model->getEvidenceFileById($fileId);
            if ($fileInfo) {
                $physicalPath = __DIR__ . '/../pages/student/portfolio/non-formal/' . $fileInfo['file_path'];
                if (file_exists($physicalPath) && is_file($physicalPath)) {
                    @unlink($physicalPath);
                }
                
                $this->model->deleteEvidenceFileById($fileId);
                return ['status' => 'success', 'message' => 'Fail berjaya dipadam.'];
            }
            return ['status' => 'error', 'message' => 'Fail tidak wujud.'];
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}