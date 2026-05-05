<?php
// =====================
// CONFIG
// =====================
include_once __DIR__ . '/../../../../config.php';

$uploadDir = rtrim(UPLOAD_DIR, '/') . '/';

// =====================
// CREATE FOLDER IF NOT EXISTS
// =====================
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// =====================
// CHECK FILE
// =====================
if (!isset($_FILES['file'])) {
    http_response_code(400);
    exit("No file received");
}

$file = $_FILES['file'];

// =====================
// MIME VALIDATION
// =====================
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedMime = [
    // images
    'image/jpeg',
    'image/png',
    'image/webp',

    // videos
    'video/mp4',
    'video/webm',

    // pdf
    'application/pdf'
];

if (!in_array($mime, $allowedMime)) {
    http_response_code(400);
    exit("File type not allowed");
}

// =====================
// SAFE EXTENSION
// =====================
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// =====================
// UNIQUE NAME
// =====================
$newName = uniqid("file_", true) . "." . $ext;

// =====================
// FULL PATH
// =====================
$fullPath = $uploadDir . $newName;

// =====================
// MOVE FILE
// =====================
if (move_uploaded_file($file['tmp_name'], $fullPath)) {

    // =====================
    // RETURN ONLY FILE NAME (IMPORTANT)
    // =====================
    echo $newName;

} else {
    http_response_code(500);
    echo "Upload failed";
}