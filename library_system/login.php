<?php
require_once 'includes/config.php';

if (isset($_SESSION['admin_id'])) {
    redirect(BASE_URL . 'dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = clean($conn, $_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $sql = "SELECT * FROM admins WHERE username='$username' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {

        $admin = mysqli_fetch_assoc($result);

        // Plain Text Password Check
        if ($password === $admin['password']) {

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_user'] = $admin['username'];

            redirect(BASE_URL . 'dashboard.php');
        }
    }

    $error = "Invalid username or password.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>LibraFlow Login</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="login-page">

    <div class="login-box">

        <h1>📚 LibraFlow</h1>

        <p>Library Management System</p>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary">
                Sign In
            </button>

        </form>

    </div>

</div>

</body>
</html>