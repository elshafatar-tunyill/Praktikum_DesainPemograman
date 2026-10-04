<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
    session_destroy();
    header('Location: debug_session.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Session</title>
</head>
<body>
    <h2>Isi $_SESSION saat ini</h2>
    
    <form method="POST">
        <button type="submit" name="reset">Reset Data</button>
    </form>

    <pre><?php print_r($_SESSION); ?></pre>
</body>
</html>