<?php
require_once '../includes/config.php';
requireLogin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = clean($conn, $_POST['name']);
    $email   = clean($conn, $_POST['email']);
    $phone   = clean($conn, $_POST['phone']);
    $address = clean($conn, $_POST['address']);
    $joined  = clean($conn, $_POST['joined_date']);

    if (!$name || !$email) { $error = 'Name and Email are required.'; }
    else {
        $conn->query(
            "INSERT INTO members (name,email,phone,address,joined_date)
             VALUES ('$name','$email','$phone','$address','$joined')"
        );
        if ($conn->insert_id) {
            $_SESSION['msg'] = "Member \"$name\" added.";
            redirect('index.php');
        }
        $error = 'Email already registered.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Add Member — LibraFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require_once '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar"><h2>Add Member</h2><a href="index.php" class="btn btn-outline">← Back</a></div>
  <div class="content">
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="card">
      <div class="card-header"><h3>New Member Registration</h3></div>
      <div class="card-body" style="padding:28px">
        <form method="POST">
          <div class="form-grid">
            <div class="form-group">
              <label>Full Name *</label>
              <input type="text" name="name" required>
            </div>
            <div class="form-group">
              <label>Email *</label>
              <input type="email" name="email" required>
            </div>
            <div class="form-group">
              <label>Phone</label>
              <input type="text" name="phone">
            </div>
            <div class="form-group">
              <label>Joined Date</label>
              <input type="date" name="joined_date" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group full">
              <label>Address</label>
              <textarea name="address"></textarea>
            </div>
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Register Member</button>
              <a href="index.php" class="btn btn-outline">Cancel</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
</body>
</html>
