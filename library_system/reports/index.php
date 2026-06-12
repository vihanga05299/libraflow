<?php
require_once '../includes/config.php';
require_once '../includes/dsa.php';
requireLogin();

// Most borrowed books
$topBooks = $conn->query(
    "SELECT b.title, b.author, COUNT(bq.id) AS borrow_count
       FROM borrow_queue bq
       JOIN books b ON bq.book_id=b.id
      GROUP BY bq.book_id ORDER BY borrow_count DESC LIMIT 5"
);

// Active members
$topMembers = $conn->query(
    "SELECT m.name, COUNT(bq.id) AS borrow_count
       FROM borrow_queue bq
       JOIN members m ON bq.member_id=m.id
      GROUP BY bq.member_id ORDER BY borrow_count DESC LIMIT 5"
);

// Overdue list
$overdue = $conn->query(
    "SELECT bq.*, b.title, m.name AS member_name, m.email,
            DATEDIFF(CURDATE(), bq.due_date) AS days_overdue
       FROM borrow_queue bq
       JOIN books b ON bq.book_id=b.id
       JOIN members m ON bq.member_id=m.id
      WHERE bq.status IN ('borrowed','overdue') AND bq.due_date < CURDATE()
      ORDER BY days_overdue DESC"
);

// Binary Search Demo
$allBooks = $conn->query("SELECT id, title FROM books ORDER BY title ASC");
$booksArr = [];
while ($r = $allBooks->fetch_assoc()) $booksArr[] = $r;
$bsResult  = null;
$bsQuery   = '';
$bsSteps   = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bs_query'])) {
    $bsQuery  = trim($_POST['bs_query']);
    $low = 0; $high = count($booksArr)-1;
    $target = strtolower($bsQuery);
    while ($low <= $high) {
        $mid  = intdiv($low + $high, 2);
        $cmp  = strcmp(strtolower($booksArr[$mid]['title']), $target);
        $bsSteps[] = "mid=$mid → \"{$booksArr[$mid]['title']}\" " . ($cmp===0 ? "✅ FOUND" : ($cmp<0?"→ go RIGHT":"→ go LEFT"));
        if ($cmp === 0) { $bsResult = $booksArr[$mid]; break; }
        if ($cmp < 0)  $low  = $mid + 1;
        else           $high = $mid - 1;
    }
    if (!$bsResult) $bsSteps[] = "❌ Not found";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reports — LibraFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require_once '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar"><h2>Reports & Analytics</h2></div>
  <div class="content">

    <!-- Binary Search Demo -->
    <div class="dsa-box" style="margin-bottom:28px">
      <h3>🔍 Binary Search Demo — DSA Feature</h3>
      <p style="font-size:.8rem;color:var(--muted);margin-bottom:16px">
        Search books using Binary Search (O log n). Books array must be sorted — it is sorted by title. Enter an exact title.
      </p>
      <form method="POST" style="display:flex;gap:12px;flex-wrap:wrap">
        <input type="text" name="bs_query"
               value="<?= htmlspecialchars($bsQuery) ?>"
               placeholder="Enter exact book title…"
               style="flex:1;min-width:200px;background:rgba(255,255,255,.1);color:#fff;border-color:rgba(255,255,255,.2)">
        <button type="submit" class="btn btn-primary">Run Binary Search</button>
      </form>
      <?php if (!empty($bsSteps)): ?>
      <div style="margin-top:16px">
        <p style="font-size:.8rem;color:var(--gold-lt);margin-bottom:8px">Search trace (n=<?= count($booksArr) ?> books):</p>
        <?php foreach ($bsSteps as $step): ?>
          <div style="font-family:monospace;font-size:.8rem;color:#ccc;padding:2px 0"><?= htmlspecialchars($step) ?></div>
        <?php endforeach; ?>
        <?php if ($bsResult): ?>
          <div style="margin-top:10px;padding:10px 14px;background:rgba(45,106,79,.4);border-radius:6px;color:#fff;font-size:.88rem">
            ✅ Found: <strong><?= htmlspecialchars($bsResult['title']) ?></strong> (ID: <?= $bsResult['id'] ?>)
          </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:28px">
      <!-- Top Books -->
      <div class="card">
        <div class="card-header"><h3>Most Borrowed Books</h3></div>
        <div class="card-body">
          <table>
            <thead><tr><th>Title</th><th>Author</th><th>Borrows</th></tr></thead>
            <tbody>
            <?php while ($r = $topBooks->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($r['title']) ?></td>
                <td><?= htmlspecialchars($r['author']) ?></td>
                <td><strong style="color:var(--gold)"><?= $r['borrow_count'] ?></strong></td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Top Members -->
      <div class="card">
        <div class="card-header"><h3>Most Active Members</h3></div>
        <div class="card-body">
          <table>
            <thead><tr><th>Member</th><th>Borrows</th></tr></thead>
            <tbody>
            <?php while ($r = $topMembers->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td><strong style="color:var(--gold)"><?= $r['borrow_count'] ?></strong></td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Overdue -->
    <div class="card">
      <div class="card-header"><h3>⚠️ Overdue Books</h3></div>
      <div class="card-body">
        <table>
          <thead><tr>
            <th>Book</th><th>Member</th><th>Email</th><th>Due Date</th><th>Days Overdue</th>
          </tr></thead>
          <tbody>
          <?php $rows = 0; while ($r = $overdue->fetch_assoc()): $rows++; ?>
            <tr>
              <td><?= htmlspecialchars($r['title']) ?></td>
              <td><?= htmlspecialchars($r['member_name']) ?></td>
              <td><?= htmlspecialchars($r['email']) ?></td>
              <td><?= $r['due_date'] ?></td>
              <td><span class="status status-overdue"><?= $r['days_overdue'] ?> days</span></td>
            </tr>
          <?php endwhile; ?>
          <?php if ($rows===0): ?>
            <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:24px">No overdue books 🎉</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>
