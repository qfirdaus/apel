<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/StudentPersonalModel.php';

class StudentPersonalController
{
    private StudentPersonalModel $model;
    private PDO $pdoMysql;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->pdoMysql = Database::pdoMysql();
        $this->pdoMysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->model = new StudentPersonalModel($this->pdoMysql);
    }

    public function getProfile(string $studentId): ?array
    {
        if (empty($studentId)) {
            return null;
        }
        return $this->model->ensureStudentProfileExists($studentId);
    }

    public function saveProfile(array $postData, string $studentId): array
    {
        $studentId = trim($studentId);
        if ($studentId === '') {
            return ['status' => 'error', 'message' => 'Sesi pengguna tidak sah. Sila log masuk semula.'];
        }

        $postedCsrf  = (string)($postData['csrf_token'] ?? '');
        $sessionCsrf = (string)($_SESSION['csrf_token'] ?? '');
        if ($postedCsrf === '' || !hash_equals($sessionCsrf, $postedCsrf)) {
            return ['status' => 'error', 'message' => 'Token keselamatan tamat tempoh. Sila muat semula halaman.'];
        }

        $address1 = trim((string)($postData['address_1'] ?? ''));
        $address2 = trim((string)($postData['address_2'] ?? ''));
        $postcode = trim((string)($postData['postcode'] ?? ''));
        $city     = trim((string)($postData['city'] ?? ''));
        $state    = trim((string)($postData['state'] ?? ''));
        $phoneNo  = trim((string)($postData['phone_no'] ?? ''));

        // Pengesahan medan wajib (bertanda *)
        if ($address1 === '') {
            return ['status' => 'error', 'message' => 'Sila masukkan Alamat Baris 1.'];
        }

        if ($state === '') {
            return ['status' => 'error', 'message' => 'Sila pilih Negeri.'];
        }

        if ($phoneNo === '') {
            return ['status' => 'error', 'message' => 'Sila masukkan No. Telefon yang sah.'];
        }

        if ($postcode !== '' && !preg_match('/^[0-9]{5}$/', $postcode)) {
            return ['status' => 'error', 'message' => 'Poskod mestilah 5 digit nombor.'];
        }

        $cleanData = [
            'address_1' => $address1,
            'address_2' => $address2,
            'postcode'  => $postcode,
            'city'      => $city,
            'state'     => $state,
            'phone_no'  => $phoneNo
        ];

        $success = $this->model->updateStudentProfile($studentId, $cleanData);

        if ($success) {
            return [
                'status'  => 'success', 
                'message' => 'Maklumat alamat & nombor telefon berjaya dikemaskini.'
            ];
        }

        return [
            'status'  => 'error', 
            'message' => 'Gagal mengemas kini maklumat. Sila cuba sebentar lagi.'
        ];
    }
}