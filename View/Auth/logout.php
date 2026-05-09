<?php
require_once __DIR__ . '/../../config.php';
logoutUser();
header('Location: ' . appUrl('View/Auth/login.php'));
exit();


