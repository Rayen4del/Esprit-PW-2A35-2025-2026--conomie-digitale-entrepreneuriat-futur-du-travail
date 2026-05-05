<?php
require_once __DIR__ . '/config.php';
if (isLoggedIn()) {
    header('Location: ' . appUrl('View/dashboard.php'));
} else {
    header('Location: ' . appUrl('View/Auth/login.php'));
}
exit();
