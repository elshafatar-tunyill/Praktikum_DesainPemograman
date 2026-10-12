<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../includes/koneksi.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$BATAS_PERINGATAN = 3;   
$BATAS_KUNCI      = 5;   
$DURASI_KUNCI     = 60;  

if (!isset($_SESSION['login_attempt'])) {
    $_SESSION['login_attempt'] = [
        'username'    => '',
        'count'       => 0,
        'locked_until'=> 0,
        'last_try'    => 0,
    ];
}
$attempt = &$_SESSION['login_attempt'];

if ($attempt['username'] !== $username) {
    $attempt['username']    = $username;
    $attempt['count']       = 0;
    $attempt['locked_until'] = 0;
}

if ($attempt['locked_until'] > time()) {
    $sisa = $attempt['locked_until'] - time();
    $_SESSION['flash'] = [
        'type'  => 'error',
        'pesan' => "Terlalu banyak percobaan gagal. Coba lagi dalam {$sisa} detik."
    ];
    header('Location: login.php');
    exit;
}


$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    unset($_SESSION['login_attempt']);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama']    = $user['nama'];
    $_SESSION['role']    = $user['role'];

    header('Location: ../index.php');
    exit;
}

$attempt['count']++;
$attempt['last_try'] = time();

if ($attempt['count'] >= $BATAS_KUNCI) {
    $attempt['locked_until'] = time() + $DURASI_KUNCI;
    $_SESSION['flash'] = [
        'type'  => 'error',
        'pesan' => "Anda telah gagal login {$attempt['count']} kali. Akun dikunci sementara selama {$DURASI_KUNCI} detik."
    ];
} elseif ($attempt['count'] >= $BATAS_PERINGATAN) {
    $sisa = $BATAS_KUNCI - $attempt['count'];
    $_SESSION['flash'] = [
        'type'  => 'error',
        'pesan' => "Username atau password salah. Peringatan: sisa {$sisa} percobaan sebelum akun dikunci sementara."
    ];
} else {
    $_SESSION['flash'] = [
        'type'  => 'error',
        'pesan' => 'Username atau password salah.'
    ];
}

header('Location: login.php');
exit;