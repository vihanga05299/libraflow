<?php
require_once '../includes/config.php';
require_once '../includes/dsa.php';
requireLogin();

$queue = new LibraryQueue($conn);
$msg   = '';
$error = '';

if (isset($_SESSION['msg'])) { $msg = $_SESSION['msg']; unset($_SESSION['msg']); }

// Enqueue (Issue book)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'borrow') {
    $book_id   = (int)$_POST['book_id'];
    $member_id = (int)$_POST['member_id'];
    $due       = clean($conn, $_POST['due_date']);

    $book = $conn->query("SELECT * FROM books WHERE id=$book_id")->fetch_assoc();
    $member = $conn->query("SELECT * FROM members WHERE id=$member_id AND status='active'")->fetch_assoc();

    if (!$book)   { $error = 'Book not found.'; }
    elseif (!$member) { $error = 'Member not found or suspended.'; }
    elseif ($book['available'] < 1) { $error = 'No copies available. Please reserve instead.'; }
    else {
        $id = $queue->enqueue($book_id, $member_id, $due);
        $_SESSION['msg'] = "✅ Book issued! Borrow ID: #$id (Enqueued at position {$book['available']})";
        redirect('index.php');
    }
}

// Dequeue (Return book)
if (isset($_GET['return'])) {
    $borrow_id = (int)$_GET['return'];
    $queue->dequeue($borrow_id);
    $_SESSION['msg'] = '✅ Book returned and removed from queue (Dequeued).';
    redirect('index.php');
}

// Load data
$books   = $conn->query("SELECT * FROM books WHERE available > 0 ORDER BY title");
$members = $conn->query("SELECT * FROM members WHERE status='active' ORDER BY name");
$borrows = $conn->query(
    "SELECT bq.*, b.title, m.name AS member_name
       FROM borrow_queue bq
       JOIN books b ON bq.book_id=b.id
       JOIN members m ON bq.member_id=m.id
      WHERE bq.status IN ('borrowed','overdue')
      ORDER BY bq.queue_pos ASC"
);
$borrow_list = [];
while ($r = $borrows->fetch_assoc()) $borrow_list[] = $r;

// Queue visual
$queueVis = array_slice($borrow_list, 0, 7);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Borrow / Return — LibraFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require_once '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar"><h2>Borrow / Return (Queue)</h2></div>
  <div class="content">
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <!-- DSA: Queue Visualization -->
    <?php if (!empty($queueVis)): ?>
    <div class="dsa-box">
      <h3>📦 Borrow Queue — FIFO Data Structure</h3>
      <p style="font-size:.8rem;color:var(--muted);margin-bottom:16px;">
        <strong>Enqueue</strong> adds to REAR · <strong>Dequeue</strong> removes from FRONT · Time Complexity: O(1)
      </p>
      <div class="dsa-items">
        <span style="color:var(--gold);font-size:.8rem;">FRONT →</span>
        <?php foreach ($queueVis as $i => $node): ?>
          <div class="dsa-node <?= $i===0?'front':'' ?>">
            ID #<?= $node['id'] ?><br>
            <span style="font-size:.68rem;opacity:.7"><?= htmlspecialchars(substr($node['title'],0,12)) ?></span>
          </div>
          <?php if ($i < count($queueVis)-1): ?><span class="dsa-arrow">→</span><?php endif; ?>
        <?php endforeach; ?>
        <span style="color:var(--gold);font-size:.8rem;margin-left:4px">→ REAR</span>
      </div>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:28px">
      <!-- Issue Book Form -->
      <div class="card">
        <div class="card-header"><h3>Issue Book (Enqueue)</h3></div>
        <div class="card-body" style="padding:24px">
          <form method="POST">
            <input type="hidden" name="action" value="borrow">
            <div class="form-group" style="margin-bottom:14px">
              <label>Book (Available only)</label>
              <select name="book_id" required>
                <option value="">-- Select Book --</option>
                <?php $books->data_seek(0); while ($b = $books->fetch_assoc()): ?>
                  <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['title']) ?> (<?= $b['available'] ?> left)</option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="form-group" style="margin-bottom:14px">
              <label>Member</label>
              <select name="member_id" required>
                <option value="">-- Select Member --</option>
                <?php $members->data_seek(0); while ($m = $members->fetch_assoc()): ?>
                  <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="form-group" style="margin-bottom:20px">
              <label>Due Date</label>
              <input type="date" name="due_date" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
              📤 Issue Book (Enqueue)
            </button>
          </form>
        </div>
      </div>

      <!-- Quick stats -->
      <div class="card">
        <div class="card-header"><h3>Queue Summary</h3></div>
        <div class="card-body" style="padding:24px">
          <p style="font-size:.88rem;color:var(--muted);margin-bottom:16px">Currently active borrow records in queue</p>
          <?php
            $total   = count($borrow_list);
            $overdue = count(array_filter($borrow_list, fn($r) => $r['status']==='overdue'));
          ?>
          <div style="display:flex;gap:16px;flex-wrap:wrap">
            <div style="flex:1;background:var(--cream);border-radius:8px;padding:16px;text-align:center">
              <div style="font-size:2rem;font-family:'Playfair Display',serif"><?= $total ?></div>
              <div style="font-size:.78rem;color:var(--muted)">Total in Queue</div>
            </div>
            <div style="flex:1;background:var(--cream);border-radius:8px;padding:16px;text-align:center">
              <div style="font-size:2rem;font-family:'Playfair Display',serif;color:var(--rust)"><?= $overdue ?></div>
              <div style="font-size:.78rem;color:var(--muted)">Overdue</div>
            </div>
          </div>
          <div style="margin-top:16px;padding:12px;background:#fff3cd;border-radius:8px;font-size:.8rem;color:#856404">
            <strong>DSA Note:</strong> This queue uses O(n) space and O(1) enqueue/dequeue operations.
            Items are served in FIFO order — oldest borrow request returns first.
          </div>
        </div>
      </div>
    </div>

    <!-- Active Borrows Table -->
    <div class="card">
      <div class="card-header"><h3>Active Borrows</h3></div>
      <div class="card-body">
        <table>
          <thead><tr>
            <th>Queue#</th><th>Borrow ID</th><th>Book</th><th>Member</th>
            <th>Borrow Date</th><th>Due Date</th><th>Status</th><th>Action</th>
          </tr></thead>
          <tbody>
          <?php foreach ($borrow_list as $r): ?>
            <tr>
              <td><span style="font-family:monospace;color:var(--gold)"><?= $r['queue_pos'] ?></span></td>
              <td>#<?= $r['id'] ?></td>
              <td><?= htmlspecialchars($r['title']) ?></td>
              <td><?= htmlspecialchars($r['member_name']) ?></td>
              <td><?= $r['borrow_date'] ?></td>
              <td><?= $r['due_date'] ?></td>
              <td><span class="status status-<?= $r['status'] ?>"><?= $r['status'] ?></span></td>
              <td>
                <a href="?return=<?= $r['id'] ?>" class="btn btn-success btn-sm"
                   onclick="return confirm('Mark as returned?')">↩ Return (Dequeue)</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($borrow_list)): ?>
            <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:30px">Queue is empty</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>
