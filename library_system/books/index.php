<?php
require_once '../includes/config.php';
requireLogin();

$msg = '';
if (isset($_SESSION['msg'])) { $msg = $_SESSION['msg']; unset($_SESSION['msg']); }

// Search
$search = clean($conn, $_GET['q'] ?? '');
$where  = $search ? "WHERE title LIKE '%$search%' OR author LIKE '%$search%' OR isbn LIKE '%$search%'" : '';
$books  = $conn->query("SELECT * FROM books $where ORDER BY title ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Books — LibraFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require_once '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar">
    <h2>Books Catalogue</h2>
    <a href="add.php" class="btn btn-primary">+ Add Book</a>
  </div>
  <div class="content">
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <form class="search-bar" method="GET">
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search by title, author or ISBN…">
      <button type="submit" class="btn btn-primary">Search</button>
      <?php if ($search): ?><a href="index.php" class="btn btn-outline">Clear</a><?php endif; ?>
    </form>

    <div class="card">
      <div class="card-header"><h3>All Books</h3></div>
      <div class="card-body">
        <table>
          <thead><tr>
            <th>ID</th><th>Title</th><th>Author</th><th>Category</th>
            <th>ISBN</th><th>Total</th><th>Available</th><th>Actions</th>
          </tr></thead>
          <tbody>
          <?php while ($b = $books->fetch_assoc()): ?>
            <tr>
              <td><?= $b['id'] ?></td>
              <td><strong><?= htmlspecialchars($b['title']) ?></strong></td>
              <td><?= htmlspecialchars($b['author']) ?></td>
              <td><?= htmlspecialchars($b['category']) ?></td>
              <td style="font-family:monospace;font-size:.8rem"><?= $b['isbn'] ?></td>
              <td><?= $b['total_copies'] ?></td>
              <td>
                <span class="status <?= $b['available']>0?'status-available':'status-borrowed' ?>">
                  <?= $b['available'] ?>
                </span>
              </td>
              <td>
                <a href="edit.php?id=<?= $b['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                <a href="delete.php?id=<?= $b['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Delete this book?')">Del</a>
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
