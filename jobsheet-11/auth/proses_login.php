<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';
csrf_verify();
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? '';

$ROLE_VALID       = ['admin', 'petugas'];
$BATAS_PERINGATAN = 3;
$BATAS_KUNCI      = 5;
$DURASI_KUNCI     = 60;
$TTL              = 15 * 60;

if (!in_array($role, $ROLE_VALID, true)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Role tidak valid.'];
    header('Location: login.php');
    exit;
}

// Setup counter
if (!isset($_SESSION['login_attempt'])) {
    $_SESSION['login_attempt'] = [
        'username'     => '',
        'count'        => 0,
        'locked_until' => 0,
        'last_try'     => 0,
    ];
}
$attempt = &$_SESSION['login_attempt'];

// TTL: reset kalau sudah lama tidak mencoba
if ($attempt['last_try'] > 0
    && (time() - $attempt['last_try']) > $TTL
    && $attempt['locked_until'] <= time()) {
    $attempt['count']        = 0;
    $attempt['locked_until'] = 0;
    $attempt['last_try']     = 0;
}

// Reset kalau username berbeda
if ($attempt['username'] !== $username) {
    $attempt['username']     = $username;
    $attempt['count']        = 0;
    $attempt['locked_until'] = 0;
}

// Cek lockout
if ($attempt['locked_until'] > time()) {
    $sisa = $attempt['locked_until'] - time();
    $_SESSION['flash'] = [
        'type'  => 'error',
        'pesan' => "Terlalu banyak percobaan gagal. Coba lagi dalam {$sisa} detik."
    ];
    header('Location: login.php');
    exit;
}

// Autentikasi
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    // ✅ Sukses
    unset($_SESSION['login_attempt']);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama']    = $user['nama'];
    $_SESSION['role']    = $role;   // ← dari dropdown (mode latihan)
    header('Location: ../index.php');
    exit;
}

// ❌ Gagal
$attempt['count']++;
$attempt['last_try'] = time();

if ($attempt['count'] >= $BATAS_KUNCI) {
    $attempt['locked_until'] = time() + $DURASI_KUNCI;
    $_SESSION['flash'] = [
        'type'  => 'error',
        'pesan' => "Gagal login {$attempt['count']} kali. Akun dikunci {$DURASI_KUNCI} detik."
    ];
} elseif ($attempt['count'] >= $BATAS_PERINGATAN) {
    $sisa = $BATAS_KUNCI - $attempt['count'];
    $_SESSION['flash'] = [
        'type'  => 'error',
        'pesan' => "Username atau password salah. Peringatan: sisa {$sisa} percobaan sebelum dikunci."
    ];
} else {
    $_SESSION['flash'] = [
        'type'  => 'error',
        'pesan' => 'Username atau password salah.'
    ];
}

header('Location: login.php');
exit;