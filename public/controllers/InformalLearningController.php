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
            if (empty($post['employer_name']) || empty($post['position_held'])) {
                return ['status' => 'error', 'message' => 'Sila lengkapkan maklumat yang bertanda (*).'];
            }

            // Format Tarikh dari DD/MM/YYYY ke YYYY-MM-DD
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

            $recordId = $this->model->saveWorkExperience($post);

            if (!empty($files['evidence_files']) && $recordId > 0) {
                $studentId = (string)($post['student_id'] ?? $_SESSION['student_id'] ?? '');
                $this->model->saveEvidenceFiles($studentId, 'WORK', $recordId, $files['evidence_files']);
            }

            return ['status' => 'success', 'message' => ''];

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
                $studentId = (string)($post['student_id'] ?? $_SESSION['f_stafID'] ?? '');
                
                $this->model->saveEvidenceFiles($studentId, 'WORK', $recordId, $files['evidence_files']);
            }

            return ['status' => 'success', 'message' => ''];
            
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


    // =====================================================================
    //  OTHER LEARNING ACTIVITIES
    // =====================================================================

    public function getSkillLevels(): array
    {
        try {
            return $this->model->getSkillLevels();
        } catch (Throwable $e) {
            return [];
        }
    }

    // Senarai levelID yang sah (string) dari lp_levelskillOther
    private function validSkillIds(): array
    {
        return array_map('strval', array_column($this->getSkillLevels(), 'levelID'));
    }

    private function currentStudentId(): string
    {
        return trim((string)($_SESSION['f_stafID'] ?? ''));
    }

    // 'dd/mm/yyyy' -> 'yyyy-mm-dd'.  Kosong = null.  Tidak sah = false.
    private function parseDmy($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }
        $d = DateTime::createFromFormat('d/m/Y', $value);
        if ($d === false || $d->format('d/m/Y') !== $value) {
            return false;
        }
        return $d->format('Y-m-d');
    }

    // Semak & bersihkan data borang. Pulangkan [data, mesejRalat]
    private function readOtherPost(array $post): array
    {
        $activity = trim((string)($post['activity_name'] ?? ''));
        if ($activity === '') {
            return [[], 'Sila lengkapkan maklumat yang bertanda (*).'];
        }

        $start = $this->parseDmy($post['start_date'] ?? '');
        $end   = $this->parseDmy($post['end_date'] ?? '');
        if ($start === false || $end === false) {
            return [[], 'Format tarikh tidak sah (DD/MM/YYYY).'];
        }
        if ($start !== null && $end !== null && $end < $start) {
            return [[], 'Tarikh tamat tidak boleh lebih awal daripada tarikh mula.'];
        }

        $skill = trim((string)($post['skill_level'] ?? ''));
        if ($skill !== '' && !in_array($skill, $this->validSkillIds(), true)) {
            return [[], 'Tahap kemahiran tidak sah.'];
        }

        $organizer = trim((string)($post['organizer'] ?? ''));
        $learned   = trim((string)($post['what_learned'] ?? ''));

        return [[
            'activity_name' => $activity,
            'start_date'    => $start,
            'end_date'      => $end,
            'organizer'     => $organizer !== '' ? $organizer : null,
            'skill_level'   => $skill !== '' ? $skill : null,
            'what_learned'  => $learned !== '' ? $learned : null,
        ], null];
    }

    // Semak fail bukti (PDF/JPG/JPEG, maks 5MB).
    private function checkEvidenceFiles($files): ?string
    {
        if (!is_array($files) || empty($files['name']) || !is_array($files['name'])) {
            return null;
        }
        foreach ($files['name'] as $i => $name) {
            $err = $files['error'][$i] ?? UPLOAD_ERR_NO_FILE;
            if ($err === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
                return 'Saiz fail melebihi 5MB.';
            }
            if ($err !== UPLOAD_ERR_OK) {
                return 'Muat naik fail gagal. Sila cuba lagi.';
            }
            $ext = strtolower(pathinfo((string)$name, PATHINFO_EXTENSION));
            if (!in_array($ext, InformalLearning::ALLOWED_EXT, true)) {
                return 'Hanya fail PDF, JPEG dan JPG dibenarkan.';
            }
            if ((int)($files['size'][$i] ?? 0) > InformalLearning::MAX_FILE_SIZE) {
                return 'Saiz fail melebihi 5MB.';
            }
        }
        return null;
    }

    public function getOtherActivities(string $studentId): array
    {
        try {
            return $this->model->getOtherActivities($studentId);
        } catch (Throwable $e) {
            return [];
        }
    }

    public function addOtherActivity(array $post, array $files): array
    {
        try {
            $studentId = $this->currentStudentId();
            if ($studentId === '') {
                return ['status' => 'error', 'message' => 'Sesi tamat. Sila log masuk semula.'];
            }

            [$data, $error] = $this->readOtherPost($post);
            if ($error !== null) {
                return ['status' => 'error', 'message' => $error];
            }

            $fileError = $this->checkEvidenceFiles($files['evidence_files'] ?? null);
            if ($fileError !== null) {
                return ['status' => 'error', 'message' => $fileError];
            }

            $data['student_id'] = $studentId;
            $recordId = $this->model->saveOtherActivity($data);

            if (!empty($files['evidence_files']) && $recordId > 0) {
                $this->model->saveEvidenceFiles($studentId, 'OTHER', $recordId, $files['evidence_files']);
            }

            return ['status' => 'success', 'message' => ''];

        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Ralat: ' . $e->getMessage()];
        }
    }

    public function updateOtherActivity(array $post, array $files): array
    {
        try {
            $studentId = $this->currentStudentId();
            $recordId  = (int)($post['record_id'] ?? 0);
            if ($studentId === '' || $recordId <= 0) {
                return ['status' => 'error', 'message' => 'ID rekod tidak sah.'];
            }

            if (!$this->model->getOtherActivityById($recordId, $studentId)) {
                return ['status' => 'error', 'message' => 'Rekod tidak dijumpai.'];
            }

            [$data, $error] = $this->readOtherPost($post);
            if ($error !== null) {
                return ['status' => 'error', 'message' => $error];
            }

            $fileError = $this->checkEvidenceFiles($files['evidence_files'] ?? null);
            if ($fileError !== null) {
                return ['status' => 'error', 'message' => $fileError];
            }

            $data['record_id']  = $recordId;
            $data['student_id'] = $studentId;
            $this->model->updateOtherActivity($data);

            if (!empty($files['evidence_files']['name'][0])) {
                $this->model->saveEvidenceFiles($studentId, 'OTHER', $recordId, $files['evidence_files']);
            }

            return ['status' => 'success', 'message' => ''];

        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Ralat: ' . $e->getMessage()];
        }
    }

    public function deleteOtherActivity(int $id): array
    {
        try {
            $studentId = $this->currentStudentId();
            if ($id <= 0 || $studentId === '') {
                return ['status' => 'error', 'message' => 'ID rekod tidak sah.'];
            }

            if ($this->model->deleteOtherActivity($id, $studentId)) {
                return ['status' => 'success', 'message' => 'Rekod berjaya dipadam.'];
            }
            return ['status' => 'error', 'message' => 'Gagal memadam rekod atau rekod tidak wujud.'];

        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Ralat: ' . $e->getMessage()];
        }
    }

    public function getOtherActivitiesFlag(string $studentId): ?int {
        try {
            return $this->model->getOtherActivitiesFlag($studentId);
        } catch (Throwable $e) {
            return null;
        }
    }

    public function updateOtherActivitiesFlag(array $post): array {
        try {
            $studentId = $this->currentStudentId();
            $flag = isset($post['flag']) ? (int)$post['flag'] : 1; // 0 = Tiada, 1 = Ada

            if ($studentId === '') {
                return ['status' => 'error', 'message' => 'Sesi tamat. Sila log masuk semula.'];
            }

            // Jika pengguna tick "Tiada Aktiviti" (flag 0), semak dahulu kalau ada rekod 
            if ($flag === 0) {
                $existingRecords = $this->model->getOtherActivities($studentId);
                if (!empty($existingRecords)) {
                    return ['status' => 'error', 'message' => 'Sila padam semua rekod sedia ada terlebih dahulu sebelum menanda kotak ini.'];
                }
            }

            $this->model->updateOtherActivitiesFlag($studentId, $flag);
            return ['status' => 'success', 'message' => 'Pilihan berjaya disimpan.'];
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Ralat: ' . $e->getMessage()];
        }
    }    
}