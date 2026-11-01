<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$page_title = "Login";
include __DIR__ . '/../includes/header.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$attempt = $_SESSION['login_attempt'] ?? null;
$batasKunci = 5;
$sisaPercobaan = null;

if ($attempt && $attempt['count'] > 0) {
    $sisaPercobaan = max(0, $batasKunci - $attempt['count']);
}
?>
        <section>
            <h2>Login Petugas</h2>

            <?php if ($flash): ?>
                <p class="flash flash-<?php echo $flash['type']; ?>"><?php echo $flash['pesan']; ?></p>
            <?php endif; ?>

            <?php if ($sisaPercobaan !== null && $sisaPercobaan > 0 && $attempt['count'] >= 3): ?>
                <p class="warning-box">
                    <strong><?php echo $attempt['count']; ?> kali</strong>.
                    Sisa <strong><?php echo $sisaPercobaan; ?></strong> percobaan sebelum dikunci.
                </p>
            <?php endif; ?>

            <?php if ($attempt && $attempt['locked_until'] > time()): ?>
                <p class="warning-box warning-box-danger">
                    <strong><?php echo $attempt['locked_until'] - time(); ?> detik</strong>.
                </p>
            <?php endif; ?>

            <form method="post" action="proses_login.php">
                <?php echo csrf_field(); ?>
                <p>
                    <label for="username">Username</label><br>
                    <input type="text" id="username" name="username" required>
                </p>
                <p>
                    <label for="password">Password</label><br>
                    <input type="password" id="password" name="password" required>
                </p>
                <p>
                    <label for="role">Role</label><br>
                    <select id="role" name="role" required>
                        <option value="admin">Admin</option>
                        <option value="petugas" selected>Petugas</option>
                    </select>
                </p>
                <p>
                    <button type="submit">Masuk</button>
                </p>
            </form>
            <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
