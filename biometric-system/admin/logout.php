<?php
require_once __DIR__ . '/../auth.php';

// Logout user
Auth::logout();

// Redirect to login page with logout message
header('Location: ../login.php?logout=1');
exit;
