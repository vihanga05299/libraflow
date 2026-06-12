<?php
require_once '../includes/config.php';
requireLogin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = clean($conn, $_POST['title']);
    $author  = clean($conn, $_POST['author']);
    $isbn    = clean($conn, $_POST['isbn']);
    $cat     = clean($conn, $_POST['category']);
    $pub     = clean($conn, $_POST['publisher']);
    $year    = (int)$_POST['year'];
    $copies  = max(1,(int)$_POST['total_copies']);

    if (!$title || !$author) {
        $error = 'Title and Author are required.';
    } else {
        $conn->query(
            "INSERT INTO books (title,author,isbn,category,publisher,year,total_copies,available)
             VALUES ('$title','$author','$isbn','$cat','$pub',$year,$copies,$copies)"
        );
        if ($conn->insert_id) {
            $_SESSION['msg'] = "Book \"$title\" added successfully.";
            redirect('index.php');
        }
        $error = 'Could not add book. ISBN may already exist.';
    }
}
$cats = $conn->query("SELECT name FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Add Book — LibraFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require_once '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar">
    <h2>Add New Book</h2>
    <a href="index.php" class="btn btn-outline">← Back</a>
  </div>
  <div class="content">
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="card">
      <div class="card-header"><h3>Book Details</h3></div>
      <div class="card-body" style="padding:28px">
        <form method="POST">
          <div class="form-grid">
            <div class="form-group full">
              <label>Title *</label>
              <input type="text" name="title" required placeholder="e.g. Introduction to Algorithms">
            </div>
            <div class="form-group">
              <label>Author *</label>
              <input type="text" name="author" required placeholder="e.g. Thomas Cormen">
            </div>
            <div class="form-group">
              <label>ISBN</label>
              <input type="text" name="isbn" placeholder="978-XXXXXXXXXX">
            </div>
            <div class="form-group">
              <label>Category</label>
              <select name="category">
                <option value="">-- Select --</option>
                <?php while ($c = $cats->fetch_assoc()): ?>
                  <option><?= htmlspecialchars($c['name']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Publisher</label>
              <input type="text" name="publisher">
            </div>
            <div class="form-group">
              <label>Year Published</label>
              <input type="number" name="year" min="1800" max="<?= date('Y') ?>" placeholder="<?= date('Y') ?>">
            </div>
            <div class="form-group">
              <label>Number of Copies</label>
              <input type="number" name="total_copies" value="1" min="1">
            </div>
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Save Book</button>
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
