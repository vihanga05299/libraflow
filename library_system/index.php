<?php
require_once 'includes/config.php';
if (isset($_SESSION['admin_id'])) {
    redirect('dashboard.php');
} else {
    redirect('login.php');
}
