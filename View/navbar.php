<?php
/**
 * navbar.php — place in View/
 * Include from BackOffice/FrontOffice: include __DIR__ . '/../navbar.php';
 * Include from dashboard (View/):      include __DIR__ . '/navbar.php';
 *
 * Caller must define $assetPath before including.
 */
$role = currentUserRole();
$name = $_SESSION['user']['name'] ?? 'User';

$navLinks = [];
if ($role === 'admin') {
    $navLinks = [
        ['label' => 'Dashboard',     'href' => appUrl('View/dashboard.php'),                          'icon' => 'bx-grid-alt'],
        ['label' => 'Opportunities', 'href' => appUrl('View/BackOffice/opportunities_backoffice.php'), 'icon' => 'bx-briefcase'],
        ['label' => 'Applications',  'href' => appUrl('View/BackOffice/applications_backoffice.php'),  'icon' => 'bx-file'],
    ];
} elseif ($role === 'super_user') {
    $navLinks = [
        ['label' => 'Dashboard',        'href' => appUrl('View/dashboard.php'),                           'icon' => 'bx-grid-alt'],
        ['label' => 'My Opportunities', 'href' => appUrl('View/FrontOffice/super_user_opportunities.php'), 'icon' => 'bx-briefcase'],
        ['label' => 'Browse All',       'href' => appUrl('View/FrontOffice/opportunities.php'),            'icon' => 'bx-search-alt'],
        ['label' => 'All Applications', 'href' => appUrl('View/FrontOffice/super_user_applications.php'),  'icon' => 'bx-file'],
    ];
} else {
    $navLinks = [
        ['label' => 'Dashboard',     'href' => appUrl('View/dashboard.php'),                'icon' => 'bx-grid-alt'],
        ['label' => 'Opportunities', 'href' => appUrl('View/FrontOffice/opportunities.php'), 'icon' => 'bx-briefcase'],
        ['label' => 'My Favorites',  'href' => appUrl('View/FrontOffice/favorites.php'),    'icon' => 'bx-heart'],
        ['label' => 'My Applications', 'href' => appUrl('View/FrontOffice/applications.php'), 'icon' => 'bx-file'],
    ];
}
?>
<link rel="stylesheet" href="<?= $assetPath ?>vendor/fonts/boxicons.css">
<nav class="sk-navbar">
  <div class="sk-nav-inner">
    <a class="sk-brand" href="<?= appUrl('View/dashboard.php') ?>">
      <span class="sk-brand-dot"></span>Skiller
    </a>
    <ul class="sk-nav-links">
      <?php foreach ($navLinks as $link):
        $active = (basename($_SERVER['PHP_SELF']) === basename($link['href'])) ? 'active' : '';
      ?>
        <li>
          <a href="<?= $link['href'] ?>" class="sk-nav-link <?= $active ?>">
            <i class="bx <?= $link['icon'] ?>"></i>
            <span><?= htmlspecialchars($link['label']) ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class="sk-nav-user">
      <span class="sk-role-badge"><?= htmlspecialchars(str_replace('_', ' ', $role)) ?></span>
      <span class="sk-user-name"><?= htmlspecialchars($name) ?></span>
      <a class="sk-logout" href="<?= appUrl('View/Auth/logout.php') ?>">
        <i class="bx bx-log-out"></i> Logout
      </a>
    </div>
  </div>
</nav>
<style>
:root {
  --sk-bg:      #f8f9fa;
  --sk-surface: #ffffff;
  --sk-border:  #e5e7eb;
  --sk-accent:  #5b6cff;
  --sk-text:    #1a202c;
  --sk-muted:   #6b7280;
  --sk-danger:  #ff4d6d;
  --sk-success: #22d3a5;
  --sk-warn:    #f5a623;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--sk-bg); color: var(--sk-text); font-family: 'Segoe UI', system-ui, sans-serif; min-height: 100vh; }

.sk-navbar { background: var(--sk-surface); border-bottom: 1px solid var(--sk-border); position: sticky; top: 0; z-index: 200; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.sk-nav-inner { max-width: 1280px; margin: 0 auto; padding: 0 24px; height: 60px; display: flex; align-items: center; gap: 4px; }
.sk-brand { display: flex; align-items: center; gap: 8px; font-size: 1.05rem; font-weight: 700; color: var(--sk-text); text-decoration: none; letter-spacing: -.02em; white-space: nowrap; margin-right: 16px; }
.sk-brand-dot { width: 8px; height: 8px; background: var(--sk-accent); border-radius: 50%; box-shadow: 0 0 8px var(--sk-accent); }
.sk-nav-links { display: flex; list-style: none; gap: 2px; flex: 1; }
.sk-nav-link { display: flex; align-items: center; gap: 7px; padding: 7px 13px; border-radius: 8px; color: var(--sk-muted); text-decoration: none; font-size: .875rem; font-weight: 500; transition: all .15s; white-space: nowrap; }
.sk-nav-link i { font-size: 1.05rem; }
.sk-nav-link:hover { color: var(--sk-text); background: rgba(255,255,255,.06); }
.sk-nav-link.active { color: var(--sk-accent); background: rgba(91,108,255,.12); }
.sk-nav-user { display: flex; align-items: center; gap: 12px; margin-left: auto; }
.sk-role-badge { background: rgba(91,108,255,.15); color: var(--sk-accent); border: 1px solid rgba(91,108,255,.3); padding: 3px 10px; border-radius: 20px; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; white-space: nowrap; }
.sk-user-name { font-size: .85rem; color: var(--sk-text); font-weight: 500; white-space: nowrap; }
.sk-logout { display: flex; align-items: center; gap: 5px; color: var(--sk-muted); text-decoration: none; font-size: .8rem; padding: 6px 12px; border-radius: 7px; border: 1px solid var(--sk-border); transition: all .15s; white-space: nowrap; }
.sk-logout:hover { color: var(--sk-danger); border-color: var(--sk-danger); }

.sk-page { max-width: 1280px; margin: 0 auto; padding: 32px 24px; }
.sk-page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; gap: 16px; }
.sk-page-title { font-size: 1.4rem; font-weight: 700; letter-spacing: -.02em; }
.sk-page-title small { display: block; font-size: .78rem; font-weight: 400; color: var(--sk-muted); margin-top: 3px; }

.sk-card { background: var(--sk-surface); border: 1px solid var(--sk-border); border-radius: 14px; overflow: hidden; }
.sk-table { width: 100%; border-collapse: collapse; }
.sk-table thead th { background: rgba(255,255,255,.03); padding: 11px 16px; text-align: left; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .09em; color: var(--sk-muted); border-bottom: 1px solid var(--sk-border); white-space: nowrap; }
.sk-table tbody tr { border-bottom: 1px solid var(--sk-border); transition: background .12s; }
.sk-table tbody tr:last-child { border-bottom: none; }
.sk-table tbody tr:hover { background: rgba(255,255,255,.025); }
.sk-table td { padding: 13px 16px; font-size: .875rem; vertical-align: middle; }
.sk-empty { text-align: center; color: var(--sk-muted); padding: 48px 16px !important; font-size: .9rem; }

.sk-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; }
.sk-badge-jobs      { background:rgba(91,108,255,.15); color:#7c8fff;    border:1px solid rgba(91,108,255,.25); }
.sk-badge-freelance { background:rgba(245,166,35,.15); color:#f5a623;    border:1px solid rgba(245,166,35,.25); }
.sk-badge-stage     { background:rgba(34,211,165,.15); color:#22d3a5;    border:1px solid rgba(34,211,165,.25); }
.sk-badge-actif     { background:rgba(34,211,165,.15); color:#22d3a5;    border:1px solid rgba(34,211,165,.25); }
.sk-badge-archive   { background:rgba(122,128,153,.15);color:var(--sk-muted); border:1px solid rgba(122,128,153,.25); }
.sk-badge-expire    { background:rgba(255,77,109,.15);  color:#ff4d6d;   border:1px solid rgba(255,77,109,.25); }
.sk-badge-pending   { background:rgba(245,166,35,.15);  color:#f5a623;   border:1px solid rgba(245,166,35,.25); }
.sk-badge-accepted  { background:rgba(34,211,165,.15);  color:#22d3a5;   border:1px solid rgba(34,211,165,.25); }
.sk-badge-rejected  { background:rgba(255,77,109,.15);  color:#ff4d6d;   border:1px solid rgba(255,77,109,.25); }

.sk-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 8px; font-size: .85rem; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all .15s; font-family: inherit; }
.sk-btn-primary { background: var(--sk-accent); color: #fff; }
.sk-btn-primary:hover { background: #4a59e8; box-shadow: 0 4px 14px rgba(91,108,255,.4); }
.sk-btn-ghost   { background: transparent; color: var(--sk-muted); border: 1px solid var(--sk-border); }
.sk-btn-ghost:hover { color: var(--sk-text); border-color: #3a4155; }
.sk-btn-danger  { background: rgba(255,77,109,.12); color: var(--sk-danger); border: 1px solid rgba(255,77,109,.25); }
.sk-btn-danger:hover { background: rgba(255,77,109,.22); }
.sk-btn-warn    { background: rgba(245,166,35,.12); color: var(--sk-warn); border: 1px solid rgba(245,166,35,.25); }
.sk-btn-warn:hover   { background: rgba(245,166,35,.22); }
.sk-btn-sm      { padding: 5px 11px; font-size: .78rem; border-radius: 6px; }

.sk-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.3); backdrop-filter: blur(4px); z-index: 500; align-items: center; justify-content: center; }
.sk-modal-overlay.open { display: flex; }
.sk-modal { background: var(--sk-surface); border: 1px solid var(--sk-border); border-radius: 16px; width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,.1); animation: modalIn .22s ease; }
@keyframes modalIn { from { transform: translateY(16px) scale(.98); opacity: 0; } to { transform: none; opacity: 1; } }
.sk-modal-sm { max-width: 420px; }
.sk-modal-header { padding: 20px 24px 16px; border-bottom: 1px solid var(--sk-border); display: flex; justify-content: space-between; align-items: center; }
.sk-modal-title { font-size: 1rem; font-weight: 700; }
.sk-modal-close { background: none; border: none; color: var(--sk-muted); font-size: 1.4rem; cursor: pointer; line-height: 1; padding: 0; }
.sk-modal-close:hover { color: var(--sk-text); }
.sk-modal-body   { padding: 20px 24px; }
.sk-modal-footer { padding: 16px 24px 20px; border-top: 1px solid var(--sk-border); display: flex; gap: 10px; justify-content: flex-end; }

.sk-label { display: block; font-size: .72rem; font-weight: 700; color: var(--sk-muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
.sk-input, .sk-select, .sk-textarea { width: 100%; background: rgba(255,255,255,.04); border: 1px solid var(--sk-border); border-radius: 8px; color: var(--sk-text); font-size: .875rem; padding: 9px 12px; transition: border-color .15s; outline: none; font-family: inherit; }
.sk-input:focus, .sk-select:focus, .sk-textarea:focus { border-color: var(--sk-accent); box-shadow: 0 0 0 3px rgba(91,108,255,.15); }
.sk-select option { background: var(--sk-surface); }
.sk-textarea { resize: vertical; min-height: 82px; }
.sk-form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
.sk-form-group { margin-bottom: 16px; }

.sk-toast { position: fixed; top: 20px; right: 20px; z-index: 9999; padding: 12px 20px; border-radius: 10px; font-size: .85rem; font-weight: 500; box-shadow: 0 4px 12px rgba(0,0,0,.1); display: flex; align-items: center; gap: 8px; animation: toastIn .3s ease; }
@keyframes toastIn { from { transform: translateX(20px); opacity: 0; } to { transform: none; opacity: 1; } }
.sk-toast-success { background:rgba(34,211,165,.15); border:1px solid rgba(34,211,165,.3); color:var(--sk-success); }
.sk-toast-danger  { background:rgba(255,77,109,.15);  border:1px solid rgba(255,77,109,.3);  color:var(--sk-danger); }

@media (max-width: 768px) {
  .sk-nav-inner { gap: 6px; padding: 0 16px; }
  .sk-user-name { display: none; }
  .sk-nav-link span { display: none; }
  .sk-nav-link { padding: 7px 10px; }
  .sk-form-row { grid-template-columns: 1fr; }
  .sk-page { padding: 20px 16px; }
  .sk-page-header { flex-direction: column; }
}
</style>
