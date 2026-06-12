<?php
require_once '../includes/config.php';
requireLogin();
$id = (int)($_GET['id'] ?? 0);
$conn->query("DELETE FROM members WHERE id=$id");
$_SESSION['msg'] = 'Member deleted.';
redirect('index.php');
