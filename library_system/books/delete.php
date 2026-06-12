<?php
require_once '../includes/config.php';
requireLogin();
$id = (int)($_GET['id'] ?? 0);
$conn->query("DELETE FROM books WHERE id=$id");
$_SESSION['msg'] = 'Book deleted.';
redirect('index.php');
