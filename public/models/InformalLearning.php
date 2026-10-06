<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';

class InformalLearning
{
    private PDO $pdo;

    public function __construct()
    {
        // Guna sambungan pangkalan data sistem anda
        $this->pdo = Database::pdoMysql(); 
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // 1. Simpan Pengalaman Kerja
    public function addWorkExperience(string $userID, array $data): int
    {
        $sql = "INSERT INTO tbl_portfolio_work_exp 
                (user_id, employer_name, contact_address, date_from, date_to, position_held, what_learned) 
                VALUES (:user_id, :employer, :address, :d_from, :d_to, :position, :learned)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id'  => $userID,
            ':employer' => $data['employer_name'],
            ':address'  => $data['contact_address'],
            ':d_from'   => !empty($data['date_from']) ? $data['date_from'] : null,
            ':d_to'     => !empty($data['date_to']) ? $data['date_to'] : null,
            ':position' => $data['position_held'],
            ':learned'  => $data['what_learned']
        ]);

        return (int)$this->pdo->lastInsertId(); // Pulangkan ID rekod untuk link dengan dokumen
    }

    // 2. Simpan Aktiviti Pembelajaran Lain
    public function addOtherActivity(string $userID, array $data): int
    {
        $sql = "INSERT INTO tbl_portfolio_other_act 
                (user_id, activity_name, start_date, end_date, organizer, skill_level, what_learned) 
                VALUES (:user_id, :activity, :s_date, :e_date, :organizer, :skill, :learned)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id'   => $userID,
            ':activity'  => $data['activity_name'],
            ':s_date'    => !empty($data['start_date']) ? $data['start_date'] : null,
            ':e_date'    => !empty($data['end_date']) ? $data['end_date'] : null,
            ':organizer' => $data['organizer'],
            ':skill'     => $data['skill_level'] ?? null,
            ':learned'   => $data['what_learned']
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    // 3. Simpan Rekod Dokumen (Polymorphic untuk kedua-dua tab)
    public function addDocument(string $moduleType, int $recordID, string $fileName, string $filePath): bool
    {
        $sql = "INSERT INTO tbl_portfolio_documents 
                (module_type, record_id, file_name, file_path) 
                VALUES (:module, :record, :fname, :fpath)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':module' => $moduleType,
            ':record' => $recordID,
            ':fname'  => $fileName,
            ':fpath'  => $filePath
        ]);
    }
}