<?php
// View/dashboard.php
require_once __DIR__ . '/../config.php';
requireLogin();

$assetPath = './assets/';
$role = currentUserRole();
$name = $_SESSION['user']['name'] ?? 'Utilisateur';
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tableau de bord - Skiller</title>
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>

<div class="sk-page">
  <div class="sk-page-header">
    <div class="sk-page-title">
      Bon retour, <?= htmlspecialchars($name) ?>
      <small>Voici les espaces auxquels vous avez acces</small>
    </div>
  </div>

  <div class="sk-dash-grid">
    <?php if ($role === 'admin'): ?>

      <a class="sk-dash-card" href="<?= appUrl('View/BackOffice/opportunities_backoffice.php') ?>">
        <div class="sk-dash-icon" style="background:rgba(91,108,255,.12)">
          <i class="bx bx-briefcase" style="color:#7c8fff"></i>
        </div>
        <div class="sk-dash-info">
          <h3>Opportunites</h3>
          <p>Modifier et supprimer les offres. La creation est reservee aux super utilisateurs.</p>
        </div>
        <i class="bx bx-chevron-right sk-dash-arrow"></i>
      </a>

      <a class="sk-dash-card" href="<?= appUrl('View/BackOffice/applications_backoffice.php') ?>">
        <div class="sk-dash-icon" style="background:rgba(34,211,165,.12)">
          <i class="bx bx-file" style="color:#22d3a5"></i>
        </div>
        <div class="sk-dash-info">
          <h3>Candidatures</h3>
          <p>Consulter, mettre a jour les statuts et gerer toutes les candidatures.</p>
        </div>
        <i class="bx bx-chevron-right sk-dash-arrow"></i>
      </a>

    <?php elseif ($role === 'super_user'): ?>

      <a class="sk-dash-card" href="<?= appUrl('View/FrontOffice/super_user_opportunities.php') ?>">
        <div class="sk-dash-icon" style="background:rgba(91,108,255,.12)">
          <i class="bx bx-briefcase" style="color:#7c8fff"></i>
        </div>
        <div class="sk-dash-info">
          <h3>Mes opportunites</h3>
          <p>Creer, modifier et supprimer vos propres offres.</p>
        </div>
        <i class="bx bx-chevron-right sk-dash-arrow"></i>
      </a>

      <a class="sk-dash-card" href="<?= appUrl('View/FrontOffice/opportunities.php') ?>">
        <div class="sk-dash-icon" style="background:rgba(56,226,192,.12)">
          <i class="bx bx-search-alt" style="color:#38e2c0"></i>
        </div>
        <div class="sk-dash-info">
          <h3>Voir toutes les offres</h3>
          <p>Voir le tableau public des offres comme les utilisateurs simples.</p>
        </div>
        <i class="bx bx-chevron-right sk-dash-arrow"></i>
      </a>

      <a class="sk-dash-card" href="<?= appUrl('View/FrontOffice/super_user_applications.php') ?>">
        <div class="sk-dash-icon" style="background:rgba(34,211,165,.12)">
          <i class="bx bx-file" style="color:#22d3a5"></i>
        </div>
        <div class="sk-dash-info">
          <h3>Toutes les candidatures</h3>
          <p>Consulter toutes les candidatures en lecture seule.</p>
        </div>
        <i class="bx bx-chevron-right sk-dash-arrow"></i>
      </a>

    <?php else: ?>

      <a class="sk-dash-card" href="<?= appUrl('View/FrontOffice/opportunities.php') ?>">
        <div class="sk-dash-icon" style="background:rgba(91,108,255,.12)">
          <i class="bx bx-briefcase" style="color:#7c8fff"></i>
        </div>
        <div class="sk-dash-info">
          <h3>Parcourir les opportunites</h3>
          <p>Explorer les emplois, missions freelance et stages disponibles.</p>
        </div>
        <i class="bx bx-chevron-right sk-dash-arrow"></i>
      </a>

      <a class="sk-dash-card" href="<?= appUrl('View/FrontOffice/applications.php') ?>">
        <div class="sk-dash-icon" style="background:rgba(34,211,165,.12)">
          <i class="bx bx-file" style="color:#22d3a5"></i>
        </div>
        <div class="sk-dash-info">
          <h3>Mes candidatures</h3>
          <p>Postuler aux opportunites et gerer vos demandes.</p>
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

