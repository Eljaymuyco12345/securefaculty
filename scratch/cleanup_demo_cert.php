<?php
require_once __DIR__ . '/../app/bootstrap.php';
try {
    $db = Database::getConnection();
    $stmt = $db->query("SELECT id, file_path FROM certificates WHERE title LIKE 'Certificate of Recognition - National Research Conference%'");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $db->exec("DELETE FROM certificates WHERE id = " . (int)$r['id']);
        if (!empty($r['file_path'])) {
            @unlink(__DIR__ . '/../storage/certificates/' . $r['file_path']);
        }
    }
} catch (Exception $e) {
    // ignore
}
