<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/InformalLearning.php';

class InformalLearningController
{
    private InformalLearning $model;
    private PDO $pdoApel;

    public function __construct()
    {        
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $pdoStudent = Database::pdoSybaseStudent();
        if (!$pdoStudent instanceof PDO) {
            throw new RuntimeException('Sambungan Sybase Pelajar tidak tersedia.');
        }

        $this->pdoApel = Database::pdoMysql();
        $this->pdoApel->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->model = new InformalLearning($this->pdoApel);   
    }

    public function addWorkExperience(array $post, array $files): array 
    {
        try {
            // 1. Semakan Input Asas
            if (empty($post['employer_name']) || empty($post['position_held'])) {
                return ['status' => 'error', 'message' => 'Sila lengkapkan maklumat yang bertanda (*).'];
            }

            // 2. Format Tarikh dari DD/MM/YYYY ke YYYY-MM-DD
            $rawStartDate = trim((string)($post['start_date'] ?? ''));
            $rawEndDate   = trim((string)($post['end_date'] ?? ''));

            $dbStartDate = null;
            $dbEndDate   = null;

            if (!empty($rawStartDate)) {
                $dateObj = DateTime::createFromFormat('d/m/Y', $rawStartDate);
                if ($dateObj !== false) {
                    $dbStartDate = $dateObj->format('Y-m-d');
                }
            }

            if (!empty($rawEndDate)) {
                $dateObj = DateTime::createFromFormat('d/m/Y', $rawEndDate);
                if ($dateObj !== false) {
                    $dbEndDate = $dateObj->format('Y-m-d');
                }
            }

            $post['start_date'] = $dbStartDate;
            $post['end_date']   = $dbEndDate;

            // 3. Simpan Rekod Pengalaman Kerja ke MySQL melalui Model
            $recordId = $this->model->saveWorkExperience($post);

            // 4. Simpan Fail Lampiran (Evidence) jika ada
            if (!empty($files['evidence_files']) && $recordId > 0) {
                $studentId = (string)($post['student_id'] ?? $_SESSION['student_id'] ?? '');
                $this->model->saveEvidenceFiles($studentId, 'WORK', $recordId, $files['evidence_files']);
            }

            // 5. Pulangkan Maklum Balas Berjaya kepada AJAX
            return [
                'status'  => 'success', 
                'message' => 'Rekod pengalaman kerja berjaya disimpan.'
            ];

        } catch (Throwable $e) {
            return [
                'status'  => 'error', 
                'message' => 'Ralat: ' . $e->getMessage()
            ];
        }
    }

    public function getEvidenceFiles(string $moduleType, int $recordId): array {
        try {
            return $this->model->getEvidenceFiles($moduleType, $recordId);
        } catch (Throwable $e) {
            return [];
        }
    }

    public function getWorkExperience(string $studentId): array {
        try {
            return $this->model->getWorkExperience($studentId);
        } catch (Throwable $e) {
            return []; 
        }
    } 
    
    public function deleteWorkExperience(int $id): array {
        try {
            if ($id <= 0) {
                return ['status' => 'error', 'message' => 'ID rekod tidak sah.'];
            }

            $deleted = $this->model->deleteWorkExperience($id);
            
            if ($deleted) {
                return ['status' => 'success', 'message' => 'Rekod berjaya dipadam.'];
            }
            return ['status' => 'error', 'message' => 'Gagal memadam rekod atau rekod tidak wujud.'];

        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Ralat: ' . $e->getMessage()];
        }
    } 
    
    public function updateWorkExperience(array $post, array $files): array 
    {
        try {
            $recordId = (int)($post['record_id'] ?? 0);
            if ($recordId <= 0) {
                return ['status' => 'error', 'message' => 'ID rekod tidak sah.'];
            }

            $dbStartDate = null;
            $dbEndDate   = null;

            if (!empty($post['start_date'])) {
                $dateObj = DateTime::createFromFormat('d/m/Y', trim($post['start_date']));
                if ($dateObj) $dbStartDate = $dateObj->format('Y-m-d');
            }
            if (!empty($post['end_date'])) {
                $dateObj = DateTime::createFromFormat('d/m/Y', trim($post['end_date']));
                if ($dateObj) $dbEndDate = $dateObj->format('Y-m-d');
            }

            $post['start_date'] = $dbStartDate;
            $post['end_date']   = $dbEndDate;

            $this->model->updateWorkExperience($post);

            if (isset($files['evidence_files']['name'][0]) && !empty($files['evidence_files']['name'][0])) {
                $studentId = (string)($post['student_id'] ?? $_SESSION['student_id'] ?? '');
                
                $this->model->saveEvidenceFiles($studentId, 'WORK', $recordId, $files['evidence_files']);
            }

            return ['status' => 'success', 'message' => 'Rekod berjaya dikemas kini.'];
            
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Ralat: ' . $e->getMessage()];
        }
    } 
    
    public function deleteSingleEvidenceFile(int $fileId): array {
        try {
            $fileInfo = $this->model->getEvidenceFileById($fileId);
            if ($fileInfo) {
                $physicalPath = __DIR__ . '/../pages/portfolio/informal/' . $fileInfo['file_path'];
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