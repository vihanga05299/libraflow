<?php
require_once '../includes/config.php';
require_once '../includes/dsa.php';
requireLogin();

$stack = new LibraryStack($conn);
$msg   = '';
$error = '';
if (isset($_SESSION['msg'])) { $msg = $_SESSION['msg']; unset($_SESSION['msg']); }

// Push reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reserve') {
    $book_id   = (int)$_POST['book_id'];
    $member_id = (int)$_POST['member_id'];
    // Check not already reserved
    $exists = $conn->query(
        "SELECT id FROM reservation_stack WHERE book_id=$book_id AND member_id=$member_id AND status='pending'"
    )->num_rows;
    if ($exists) { $error = 'Member already has a pending reservation for this book.'; }
    else {
        $id = $stack->push($book_id, $member_id);
        $_SESSION['msg'] = "✅ Reservation pushed to stack! ID: #$id";
        redirect('index.php');
    }
}

// Pop reservation (fulfil)
if (isset($_GET['pop'])) {
    $book_id = (int)$_GET['pop'];
    $popped  = $stack->pop($book_id);
    if ($popped) {
        $_SESSION['msg'] = "✅ Top reservation popped and fulfilled for member ID {$popped['member_id']}.";
    }
    redirect('index.php');
}

// Cancel
if (isset($_GET['cancel'])) {
    $rid = (int)$_GET['cancel'];
    $conn->query("UPDATE reservation_stack SET status='cancelled' WHERE id=$rid");
    $_SESSION['msg'] = 'Reservation cancelled.';
    redirect('index.php');
}

$books   = $conn->query("SELECT * FROM books ORDER BY title");
$members = $conn->query("SELECT * FROM members WHERE status='active' ORDER BY name");
$pending = $conn->query(
    "SELECT rs.*, b.title, m.name AS member_name
       FROM reservation_stack rs
       JOIN books b ON rs.book_id=b.id
       JOIN members m ON rs.member_id=m.id
      WHERE rs.status='pending'
      ORDER BY rs.book_id, rs.stack_depth DESC"
);
$pendingList = [];
while ($r = $pending->fetch_assoc()) $pendingList[] = $r;

// Stack visual per book
$stackPerBook = [];
foreach ($pendingList as $p) { $stackPerBook[$p['book_id']][] = $p; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reservations — LibraFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require_once '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar"><h2>Reservations (Stack)</h2></div>
  <div class="content">
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <!-- DSA: Stack Visualization -->
    <?php if (!empty($pendingList)): ?>
    <div class="dsa-box">
      <h3>🗃 Reservation Stack — LIFO Data Structure</h3>
      <p style="font-size:.8rem;color:var(--muted);margin-bottom:16px;">
        <strong>Push</strong> adds to TOP · <strong>Pop</strong> removes from TOP · Time Complexity: O(1)
      </p>
      <?php foreach (array_slice($stackPerBook,0,2,true) as $bid => $items): ?>
        <?php $bookTitle = $items[0]['title']; ?>
        <div style="margin-bottom:12px">
          <span style="font-size:.75rem;color:var(--gold-lt);text-transform:uppercase;letter-spacing:1px">
            📖 <?= htmlspecialchars(substr($bookTitle,0,30)) ?>
          </span>
          <div class="dsa-items" style="margin-top:6px">
            <?php foreach ($items as $i => $node): ?>
              <div class="dsa-node <?= $i===0?'top-stack':'' ?>">
                Depth <?= $node['stack_depth'] ?><br>
                <span style="font-size:.68rem;opacity:.7"><?= htmlspecialchars(substr($node['member_name'],0,12)) ?></span>
              </div>
              <?php if ($i < count($items)-1): ?><span class="dsa-arrow">↓</span><?php endif; ?>
            <?php endforeach; ?>
            <a href="?pop=<?= $bid ?>" class="btn btn-success btn-sm"
               onclick="return confirm('Pop top reservation?')"
               style="margin-left:12px">⬆ Pop</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:28px">
      <!-- Push Form -->
      <div class="card">
        <div class="card-header"><h3>Add Reservation (Push)</h3></div>
        <div class="card-body" style="padding:24px">
          <form method="POST">
            <input type="hidden" name="action" value="reserve">
            <div class="form-group" style="margin-bottom:14px">
              <label>Book</label>
              <select name="book_id" required>
                <option value="">-- Select Book --</option>
                <?php $books->data_seek(0); while ($b = $books->fetch_assoc()): ?>
                  <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['title']) ?> (avail: <?= $b['available'] ?>)</option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="form-group" style="margin-bottom:20px">
              <label>Member</label>
              <select name="member_id" required>
                <option value="">-- Select Member --</option>
                <?php $members->data_seek(0); while ($m = $members->fetch_assoc()): ?>
                  <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
              📌 Reserve (Push to Stack)
            </button>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>About Stack</h3></div>
        <div class="card-body" style="padding:24px;font-size:.88rem;color:var(--slate);line-height:1.7">
          <p>The reservation system uses a <strong>Stack (LIFO)</strong> data structure.</p>
          <ul style="margin:12px 0 0 16px">
            <li><strong>Push</strong>: New reservation added on top</li>
            <li><strong>Pop</strong>: Most recent reservation fulfilled first</li>
            <li><strong>Peek</strong>: View top without removing</li>
          </ul>
          <div style="margin-top:14px;padding:10px;background:var(--cream);border-radius:6px;font-size:.78rem">
            Stack depth increases with each new reservation for the same book.
            The member at the highest depth is served next (LIFO order).
          </div>
        </div>
      </div>
    </div>

    <!-- Pending Reservations -->
    <div class="card">
      <div class="card-header"><h3>Pending Reservations</h3></div>
      <div class="card-body">
        <table>
          <thead><tr>
            <th>Stack Depth</th><th>ID</th><th>Book</th><th>Member</th><th>Reserved At</th><th>Status</th><th>Action</th>
          </tr></thead>
          <tbody>
          <?php foreach ($pendingList as $r): ?>
            <tr>
              <td><span style="font-family:monospace;color:var(--gold)"><?= $r['stack_depth'] ?></span></td>
              <td>#<?= $r['id'] ?></td>
              <td><?= htmlspecialchars($r['title']) ?></td>
              <td><?= htmlspecialchars($r['member_name']) ?></td>
              <td><?= $r['reserved_at'] ?></td>
              <td><span class="status status-borrowed">pending</span></td>
              <td>
                <a href="?cancel=<?= $r['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Cancel reservation?')">Cancel</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($pendingList)): ?>
            <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:30px">Stack is empty</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>
