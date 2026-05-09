<?php
// View/FrontOffice/super_user_opportunities.php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/OportunityController.php';
requireRole(['super_user']);

$assetPath = '../assets/';
$controller = new OportunityController();
$message = ''; $messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $dp = !empty($_POST['datePublication']) ? new DateTime($_POST['datePublication']) : null;
        $controller->addOportunity(new Oportunity(null, $_POST['Titre']??null, $_POST['Type_job']??null, $_POST['Description']??null, $_POST['Localisation']??null, $dp, $_POST['Statut']??null));
        $message = 'Opportunite creee !'; $messageType = 'success';
    } elseif ($_POST['action'] === 'update') {
        $id = (int)$_POST['id'];
        $dp = !empty($_POST['datePublication']) ? new DateTime($_POST['datePublication']) : null;
        $controller->updateOportunity(new Oportunity($id, $_POST['Titre']??null, $_POST['Type_job']??null, $_POST['Description']??null, $_POST['Localisation']??null, $dp, $_POST['Statut']??null), $id);
        $message = 'Opportunite mise a jour !'; $messageType = 'success';
    } elseif ($_POST['action'] === 'delete') {
        $controller->deleteOportunity((int)$_POST['id']);
        $message = 'Opportunite supprimee.'; $messageType = 'danger';
    }
}
if (isset($_GET['fetch_id'])) {
    header('Content-Type: application/json');
    echo json_encode($controller->showOportunity((int)$_GET['fetch_id']));
    exit();
}
$opportunitiesList = ($r = $controller->listOportunities()) ? $r->fetchAll(PDO::FETCH_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes opportunites - Skiller</title>
</head>
<body>
<?php include __DIR__ . '/../navbar.php'; ?>

<div class="sk-page">
  <?php if ($message): ?>
    <div class="sk-toast sk-toast-<?= $messageType ?>" id="sk-toast">
      <i class="bx <?= $messageType==='success'?'bx-check-circle':'bx-x-circle' ?>"></i>
      <?= htmlspecialchars($message) ?>
    </div>
    <script>setTimeout(()=>{const t=document.getElementById('sk-toast');if(t){t.style.opacity='0';t.style.transition='opacity .4s';setTimeout(()=>t.remove(),400);}},3000);</script>
  <?php endif; ?>

  <div class="sk-page-header">
    <div class="sk-page-title">
      Mes opportunites
      <small>Gestion complete - creation, modification et suppression</small>
    </div>
    <button class="sk-btn sk-btn-primary" onclick="openModal('addModal')">
      <i class="bx bx-plus"></i> Ajouter une opportunite
    </button>
  </div>

  <div class="sk-card">
    <table class="sk-table">
      <thead>
        <tr><th>ID</th><th>Titre</th><th>Type</th><th>Lieu</th><th>Publie le</th><th>Statut</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($opportunitiesList as $row):
          $sc = $row['Statut']==='actif' ? 'actif' : ($row['Statut']==='archivÃ©' ? 'archive' : 'expire');
        ?>
          <tr>
            <td style="color:var(--sk-muted);font-size:.8rem">#<?= $row['ID'] ?></td>
            <td style="font-weight:600"><?= htmlspecialchars($row['Titre']) ?></td>
            <td><span class="sk-badge sk-badge-<?= htmlspecialchars($row['Type_job']) ?>"><?= htmlspecialchars($row['Type_job']) ?></span></td>
            <td style="color:var(--sk-muted)"><?= htmlspecialchars($row['Localisation'] ?? 'â€”') ?></td>
            <td style="color:var(--sk-muted)"><?= htmlspecialchars($row['datePublication'] ?? 'â€”') ?></td>
            <td><span class="sk-badge sk-badge-<?= $sc ?>"><?= htmlspecialchars($row['Statut']) ?></span></td>
            <td style="white-space:nowrap">
              <button class="sk-btn sk-btn-warn sk-btn-sm" onclick="openModifierModal(<?= $row['ID'] ?>)"><i class="bx bx-edit-alt"></i></button>
              <button class="sk-btn sk-btn-danger sk-btn-sm" onclick="openDeleteModal(<?= $row['ID'] ?>, '<?= htmlspecialchars($row['Titre'], ENT_QUOTES) ?>')"><i class="bx bx-trash"></i></button>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($opportunitiesList)): ?>
          <tr><td colspan="7" class="sk-empty">Aucune opportunite pour le moment. Creez la premiere !</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- AJOUT -->
<div class="sk-modal-overlay" id="addModal">
  <div class="sk-modal">
    <div class="sk-modal-header">
      <span class="sk-modal-title">Ajouter une opportunite</span>
      <button class="sk-modal-close" onclick="closeModal('addModal')">Ã—</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="sk-modal-body">
        <div class="sk-form-row">
          <div><label class="sk-label">Titre *</label><input type="text" name="Titre" class="sk-input" required></div>
          <div><label class="sk-label">Type *</label>
            <select name="Type_job" class="sk-select" required>
              <option value="">Choisir...</option>
              <option value="jobs">Emploi</option>
              <option value="freelance">Freelance</option>
              <option value="stage">Stage</option>
            </select>
          </div>
        </div>
        <div class="sk-form-group"><label class="sk-label">Description</label><textarea name="Description" class="sk-textarea"></textarea></div>
        <div class="sk-form-row">
          <div><label class="sk-label">Lieu</label><input type="text" name="Localisation" class="sk-input"></div>
          <div><label class="sk-label">Date *</label><input type="date" name="datePublication" class="sk-input" value="<?= date('Y-m-d') ?>" required></div>
        </div>
        <div class="sk-form-group"><label class="sk-label">Statut *</label>
          <select name="Statut" class="sk-select" required>
            <option value="">Choisir...</option>
            <option value="actif">Actif</option>
            <option value="archivÃ©">ArchivÃ©</option>
            <option value="expirÃ©">ExpirÃ©</option>
          </select>
        </div>
      </div>
      <div class="sk-modal-footer">
        <button type="button" class="sk-btn sk-btn-ghost" onclick="closeModal('addModal')">Annuler</button>
        <button type="submit" class="sk-btn sk-btn-primary">Creer</button>
      </div>
    </form>
  </div>
</div>

<!-- MODIFICATION -->
<div class="sk-modal-overlay" id="editModal">
  <div class="sk-modal">
    <div class="sk-modal-header">
      <span class="sk-modal-title">Modifier Opportunite</span>
      <button class="sk-modal-close" onclick="closeModal('editModal')">Ã—</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="editId">
      <div class="sk-modal-body" id="editBody"><div class="sk-loading">Chargement...</div></div>
      <div class="sk-modal-footer">
        <button type="button" class="sk-btn sk-btn-ghost" onclick="closeModal('editModal')">Annuler</button>
        <button type="submit" class="sk-btn sk-btn-primary">Mettre a jour</button>
      </div>
    </form>
  </div>
</div>

<!-- SUPPRESSION -->
<div class="sk-modal-overlay" id="deleteModal">
  <div class="sk-modal sk-modal-sm">
    <div class="sk-modal-header">
      <span class="sk-modal-title" style="color:var(--sk-danger)">Supprimer Opportunite</span>
      <button class="sk-modal-close" onclick="closeModal('deleteModal')">Ã—</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="id" id="deleteId">
      <div class="sk-modal-body">
        <p style="color:var(--sk-muted);font-size:.875rem">Supprimer <strong id="deleteTitre" style="color:var(--sk-text)"></strong>? Cette action est irreversible.</p>
      </div>
      <div class="sk-modal-footer">
        <button type="button" class="sk-btn sk-btn-ghost" onclick="closeModal('deleteModal')">Annuler</button>
        <button type="submit" class="sk-btn sk-btn-danger">Supprimer</button>
      </div>
    </form>
  </div>
</div>

<style>.sk-loading{text-align:center;padding:32px;color:var(--sk-muted)}</style>
<script>
function openModal(id){document.getElementById(id).classList.add('open');}
function closeModal(id){document.getElementById(id).classList.remove('open');}
function openDeleteModal(id,title){document.getElementById('deleteId').value=id;document.getElementById('deleteTitre').textContent=title;openModal('deleteModal');}
function openModifierModal(id){
  document.getElementById('editId').value=id;
  document.getElementById('editBody').innerHTML='<div class="sk-loading">Chargement...</div>';
  openModal('editModal');
  fetch('?fetch_id='+id).then(r=>r.json()).then(d=>{
    document.getElementById('editBody').innerHTML=`
      <div class="sk-form-row">
        <div><label class="sk-label">Titre *</label><input type="text" name="Titre" class="sk-input" value="${esc(d.Titre||'')}" required></div>
        <div><label class="sk-label">Type *</label><select name="Type_job" class="sk-select" required>
          <option value="">Choisir...</option>
          <option value="jobs" ${d.Type_job==='jobs'?'selected':''}>Emploi</option>
          <option value="freelance" ${d.Type_job==='freelance'?'selected':''}>Freelance</option>
          <option value="stage" ${d.Type_job==='stage'?'selected':''}>Stage</option>
        </select></div>
      </div>
      <div class="sk-form-group"><label class="sk-label">Description</label><textarea name="Description" class="sk-textarea">${esc(d.Description||'')}</textarea></div>
      <div class="sk-form-row">
        <div><label class="sk-label">Lieu</label><input type="text" name="Localisation" class="sk-input" value="${esc(d.Localisation||'')}"></div>
        <div><label class="sk-label">Date *</label><input type="date" name="datePublication" class="sk-input" value="${esc(d.datePublication||'')}" required></div>
      </div>
      <div class="sk-form-group"><label class="sk-label">Statut *</label><select name="Statut" class="sk-select" required>
        <option value="">Choisir...</option>
        <option value="actif" ${d.Statut==='actif'?'selected':''}>Actif</option>
        <option value="archivÃ©" ${d.Statut==='archivÃ©'?'selected':''}>ArchivÃ©</option>
        <option value="expirÃ©" ${d.Statut==='expirÃ©'?'selected':''}>ExpirÃ©</option>
      </select></div>`;
  });
}
function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
document.querySelectorAll('.sk-modal-overlay').forEach(el=>el.addEventListener('click',e=>{if(e.target===el)el.classList.remove('open');}));
</script>
</body>
</html>

