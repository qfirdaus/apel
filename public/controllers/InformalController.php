<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/InformalLearning.php';

class InformalController
{
    private InformalLearning $model;
    private array $allowedExtensions = ['pdf', 'jpg', 'jpeg'];
    private int $maxFileSize = 5242880; // 5MB in bytes

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $this->model = new InformalLearning();
    }

    // Fungsi Utama untuk mengendalikan POST request
    public function handleRequest()
    {
        $action = $_POST['action'] ?? '';
        $userID = $_SESSION['f_stafID'] ?? 'USER_DUMMY_01'; // Tukar mengikut session sebenar

        try {
            if ($action === 'add_work_exp') {
                $this->processWorkExp($userID, $_POST, $_FILES);
            } elseif ($action === 'add_other_act') {
                $this->processOtherAct($userID, $_POST, $_FILES);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Tindakan tidak sah.']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()]);
        }
    }

    private function processWorkExp(string $userID, array $postData, array $files)
    {
        // 1. Simpan data teks ke pangkalan data
        $recordID = $this->model->addWorkExperience($userID, $postData);

        // 2. Proses muat naik dokumen (jika ada)
        if (isset($files['evidence']) && !empty($files['evidence']['name'][0])) {
            $this->uploadMultipleFiles('work_exp', $recordID, $files['evidence']);
        }

        echo json_encode(['status' => 'success', 'message' => 'Rekod Pengalaman Kerja berjaya disimpan.']);
    }

    private function processOtherAct(string $userID, array $postData, array $files)
    {
        // 1. Simpan data teks
        $recordID = $this->model->addOtherActivity($userID, $postData);

        // 2. Proses muat naik dokumen (jika ada)
        if (isset($files['evidence']) && !empty($files['evidence']['name'][0])) {
            $this->uploadMultipleFiles('other_act', $recordID, $files['evidence']);
        }

        echo json_encode(['status' => 'success', 'message' => 'Rekod Aktiviti berjaya disimpan.']);
    }

    // Fungsi Khas: Loop dan simpan multiple files
    private function uploadMultipleFiles(string $moduleType, int $recordID, array $fileArray)
    {
        $uploadDir = __DIR__ . '/../../uploads/portfolio/informal/';
        
        // Buat folder jika belum wujud
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $totalFiles = count($fileArray['name']);

        for ($i = 0; $i < $totalFiles; $i++) {
            $tmpName  = $fileArray['tmp_name'][$i];
            $fileName = basename($fileArray['name'][$i]);
            $fileSize = $fileArray['size'][$i];
            $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if ($tmpName !== '') {
                // Validasi Saiz dan Jenis Fail
                if ($fileSize <= $this->maxFileSize && in_array($fileExt, $this->allowedExtensions)) {
                    
                    // Generate nama fail unik: module_recordID_timestamp_namafail.ext
                    $newFileName = $moduleType . '_' . $recordID . '_' . time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $fileName);
                    $destination = $uploadDir . $newFileName;

                    // Pindahkan fail dari tmp ke folder sistem
                    if (move_uploaded_file($tmpName, $destination)) {
                        // Simpan path ke pangkalan data
                        $dbFilePath = 'uploads/portfolio/informal/' . $newFileName;
                        $this->model->addDocument($moduleType, $recordID, $fileName, $dbFilePath);
                    }
                }
            }
        }
    }
}