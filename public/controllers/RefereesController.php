<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/Referees.php';

class RefereesController 
{
    private Referees $model;
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

        $this->model = new Referees($this->pdoApel);   
    }

    public function getRefereesRecord(string $studentId): array {
        return $this->model->getReferees($studentId);
    }

    // Save
    public function saveReferees(array $post): array {
        try {
            $studentId = trim((string)($_SESSION['f_stafID'] ?? ''));
            
            if (empty($studentId)) {
                return ['status' => 'error', 'message' => 'Sesi telah tamat. Sila log masuk semula.'];
            }

            // name="referee[1][name]"
            $refereesData = $post['referee'] ?? [];

            // Loop referee (Type 1 dan Type 2)
            foreach ([1, 2] as $type) {
                $refData = $refereesData[$type] ?? [];
                
                $data = [
                    'student_id'    => $studentId,
                    'referee_type'  => $type,
                    'name'          => trim($refData['name'] ?? ''),
                    'position_held' => trim($refData['position_held'] ?? ''),
                    'organization'  => trim($refData['organization'] ?? ''),
                    'office_phone'  => trim($refData['office_phone'] ?? ''),
                    'mobile_phone'  => trim($refData['mobile_phone'] ?? ''),
                    'email'         => trim($refData['email'] ?? ''),
                    'relationship'  => trim($refData['relationship'] ?? '')
                ];

                // Simpan ke DB (UPSERT)
                $this->model->saveReferee($data);
            }

            return ['status' => 'success', 'message' => 'Maklumat perujuk berjaya disimpan.'];
            
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()];
        }
    }
}