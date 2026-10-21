<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/UploadDocument.php';

class UploadDocumentController {
    private UploadDocument $model;
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

        $this->model = new UploadDocument($this->pdoApel);   
    }

    public function getDocumentsList(string $studentId, int $applicationId = 0): array {
        return $this->model->getStudentDocumentsList($studentId, $applicationId); 
    }

public function processUpload(array $post, array $files): array {
        try {
            $studentId = trim((string)($_SESSION['f_stafID'] ?? ''));
            $appId = (int)($post['application_id'] ?? 0);
            
            if (empty($studentId)) {
                return ['status' => 'error', 'message' => 'Sesi telah tamat. Sila log masuk semula.'];
            }

            $docTypeId = (int)($post['document_type_id'] ?? 0);
            $docName = $post['document_name'] ?? 'document'; 

            if (!isset($files['document_file']) || $files['document_file']['error'] !== UPLOAD_ERR_OK) {
                return ['status' => 'error', 'message' => 'Sila pilih fail untuk dimuat naik atau fail mungkin rosak.'];
            }

            $file = $files['document_file'];
            $fileSize = $file['size'];
            $tmpName = $file['tmp_name'];
            $originalName = $file['name'];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
            if (!in_array($ext, $allowedExtensions)) {
                return ['status' => 'error', 'message' => 'Format fail tidak dibenarkan. Hanya PDF, JPG, dan PNG diterima.'];
            }

            if ($fileSize > (5 * 1024 * 1024)) {
                return ['status' => 'error', 'message' => 'Saiz fail terlalu besar. Had maksimum adalah 5MB.'];
            }

            $oldFileName = null;
            $existingDocs = $this->model->getStudentDocumentsList($studentId, $appId); 
            if (!empty($existingDocs)) {
                foreach ($existingDocs as $doc) {
                    $currentTypeId = (int)($doc['doc_type_id'] ?? 0);
                    
                    if ($currentTypeId === $docTypeId && !empty($doc['file_path'])) {
                        $oldFileName = basename($doc['file_path']);
                        break; 
                    }
                }
            }

            $safeDocName = preg_replace('/[^a-z0-9]+/i', '_', strtolower($docName)); 
            $newFileName = $studentId . '_' . $safeDocName . '_' . time() . '.' . $ext;

            $uploadDir = __DIR__ . '/../pages/student/portfolio/upload-documents/uploads/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true); 
            }

            $destination = $uploadDir . $newFileName;
            $dbPath = 'uploads/' . $newFileName; 

            if (move_uploaded_file($tmpName, $destination)) {
                $dbData = [
                    'student_id'       => $studentId,
                    'application_id'   => $appId,
                    'document_type_id' => $docTypeId, 
                    'file_name'        => $originalName, 
                    'file_path'        => $dbPath
                ];

                $this->model->saveDocumentRecord($dbData);

                if ($oldFileName) {
                    $oldPhysicalPath = $uploadDir . $oldFileName;
                    if (file_exists($oldPhysicalPath) && is_file($oldPhysicalPath)) {
                        @unlink($oldPhysicalPath);
                    }
                }

                return ['status' => 'success', 'message' => 'Fail berjaya dimuat naik.', 'file_path' => $dbPath];
            } else {
                return ['status' => 'error', 'message' => 'Ralat semasa memindahkan fail.'];
            }
            
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()];
        }
    }
}