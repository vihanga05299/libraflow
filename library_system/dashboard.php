<?php
require_once 'includes/config.php';
requireLogin();

// Stats
$stats = [];
$stats['books']     = $conn->query("SELECT COUNT(*) c FROM books")->fetch_assoc()['c'];
$stats['members']   = $conn->query("SELECT COUNT(*) c FROM members")->fetch_assoc()['c'];
$stats['borrowed']  = $conn->query("SELECT COUNT(*) c FROM borrow_queue WHERE status='borrowed'")->fetch_assoc()['c'];
$stats['overdue']   = $conn->query("SELECT COUNT(*) c FROM borrow_queue WHERE status='borrowed' AND due_date < CURDATE()")->fetch_assoc()['c'];

// Recent borrows
$recent = $conn->query(
    "SELECT bq.*, b.title, m.name AS member_name
       FROM borrow_queue bq
       JOIN books b ON bq.book_id=b.id
       JOIN members m ON bq.member_id=m.id
      ORDER BY bq.created_at DESC LIMIT 8"
);

// Queue visualization (up to 6 nodes)
$queueNodes = $conn->query(
    "SELECT bq.id, b.title, m.name AS member_name
       FROM borrow_queue bq
       JOIN books b  ON bq.book_id=b.id
       JOIN members m ON bq.member_id=m.id
      WHERE bq.status='borrowed'
      ORDER BY bq.queue_pos ASC LIMIT 6"
);
$queueItems = [];
while ($r = $queueNodes->fetch_assoc()) $queueItems[] = $r;

// Mark overdue
$conn->query("UPDATE borrow_queue SET status='overdue' WHERE status='borrowed' AND due_date < CURDATE()");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard — LibraFlow</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php require_once 'includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar">
    <h2>Dashboard</h2>
    <div class="topbar-right">
      <?php if ($stats['overdue'] > 0): ?>
        <span class="badge"><?= $stats['overdue'] ?> Overdue</span>
      <?php endif; ?>
      <span style="font-size:.85rem;color:var(--muted)"><?= date('l, d M Y') ?></span>
    </div>
  </div>

  <div class="content">
    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">📖</div>
        <div class="stat-num"><?= $stats['books'] ?></div>
        <div class="stat-label">Total Books</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-num"><?= $stats['members'] ?></div>
        <div class="stat-label">Registered Members</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🔄</div>
        <div class="stat-num"><?= $stats['borrowed'] ?></div>
        <div class="stat-label">Currently Borrowed</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">⚠️</div>
        <div class="stat-num" style="color:var(--rust)"><?= $stats['overdue'] ?></div>
        <div class="stat-label">Overdue Books</div>
      </div>
    </div>

    <!-- DSA: Queue Visualizer -->
    <?php if (!empty($queueItems)): ?>
    <div class="dsa-box">
      <h3>🗂 Borrow Queue (FIFO — Data Structure Visualization)</h3>
      <p style="font-size:.82rem;color:var(--muted);margin-bottom:16px;">
        Books are served in First-In-First-Out order. The front item was borrowed first.
      </p>
      <div class="dsa-items">
        <?php foreach ($queueItems as $i => $node): ?>
          <div class="dsa-node <?= $i===0?'front':'' ?>">
            #<?= $node['id'] ?><br>
            <span style="font-size:.7rem;opacity:.7"><?= htmlspecialchars(substr($node['title'],0,16)) ?>…</span>
          </div>
          <?php if ($i < count($queueItems)-1): ?><span class="dsa-arrow">→</span><?php endif; ?>
        <?php endforeach; ?>
        <span style="color:var(--muted);font-size:.8rem;margin-left:8px">← REAR (new borrows enqueue here)</span>
      </div>
    </div>
    <?php endif; ?>

    <!-- Recent borrows -->
    <div class="card">
      <div class="card-header">
        <h3>Recent Borrow Activity</h3>
        <a href="borrow/index.php" class="btn btn-outline btn-sm" style="color:var(--gold-lt);border-color:rgba(255,255,255,.2)">View All</a>
      </div>
      <div class="card-body">
        <table>
          <thead><tr>
            <th>#</th><th>Book</th><th>Member</th>
            <th>Borrow Date</th><th>Due Date</th><th>Status</th>
          </tr></thead>
          <tbody>
          <?php while ($r = $recent->fetch_assoc()): ?>
            <tr>
              <td><?= $r['id'] ?></td>
              <td><?= htmlspecialchars($r['title']) ?></td>
              <td><?= htmlspecialchars($r['member_name']) ?></td>
              <td><?= $r['borrow_date'] ?></td>
              <td><?= $r['due_date'] ?></td>
              <td><span class="status status-<?= $r['status'] ?>"><?= $r['status'] ?></span></td>
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
