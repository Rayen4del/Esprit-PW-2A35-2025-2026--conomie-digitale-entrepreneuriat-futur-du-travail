<?php

$uploadDir = __DIR__ . "/uploads/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (!isset($_FILES['file'])) {
    http_response_code(400);
    exit("No file received");
}

$file = $_FILES['file'];

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

$allowed = ['jpg','jpeg','png','mp4','webm','pdf'];

if (!in_array($ext, $allowed)) {
    http_response_code(400);
    exit("File type not allowed");
}

$newName = uniqid("file_", true) . "." . $ext;

$path = $uploadDir . $newName;

if (move_uploaded_file($file['tmp_name'], $path)) {

    // URL retournée au JS
    echo "uploads/" . $newName;

} else {
    http_response_code(500);
    echo "Upload failed";
}