<?php
// Ambil payload dari GitHub
$payload = file_get_contents("php://input");
$data = json_decode($payload, true);

// Cek apakah ada push ke branch utama
if (isset($data['ref']) && $data['ref'] === 'refs/heads/main') {
    // Jalankan git pull di repository server
    $output = shell_exec("git -C D:/nginx/html/bacpos pull 2>&1");

    // Simpan log
    file_put_contents("webhook.log", date('Y-m-d H:i:s') . "\n" . $output . "\n", FILE_APPEND);

    echo "Git pull executed!";
} else {
    echo "Ignored.";
}
?>
