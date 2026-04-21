<?php
// skiller/controller/PostController.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/blog/Post.php';

// ─────────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────────

// Detect the base path dynamically so it works on any machine / folder name
function getBaseUrl() {
    // e.g. /projet2_Copie/.../skiller  or just /skiller
    $script = $_SERVER['SCRIPT_NAME'];            // /skiller/controller/PostController.php
    $base   = dirname(dirname($script));          // /skiller
    return rtrim($base, '/');
}

function redirectTo($path, $msg = '') {
    $base = getBaseUrl();
    $url  = $base . $path;
    if ($msg) $url .= (strpos($url, '?') === false ? '?' : '&') . 'msg=' . urlencode($msg);
    header('Location: ' . $url);
    exit;
}

function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// ─────────────────────────────────────────────
// ROUTING — only accept POST
// ─────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectTo('/view/gestion_blog/index.php');
}

$action   = $_POST['action'] ?? '';
$postModel = new Post();

switch ($action) {

    // ─────────────────────────────────────────
    // BACK-OFFICE: Delete a post
    // ─────────────────────────────────────────
    case 'backDelete':
        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            if (isAjax()) jsonResponse(['success' => false, 'message' => 'Invalid post ID']);
            redirectTo('/view/gestion_blog/index.php', 'error');
        }

        $result = $postModel->delete($id);

        if (isAjax()) {
            jsonResponse(['success' => (bool)$result]);
        }

        redirectTo('/view/gestion_blog/index.php', $result ? 'deleted' : 'error');
        break;

    // ─────────────────────────────────────────
    // BACK-OFFICE: Change post status
    // ─────────────────────────────────────────
    case 'backChangeStatus':
        $id     = (int)($_POST['id']     ?? 0);
        $statut = trim($_POST['statut']  ?? '');

        $allowed = ['publié', 'brouillon', 'archivé'];

        if (!$id || !in_array($statut, $allowed)) {
            if (isAjax()) jsonResponse(['success' => false, 'message' => 'Invalid parameters']);
            redirectTo('/view/gestion_blog/index.php', 'error');
        }

        $result = $postModel->updateStatut($id, $statut);

        if (isAjax()) {
            jsonResponse(['success' => (bool)$result]);
        }

        redirectTo('/view/gestion_blog/index.php', $result ? 'status_updated' : 'error');
        break;

    // ─────────────────────────────────────────
    // FRONT-OFFICE: Create a post
    // ─────────────────────────────────────────
    case 'create':
        $titre    = trim($_POST['titre']    ?? '');
        $contenu  = trim($_POST['contenu']  ?? '');
        $categorie = trim($_POST['categorie'] ?? '');
        $statut   = trim($_POST['statut']   ?? 'brouillon');

        if (!$titre || !$contenu) {
            redirectTo('/view/gestion_blog/front_office/posts/index.php', 'error');
        }

        // Handle file upload (media/image)
        $mediaFilename = null;
        if (!empty($_FILES['media']['name'])) {
            $uploadDir = __DIR__ . '/../view/gestion_blog/uploads/posts/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext           = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
            $allowed_ext   = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov'];
            $mediaFilename = uniqid('post_') . '.' . $ext;

            if (in_array($ext, $allowed_ext)) {
                move_uploaded_file($_FILES['media']['tmp_name'], $uploadDir . $mediaFilename);
            } else {
                $mediaFilename = null;
            }
        }

        $postModel->create($titre, $contenu, $categorie, null, $mediaFilename, $statut);
        redirectTo('/view/gestion_blog/front_office/posts/index.php', 'created');
        break;

    // ─────────────────────────────────────────
    // FRONT-OFFICE: Update a post
    // ─────────────────────────────────────────
    case 'update':
        $id       = (int)($_POST['id']       ?? 0);
        $titre    = trim($_POST['titre']     ?? '');
        $contenu  = trim($_POST['contenu']   ?? '');
        $categorie = trim($_POST['categorie'] ?? '');

        if (!$id || !$titre || !$contenu) {
            redirectTo('/view/gestion_blog/front_office/posts/index.php', 'error');
        }

        $mediaFilename = null;
        if (!empty($_FILES['media']['name'])) {
            $uploadDir = __DIR__ . '/../view/gestion_blog/uploads/posts/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext           = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
            $allowed_ext   = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov'];
            $mediaFilename = uniqid('post_') . '.' . $ext;

            if (in_array($ext, $allowed_ext)) {
                move_uploaded_file($_FILES['media']['tmp_name'], $uploadDir . $mediaFilename);
            } else {
                $mediaFilename = null;
            }
        }

        $postModel->update($id, $titre, $contenu, $categorie, null, $mediaFilename);
        redirectTo('/view/gestion_blog/front_office/posts/index.php', 'updated');
        break;

    // ─────────────────────────────────────────
    // FRONT-OFFICE: Delete own post
    // ─────────────────────────────────────────
    case 'delete':
        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            redirectTo('/view/gestion_blog/front_office/posts/index.php', 'error');
        }

        $postModel->delete($id);
        redirectTo('/view/gestion_blog/front_office/posts/index.php', 'deleted');
        break;

    // ─────────────────────────────────────────
    // Unknown action
    // ─────────────────────────────────────────
    default:
        redirectTo('/view/gestion_blog/index.php', 'error');
        break;
}