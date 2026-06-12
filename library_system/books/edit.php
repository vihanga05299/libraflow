<?php
require_once '../includes/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$book = $conn->query("SELECT * FROM books WHERE id=$id")->fetch_assoc();
if (!$book) { $_SESSION['msg']='Book not found.'; redirect('index.php'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title  = clean($conn, $_POST['title']);
    $author = clean($conn, $_POST['author']);
    $isbn   = clean($conn, $_POST['isbn']);
    $cat    = clean($conn, $_POST['category']);
    $pub    = clean($conn, $_POST['publisher']);
    $year   = (int)$_POST['year'];
    $copies = max(1,(int)$_POST['total_copies']);
    $diff   = $copies - $book['total_copies'];
    $avail  = max(0, $book['available'] + $diff);

    $conn->query("UPDATE books SET title='$title',author='$author',isbn='$isbn',
                  category='$cat',publisher='$pub',year=$year,
                  total_copies=$copies,available=$avail WHERE id=$id");
    $_SESSION['msg'] = "Book updated.";
    redirect('index.php');
}
$cats = $conn->query("SELECT name FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Book — LibraFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require_once '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar">
    <h2>Edit Book</h2>
    <a href="index.php" class="btn btn-outline">← Back</a>
  </div>
  <div class="content">
    <div class="card">
      <div class="card-header"><h3>Edit: <?= htmlspecialchars($book['title']) ?></h3></div>
      <div class="card-body" style="padding:28px">
        <form method="POST">
          <div class="form-grid">
            <div class="form-group full">
              <label>Title *</label>
              <input type="text" name="title" required value="<?= htmlspecialchars($book['title']) ?>">
            </div>
            <div class="form-group">
              <label>Author *</label>
              <input type="text" name="author" required value="<?= htmlspecialchars($book['author']) ?>">
            </div>
            <div class="form-group">
              <label>ISBN</label>
              <input type="text" name="isbn" value="<?= htmlspecialchars($book['isbn']) ?>">
            </div>
            <div class="form-group">
              <label>Category</label>
              <select name="category">
                <option value="">-- Select --</option>
                <?php while ($c = $cats->fetch_assoc()): ?>
                  <option <?= $c['name']==$book['category']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Publisher</label>
              <input type="text" name="publisher" value="<?= htmlspecialchars($book['publisher']) ?>">
            </div>
            <div class="form-group">
              <label>Year</label>
              <input type="number" name="year" value="<?= $book['year'] ?>">
            </div>
            <div class="form-group">
              <label>Total Copies</label>
              <input type="number" name="total_copies" value="<?= $book['total_copies'] ?>" min="1">
            </div>
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Update Book</button>
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
