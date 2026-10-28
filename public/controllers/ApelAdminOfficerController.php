<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Mailer.php'; 
require_once __DIR__ . '/../classes/NotificationWorkflowService.php';
require_once __DIR__ . '/../models/ApelAdminOfficerModel.php';

class ApelAdminOfficerController
{
    private ApelAdminOfficerModel $model;
    private PDO $pdoMysql;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->pdoMysql = Database::pdoMysql();
        $this->pdoMysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdoSybase = Database::pdoSybaseStudent(); 
        if (!$pdoSybase instanceof PDO) {
            throw new RuntimeException('Sambungan pangkalan data Sybase tidak tersedia.');
        }

        $this->model = new ApelAdminOfficerModel($this->pdoMysql, $pdoSybase);
    }

    public function getActiveRoleInfo(): array {
        return $this->model->getActiveRoleInfo();
    }    

    public function getApplications(?string $facultyCode = null): array
    {
        return $this->model->getApplicationsForAssignment($facultyCode);
    }

    public function getAdvisors(): array
    {
        return $this->model->getAllAcademicStaffSybase();
    }

    public function getFaculties(): array
    {
        return $this->model->getAllFaculties();
    }

    public function getFacultyCoordinators(): array
    {
        return $this->model->getFacultyCoordinatorsList();
    }

    public function removeFacultyCoordinator(int $mappingId, string $adminId): array
    {
        if ($mappingId <= 0) {
            return ['status' => 'error', 'message' => 'ID rekod tidak sah.'];
        }

        $success = $this->model->removeFacultyCoordinator($mappingId, $adminId);
        if ($success) {
            return ['status' => 'success', 'message' => 'Penyelaras Fakulti berjaya dikeluarkan.'];
        }

        return ['status' => 'error', 'message' => 'Gagal mengeluarkan pegawai.'];
    }    

    /** Admin atau Penyelaras menetapkan Penasihat kepada pelajar */
    public function assignAdvisorToApplication(array $postData, string $assignedBy): array
    {
        $appId     = (int)($postData['application_id'] ?? 0);
        $studentId = trim((string)($postData['student_id'] ?? ''));
        $advisorId = trim((string)($postData['advisor_id'] ?? ''));

        if ($appId <= 0 || empty($studentId) || empty($advisorId)) {
            return ['status' => 'error', 'message' => 'Sila pilih Penasihat dan pastikan maklumat pelajar sah.'];
        }

        $success = $this->model->assignAdvisorToStudent($appId, $studentId, $advisorId, $assignedBy);

        if ($success) {
            try {
                $this->sendOfficerAssignmentEmail($advisorId, 'Penasihat Portfolio APEL.Q', $studentId, $appId);
            } catch (\Throwable $e) {
                error_log("Emel Penasihat gagal: " . $e->getMessage());
            }

            return ['status' => 'success', 'message' => 'Penasihat berjaya disetkan kepada pelajar.'];
        }

        return ['status' => 'error', 'message' => 'Gagal menyimpan penugasan penasihat.'];
    }

    /** Admin menetapkan Penyelaras bagi sesuatu Fakulti */
    public function assignFacultyCoordinator(array $postData, string $adminId): array
    {
        $facultyCode = trim((string)($postData['faculty_code'] ?? ''));
        $facultyName = trim((string)($postData['faculty_name'] ?? ''));
        
        $stafIds = $postData['staf_ids'] ?? [];
        if (!is_array($stafIds) && !empty($postData['staf_id'])) {
            $stafIds = [$postData['staf_id']];
        }

        $stafIds = array_filter(array_map('trim', (array)$stafIds));

        if (empty($facultyCode) || empty($stafIds)) {
            return ['status' => 'error', 'message' => 'Sila pilih Fakulti dan sekurang-kurangnya seorang Pegawai Penyelaras.'];
        }

        $success = $this->model->assignFacultyCoordinator($stafIds, $facultyCode, $facultyName, $adminId);
        
        if ($success) {
            // Hantar e-mel pelantikan kepada setiap Penyelaras yang dilantik
            foreach ($stafIds as $stafId) {
                try {
                    $this->sendCoordinatorAppointmentEmail($stafId, $facultyCode, $facultyName);
                } catch (\Throwable $mailErr) {
                    error_log("Gagal hantar e-mel penyelaras ($stafId): " . $mailErr->getMessage());
                }
            }

            return ['status' => 'success', 'message' => 'Penyelaras Fakulti berjaya disimpan dan e-mel notifikasi telah dihantar.'];
        }

        return ['status' => 'error', 'message' => 'Gagal menetapkan Penyelaras Fakulti.'];
    }

    private function sendCoordinatorAppointmentEmail(string $officerStafId, string $facultyCode, string $facultyName): bool
    {
        try {
            $officer = $this->model->getOfficerUserByStafId($officerStafId);
            $namaPegawai  = $officer['f_nama'] ?? $officerStafId;
            $emailPegawai = 'zaila@upnm.edu.my'; // Mode test. $officer['f_email'] 

            $resolvedFacultyName = trim($facultyName);
            if ($resolvedFacultyName === '') {
                $facList = $this->model->getAllFaculties();
                foreach ($facList as $f) {
                    if (trim((string)($f['faculty_code'] ?? '')) === $facultyCode) {
                        $resolvedFacultyName = trim((string)($f['faculty_name'] ?? ''));
                        break;
                    }
                }
            }
            if ($resolvedFacultyName === '') {
                $resolvedFacultyName = $facultyCode;
            }

            $tpl = $this->model->getActiveEmailTemplate('APELQ_COORDINATOR_ASSIGNED');
            if (!$tpl) {
                error_log("sendCoordinatorAppointmentEmail: Template APELQ_COORDINATOR_ASSIGNED tidak ditemui atau tidak aktif.");
                return false;
            }

            $actionLink = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
            $placeholders = [
                'recipient_name' => $namaPegawai,
                'faculty_name'   => $resolvedFacultyName,
                'faculty_code'   => $facultyCode,
                'assign_date'    => date('d/m/Y'),
                'action_link'    => $actionLink
            ];

            $subject  = $this->renderEmailTemplate((string)$tpl['f_subjectTemplate'], $placeholders);
            $htmlBody = $this->renderEmailTemplate((string)$tpl['f_bodyHtml'], $placeholders);
            $textBody = !empty($tpl['f_bodyText']) ? $this->renderEmailTemplate((string)$tpl['f_bodyText'], $placeholders) : null;

            // Senarai CC (Boleh tambah banyak emel di dalam array ini)
            $ccRecipients = [
                'lavindastarz@gmail.com'
                // 'emel_seterusnya@upnm.edu.my',
            ];

            $opts = [
                'cc' => $ccRecipients
            ];

            return Mailer::quickSend($this->pdoMysql, $emailPegawai, $subject, $htmlBody, $textBody, $opts);
        } catch (\Throwable $e) {
            error_log("sendCoordinatorAppointmentEmail Error: " . $e->getMessage());
            return false;
        }
    }

    private function sendOfficerAssignmentEmail(string $officerStafId, string $roleName, string $studentId, int $applicationId): bool
    {
        try {
            $officer = $this->model->getOfficerUserByStafId($officerStafId);
            $namaPegawai  = $officer['f_nama'] ?? $officerStafId;
            $emailPegawai = 'zaila@upnm.edu.my'; // Mode test $officer['f_email']

            $student = $this->model->getStudentUserById($studentId);
            $studentName = !empty($student['f_nama']) ? $student['f_nama'] : $studentId;

            $tpl = $this->model->getActiveEmailTemplate('APELQ_ADVISOR_ASSIGNED');
            if (!$tpl) return false;

            $actionLink = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
            $placeholders = [
                'recipient_name' => $namaPegawai,
                'role_name'      => $roleName,
                'student_name'   => $studentName,
                'student_id'     => $studentId,
                'application_id' => $applicationId,
                'assign_date'    => date('d/m/Y'),
                'action_link'    => $actionLink
            ];

            $subject  = $this->renderEmailTemplate((string)$tpl['f_subjectTemplate'], $placeholders);
            $htmlBody = $this->renderEmailTemplate((string)$tpl['f_bodyHtml'], $placeholders);
            $textBody = !empty($tpl['f_bodyText']) ? $this->renderEmailTemplate((string)$tpl['f_bodyText'], $placeholders) : null;

            $ccRecipients = [
                'lavindastarz@gmail.com'
            ];

            $opts = [
                'cc' => $ccRecipients
            ];

            return Mailer::quickSend($this->pdoMysql, $emailPegawai, $subject, $htmlBody, $textBody, $opts);
        } catch (\Throwable $e) {
            error_log("sendOfficerAssignmentEmail Error: " . $e->getMessage());
            return false;
        }
    }

    private function renderEmailTemplate(string $templateText, array $placeholders): string
    {
        foreach ($placeholders as $key => $value) {
            $templateText = str_replace(['{{' . $key . '}}', '{' . $key . '}'], (string)$value, $templateText);
        }
        return $templateText;
    }
}