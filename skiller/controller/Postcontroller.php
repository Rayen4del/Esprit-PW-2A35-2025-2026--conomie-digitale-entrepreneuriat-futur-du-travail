<?php
// skiller/controller/blog/PostController.php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/blog/Post.php';

$postModel = new Post();
$action    = $_REQUEST['action'] ?? '';

switch ($action) {

    // ─── BACKOFFICE ───────────────────────────────────────────

    case 'backChangeStatus':
        $id     = (int)($_POST['id']     ?? 0);
        $statut = $_POST['statut'] ?? '';
        $allowed = ['publié', 'brouillon', 'archivé'];
        if ($id && in_array($statut, $allowed)) {
            $postModel->updateStatut($id, $statut);
        }
        header('Location: ../../view/gestion_blog/index.php');
        exit;

    case 'backDelete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            // Delete media file if exists
            $post = $postModel->getById($id);
            if ($post && !empty($post['media'])) {
                $mediaPath = __DIR__ . '/../../view/gestion_blog/uploads/posts/' . $post['media'];
                if (file_exists($mediaPath)) unlink($mediaPath);
            }
            $postModel->delete($id);
        }
        header('Location: ../../view/gestion_blog/index.php');
        exit;

    // ─── FRONTOFFICE ──────────────────────────────────────────

    case 'frontCreate':
        $titre    = trim($_POST['titre']    ?? '');
        $contenu  = trim($_POST['contenu']  ?? '');
        $categorie= trim($_POST['categorie']?? '');
        $id_user  = (int)($_POST['id_user'] ?? 1); // hardcoded to 1 until user module is ready
        $media    = null;

        if ($titre && $contenu) {
            // Handle file upload
            if (!empty($_FILES['media']['name'])) {
                $ext      = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('post_') . '.' . $ext;
                $dest     = __DIR__ . '/../../view/gestion_blog/uploads/posts/' . $filename;
                if (move_uploaded_file($_FILES['media']['tmp_name'], $dest)) {
                    $media = $filename;
                }
            }
            $postModel->create($id_user, $titre, $contenu, $categorie, $media);
        }
        header('Location: ../../view/gestion_blog/front office/posts/index.php');
        exit;

    case 'frontEdit':
        $id      = (int)($_POST['id']      ?? 0);
        $titre   = trim($_POST['titre']    ?? '');
        $contenu = trim($_POST['contenu']  ?? '');
        $categorie= trim($_POST['categorie']?? '');
        $media   = null;

        if ($id && $titre && $contenu) {
            if (!empty($_FILES['media']['name'])) {
                // Remove old media
                $old = $postModel->getById($id);
                if ($old && !empty($old['media'])) {
                    $oldPath = __DIR__ . '/../../view/gestion_blog/uploads/posts/' . $old['media'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }
                $ext      = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('post_') . '.' . $ext;
                $dest     = __DIR__ . '/../../view/gestion_blog/uploads/posts/' . $filename;
                if (move_uploaded_file($_FILES['media']['tmp_name'], $dest)) {
                    $media = $filename;
                }
            }
            $postModel->update($id, $titre, $contenu, $categorie, $media);
        }
        header('Location: ../../view/gestion_blog/front office/posts/index.php');
        exit;

case 'frontDelete':
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $post = $postModel->getById($id);
        if ($post && !empty($post['media'])) {
            $mediaPath = __DIR__ . '/../../view/gestion_blog/uploads/posts/' . $post['media'];
            if (file_exists($mediaPath)) unlink($mediaPath);
        }
        $postModel->delete($id);
    }
    // FIXED: Changed "front office" to "front_office" (underscore instead of space)
    header('Location: ../../view/gestion_blog/front_office/posts/index.php');
    exit;
}