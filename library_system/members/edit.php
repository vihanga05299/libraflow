<?php
require_once '../includes/config.php';
requireLogin();
$id = (int)($_GET['id'] ?? 0);
$m = $conn->query("SELECT * FROM members WHERE id=$id")->fetch_assoc();
if (!$m) { redirect('index.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = clean($conn, $_POST['name']);
    $email   = clean($conn, $_POST['email']);
    $phone   = clean($conn, $_POST['phone']);
    $address = clean($conn, $_POST['address']);
    $status  = clean($conn, $_POST['status']);
    $conn->query("UPDATE members SET name='$name',email='$email',phone='$phone',
                  address='$address',status='$status' WHERE id=$id");
    $_SESSION['msg'] = 'Member updated.';
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Member — LibraFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require_once '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar"><h2>Edit Member</h2><a href="index.php" class="btn btn-outline">← Back</a></div>
  <div class="content">
    <div class="card">
      <div class="card-header"><h3><?= htmlspecialchars($m['name']) ?></h3></div>
      <div class="card-body" style="padding:28px">
        <form method="POST">
          <div class="form-grid">
            <div class="form-group">
              <label>Full Name</label>
              <input type="text" name="name" value="<?= htmlspecialchars($m['name']) ?>" required>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" value="<?= htmlspecialchars($m['email']) ?>" required>
            </div>
            <div class="form-group">
              <label>Phone</label>
              <input type="text" name="phone" value="<?= htmlspecialchars($m['phone']) ?>">
            </div>
            <div class="form-group">
              <label>Status</label>
              <select name="status">
                <option value="active"     <?= $m['status']=='active'?'selected':'' ?>>Active</option>
                <option value="suspended"  <?= $m['status']=='suspended'?'selected':'' ?>>Suspended</option>
              </select>
            </div>
            <div class="form-group full">
              <label>Address</label>
              <textarea name="address"><?= htmlspecialchars($m['address']) ?></textarea>
            </div>
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Update Member</button>
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
