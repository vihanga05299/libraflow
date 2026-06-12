<?php

require_once 'includes/config.php';

$_SESSION = [];

session_destroy();

redirect(BASE_URL . 'login.php');