<?php
declare(strict_types=1);

class Referees {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getReferees(string $studentId): array {
        $stmt = $this->db->prepare("SELECT * FROM apelq_referees WHERE student_id = :student_id ORDER BY referee_type ASC");
        $stmt->execute(['student_id' => $studentId]);
        
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $formatted = [];
        
        // Susun supaya mudah dipanggil di View: $formatted[1]['name'], $formatted[2]['name']
        foreach ($records as $row) {
            $formatted[(int)$row['referee_type']] = $row;
        }
        
        return $formatted;
    }

    // Fungsi UPSERT untuk menyimpan/mengemaskini rekod perujuk
    public function saveReferee(array $data): bool {
        $sql = "INSERT INTO apelq_referees 
                (student_id, referee_type, name, position_held, organization, office_phone, mobile_phone, email, relationship) 
                VALUES (:student_id, :referee_type, :name, :position_held, :organization, :office_phone, :mobile_phone, :email, :relationship)
                ON DUPLICATE KEY UPDATE 
                name = VALUES(name),
                position_held = VALUES(position_held),
                organization = VALUES(organization),
                office_phone = VALUES(office_phone),
                mobile_phone = VALUES(mobile_phone),
                email = VALUES(email),
                relationship = VALUES(relationship)";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'student_id'    => $data['student_id'],
            'referee_type'  => $data['referee_type'],
            'name'          => $data['name'],
            'position_held' => $data['position_held'],
            'organization'  => $data['organization'],
            'office_phone'  => $data['office_phone'],
            'mobile_phone'  => $data['mobile_phone'],
            'email'         => $data['email'],
            'relationship'  => $data['relationship']
        ]);
    }
}