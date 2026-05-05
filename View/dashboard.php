<?php
// View/dashboard.php
require_once __DIR__ . '/../config.php';
requireLogin();

$assetPath = './assets/';   // View/ → assets/ is one level up
$role = currentUserRole();
$name = $_SESSION['user']['name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Skiller</title>
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>

<div class="sk-page">
  <div class="sk-page-header">
    <div class="sk-page-title">
      Welcome back, <?= htmlspecialchars($name) ?>
      <small>Here's what you have access to</small>
    </div>
  </div>

  <div class="sk-dash-grid">
    <?php if ($role === 'admin'): ?>

      <a class="sk-dash-card" href="<?= appUrl('View/BackOffice/opportunities_backoffice.php') ?>">
        <div class="sk-dash-icon" style="background:rgba(91,108,255,.12)">
          <i class="bx bx-briefcase" style="color:#7c8fff"></i>
        </div>
        <div class="sk-dash-info">
          <h3>Opportunities</h3>
          <p>Edit &amp; delete listings. No create access for admin.</p>
        </div>
        <i class="bx bx-chevron-right sk-dash-arrow"></i>
      </a>

      <a class="sk-dash-card" href="<?= appUrl('View/BackOffice/applications_backoffice.php') ?>">
        <div class="sk-dash-icon" style="background:rgba(34,211,165,.12)">
          <i class="bx bx-file" style="color:#22d3a5"></i>
        </div>
        <div class="sk-dash-info">
          <h3>Applications</h3>
          <p>Full CRUD — review, update statuses, manage all records.</p>
        </div>
        <i class="bx bx-chevron-right sk-dash-arrow"></i>
      </a>

    <?php elseif ($role === 'super_user'): ?>

      <a class="sk-dash-card" href="<?= appUrl('View/FrontOffice/super_user_opportunities.php') ?>">
        <div class="sk-dash-icon" style="background:rgba(91,108,255,.12)">
          <i class="bx bx-briefcase" style="color:#7c8fff"></i>
        </div>
        <div class="sk-dash-info">
          <h3>My Opportunities</h3>
          <p>Full CRUD — create, edit, and delete your listings.</p>
        </div>
        <i class="bx bx-chevron-right sk-dash-arrow"></i>
      </a>

      <a class="sk-dash-card" href="<?= appUrl('View/FrontOffice/opportunities.php') ?>">
        <div class="sk-dash-icon" style="background:rgba(56,226,192,.12)">
          <i class="bx bx-search-alt" style="color:#38e2c0"></i>
        </div>
        <div class="sk-dash-info">
          <h3>Browse All Offers</h3>
          <p>See the public opportunity board as simple users see it.</p>
        </div>
        <i class="bx bx-chevron-right sk-dash-arrow"></i>
      </a>

      <a class="sk-dash-card" href="<?= appUrl('View/FrontOffice/super_user_applications.php') ?>">
        <div class="sk-dash-icon" style="background:rgba(34,211,165,.12)">
          <i class="bx bx-file" style="color:#22d3a5"></i>
        </div>
        <div class="sk-dash-info">
          <h3>All Applications</h3>
          <p>View all applications (read-only access).</p>
        </div>
        <i class="bx bx-chevron-right sk-dash-arrow"></i>
      </a>

    <?php else: ?>

      <a class="sk-dash-card" href="<?= appUrl('View/FrontOffice/opportunities.php') ?>">
        <div class="sk-dash-icon" style="background:rgba(91,108,255,.12)">
          <i class="bx bx-briefcase" style="color:#7c8fff"></i>
        </div>
        <div class="sk-dash-info">
          <h3>Browse Opportunities</h3>
          <p>Explore available jobs, freelance gigs, and internships.</p>
        </div>
        <i class="bx bx-chevron-right sk-dash-arrow"></i>
      </a>

      <a class="sk-dash-card" href="<?= appUrl('View/FrontOffice/applications.php') ?>">
        <div class="sk-dash-icon" style="background:rgba(34,211,165,.12)">
          <i class="bx bx-file" style="color:#22d3a5"></i>
        </div>
        <div class="sk-dash-info">
          <h3>My Applications</h3>
          <p>Apply to opportunities and manage your submissions.</p>
        </div>
        <i class="bx bx-chevron-right sk-dash-arrow"></i>
      </a>

    <?php endif; ?>
  </div>
</div>

<style>
.sk-dash-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; }
.sk-dash-card {
  display: flex; align-items: center; gap: 16px;
  background: var(--sk-surface); border: 1px solid var(--sk-border);
  border-radius: 14px; padding: 20px; text-decoration: none;
  transition: all .2s; cursor: pointer;
}
.sk-dash-card:hover { border-color: var(--sk-accent); transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,.35); }
.sk-dash-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.4rem; }
.sk-dash-info { flex: 1; }
.sk-dash-info h3 { font-size: .95rem; font-weight: 700; color: var(--sk-text); margin-bottom: 4px; }
.sk-dash-info p  { font-size: .78rem; color: var(--sk-muted); line-height: 1.45; }
.sk-dash-arrow { color: var(--sk-muted); font-size: 1.3rem; transition: transform .2s, color .2s; }
.sk-dash-card:hover .sk-dash-arrow { transform: translateX(4px); color: var(--sk-accent); }
</style>
</body>
</html>