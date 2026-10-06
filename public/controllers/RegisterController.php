<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/RegisterPortfolio.php';

class RegisterController
{
    private RegisterPortfolio $model;

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $this->model = new RegisterPortfolio();
    }

    // Dapatkan data untuk dipaparkan pada borang (Dropdown)
    public function getHalamanData(): array
    {
        return [
            'senarai_program' => $this->model->getSenaraiProgramDummy()
        ];
    }

    // Fungsi dipanggil melalui endpoint AJAX 
    public function prosesPendaftaranManual($postData, $fileData, $userID)
    {
        // 1. Validasi fail resit
        if (!isset($fileData['resit_bayaran']) || $fileData['resit_bayaran']['error'] !== UPLOAD_ERR_OK) {
            return ['status' => 'error', 'message' => 'Sila muat naik resit yang sah.'];
        }

        // 2. Simpan rekod pendaftaran dengan status 'Menunggu Pengesahan Kewangan'
        $berjaya = $this->model->simpanPendaftaran($postData, $userID);

        if ($berjaya) {
            return ['status' => 'success', 'message' => 'Pendaftaran berjaya. Resit anda sedang disemak.'];
        }
        return ['status' => 'error', 'message' => 'Pendaftaran gagal disimpan.'];
    }
}