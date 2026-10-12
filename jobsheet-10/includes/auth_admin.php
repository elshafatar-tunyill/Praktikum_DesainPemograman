<?php
require_once __DIR__ . '/auth.php';

if ($_SESSION['role'] !== 'admin') {
    $_SESSION['flash'] = [
        'type'  => 'error',
        'pesan' => 'Akses ditolak: Hanya Admin yang boleh melakukan aksi ini.'
    ];
    header('Location: list.php');
    exit;
}