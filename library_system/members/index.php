<?php
require_once '../includes/config.php';
requireLogin();
$msg = '';
if (isset($_SESSION['msg'])) { $msg = $_SESSION['msg']; unset($_SESSION['msg']); }

$search = clean($conn, $_GET['q'] ?? '');
$where  = $search ? "WHERE name LIKE '%$search%' OR email LIKE '%$search%'" : '';
$members = $conn->query("SELECT * FROM members $where ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Members — LibraFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require_once '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar">
    <h2>Members</h2>
    <a href="add.php" class="btn btn-primary">+ Add Member</a>
  </div>
  <div class="content">
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <form class="search-bar" method="GET">
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or email…">
      <button type="submit" class="btn btn-primary">Search</button>
      <?php if ($search): ?><a href="index.php" class="btn btn-outline">Clear</a><?php endif; ?>
    </form>
    <div class="card">
      <div class="card-header"><h3>All Members</h3></div>
      <div class="card-body">
        <table>
          <thead><tr>
            <th>ID</th><th>Name</th><th>Email</th><th>Phone</th>
            <th>Joined</th><th>Status</th><th>Actions</th>
          </tr></thead>
          <tbody>
          <?php while ($m = $members->fetch_assoc()): ?>
            <tr>
              <td><?= $m['id'] ?></td>
              <td><strong><?= htmlspecialchars($m['name']) ?></strong></td>
              <td><?= htmlspecialchars($m['email']) ?></td>
              <td><?= $m['phone'] ?></td>
              <td><?= $m['joined_date'] ?></td>
              <td><span class="status status-<?= $m['status'] ?>"><?= $m['status'] ?></span></td>
              <td>
                <a href="edit.php?id=<?= $m['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                <a href="delete.php?id=<?= $m['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Delete member?')">Del</a>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>
