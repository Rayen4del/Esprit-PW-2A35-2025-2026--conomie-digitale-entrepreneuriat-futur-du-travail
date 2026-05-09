<?php
// View/FrontOffice/ai-search.php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/OportunityController.php';
require_once __DIR__ . '/../../Controller/AiSearchController.php';
require_once __DIR__ . '/../../Controller/FavoritesController.php';
requireLogin();

$assetPath = '../assets/';
$controller = new OportunityController();
$aiSearchController = new AiSearchController();
$favCtrl = new FavoritesController();
$userId = $_SESSION['user']['id'] ?? null;
$opportunitiesList = ($r = $controller->listOportunities()) ? $r->fetchAll(PDO::FETCH_ASSOC) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favorite_action'])) {
    if (!$userId) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Non connecte']);
        exit;
    }
    $favCtrl->toggleFavorite($userId, (int)($_POST['opportunity_id'] ?? 0));
}

// Recherche IA AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ai_search'])) {
    header('Content-Type: application/json');
    $query = trim($_POST['query'] ?? '');
    
    if (empty($query)) {
        echo json_encode(['success' => false, 'message' => 'Veuillez saisir une recherche']);
        exit;
    }

    $response = $aiSearchController->searchOpportunities($query, $opportunitiesList);
    if (!empty($response['results'])) {
        foreach ($response['results'] as &$result) {
            $result['is_favorited'] = $userId ? $favCtrl->isFavorited($userId, (int)($result['ID'] ?? 0)) : false;
        }
        unset($result);
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recherche IA - Skiller</title>
  <link rel="stylesheet" href="<?= $assetPath ?>vendor/css/core.css">
  <link rel="stylesheet" href="<?= $assetPath ?>vendor/css/theme-default.css">
  <link rel="stylesheet" href="<?= $assetPath ?>css/demo.css">
  <link rel="stylesheet" href="<?= $assetPath ?>vendor/fonts/boxicons.css">
</head>
<body>
<?php include __DIR__ . '/../navbar.php'; ?>

<div class="sk-page">
  <div class="sk-page-header">
    <div class="sk-page-title">
      Assistant de recherche IA
      <small>Trouvez des opportunites en langage naturel</small>
    </div>
    <a href="opportunities.php" class="sk-btn sk-btn-secondary">
      <i class="bx bx-arrow-back"></i> Retour
    </a>
  </div>

  <div style="display: grid; grid-template-columns: 1fr 350px; gap: 24px; max-width: 1280px; margin: 0 auto;">
    <!-- Interface de chat -->
    <div class="sk-card" style="display: flex; flex-direction: column; height: 600px;">
      <div style="padding: 20px; border-bottom: 1px solid var(--sk-border); background: rgba(91,108,255,.08);">
        <h3 style="margin: 0; font-size: 1rem;">Recherche IA</h3>
        <p style="margin: 4px 0 0 0; font-size: 0.8rem; color: var(--sk-muted);">Decrivez l opportunite recherchee</p>
      </div>
      
      <div id="chatMessages" style="flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 12px;">
        <div style="display: flex; justify-content: flex-start;">
          <div style="background: var(--sk-border); padding: 12px 16px; border-radius: 12px; max-width: 70%; color: var(--sk-muted); font-size: 0.875rem;">
            Bonjour ! Je peux vous aider a trouver des opportunites. Essayez par exemple :
            <ul style="margin: 8px 0 0 0; padding-left: 20px;">
              <li>"Trouve des emplois a distance"</li>
              <li>"Cherche des stages a Paris"</li>
              <li>"Montre-moi des missions freelance"</li>
            </ul>
          </div>
        </div>
      </div>
      
      <div style="padding: 16px; border-top: 1px solid var(--sk-border); display: flex; gap: 8px;">
        <input type="text" id="searchQuery" class="sk-input" placeholder="Posez votre question..." style="flex: 1;">
        <button class="sk-btn sk-btn-primary" onclick="sendRecherche()">
          <i class="bx bx-send"></i>
        </button>
      </div>
    </div>

    <!-- Resultats -->
    <div class="sk-card" style="height: 600px; overflow-y: auto; padding: 0;">
      <div style="padding: 16px; border-bottom: 1px solid var(--sk-border); position: sticky; top: 0; background: var(--sk-surface);">
        <h4 style="margin: 0; font-size: 0.9rem;">Resultats</h4>
        <p id="resultCount" style="margin: 4px 0 0 0; font-size: 0.75rem; color: var(--sk-muted);">0 resultat</p>
      </div>
      <div id="resultsList" style="padding: 12px;">
        <p style="color: var(--sk-muted); font-size: 0.85rem; text-align: center; padding: 20px 12px;">
          <i class="bx bx-search" style="font-size: 1.5rem; display: block; margin-bottom: 8px;"></i>
          Lancez une recherche pour voir les resultats
        </p>
      </div>
    </div>
  </div>
</div>

<script>
function sendRecherche() {
  const query = document.getElementById('searchQuery').value.trim();
  if (!query) return;
  
  // Ajouter le message utilisateur au chat
  const chatMessages = document.getElementById('chatMessages');
  const userMsg = document.createElement('div');
  userMsg.style.display = 'flex';
  userMsg.style.justifyContent = 'flex-end';
  userMsg.innerHTML = `
    <div style="background: var(--sk-accent); color: white; padding: 12px 16px; border-radius: 12px; max-width: 70%; font-size: 0.875rem;">
      ${escapeHtml(query)}
    </div>
  `;
  chatMessages.appendChild(userMsg);
  chatMessages.scrollTop = chatMessages.scrollHeight;
  
  // Afficher le chargement
  const loadingMsg = document.createElement('div');
  loadingMsg.style.display = 'flex';
  loadingMsg.style.justifyContent = 'flex-start';
  loadingMsg.innerHTML = `
    <div style="background: var(--sk-border); padding: 12px 16px; border-radius: 12px; color: var(--sk-muted); font-size: 0.875rem;">
      <i class="bx bx-loader bx-spin"></i> Recherche en cours...
    </div>
  `;
  loadingMsg.id = 'loading-msg';
  chatMessages.appendChild(loadingMsg);
  chatMessages.scrollTop = chatMessages.scrollHeight;
  
  // Lancer la recherche
  fetch('<?= $_SERVER['PHP_SELF'] ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'ai_search=1&query=' + encodeURIComponent(query)
  })
  .then(r => r.json())
  .then(data => {
    // Retirer le chargement
    const loading = document.getElementById('loading-msg');
    if (loading) loading.remove();
    
    // Ajouter la reponse IA
    const aiMsg = document.createElement('div');
    aiMsg.style.display = 'flex';
    aiMsg.style.justifyContent = 'flex-start';
    const sourceLabel = data.ai_used ? 'IA Gemini' : 'Recherche locale';
    aiMsg.innerHTML = `
      <div style="background: var(--sk-border); padding: 12px 16px; border-radius: 12px; max-width: 70%; color: var(--sk-muted); font-size: 0.875rem;">
        <div style="font-size:0.7rem;font-weight:700;color:var(--sk-accent);margin-bottom:4px;">${sourceLabel}</div>
        ${escapeHtml(data.message || 'Recherche terminee.')}
      </div>
    `;
    chatMessages.appendChild(aiMsg);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    // Afficher les resultats
    displayResultats(data.results || []);
    document.getElementById('resultCount').textContent = (data.count || 0) + ' resultat' + ((data.count || 0) !== 1 ? 's' : '');
    
    document.getElementById('searchQuery').value = '';
  })
  .catch(e => {
    console.error('Erreur :', e);
    alert('Erreur lors de la recherche');
  });
}

function displayResultats(results) {
  const resultsList = document.getElementById('resultsList');
  if (results.length === 0) {
    resultsList.innerHTML = '<p style="color: var(--sk-muted); font-size: 0.85rem; text-align: center; padding: 20px 12px;">Aucune opportunite trouvee</p>';
    return;
  }
  
  resultsList.innerHTML = results.map(opp => {
    const sc = opp.Statut === 'actif' ? 'actif' : (opp.Statut === 'archivÃ©' ? 'archive' : 'expire');
    const isFav = !!opp.is_favorited;
    const applyUrl = 'applications.php?opportunity_id=' + encodeURIComponent(opp.ID);
    return `
      <div style="padding: 12px; border: 1px solid var(--sk-border); border-radius: 8px; margin-bottom: 8px; font-size: 0.8rem;">
        <div style="font-weight: 600; margin-bottom: 4px;">${escapeHtml(opp.Titre)}</div>
        <div style="color: var(--sk-muted); margin-bottom: 6px; line-height: 1.3;">
          ${escapeHtml((opp.Description || '').substring(0, 80))}${(opp.Description || '').length > 80 ? '...' : ''}
        </div>
        ${opp.ai_reason ? `<div style="color: var(--sk-accent); margin-bottom: 8px; line-height: 1.3;"><i class="bx bx-sparkles"></i> ${escapeHtml(opp.ai_reason)}</div>` : ''}
        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
          <span class="sk-badge sk-badge-${escapeHtml(opp.Type_job)}">${escapeHtml(opp.Type_job)}</span>
          <span class="sk-badge sk-badge-${sc}">${escapeHtml(opp.Statut)}</span>
        </div>
        <div style="display:flex;gap:8px;align-items:center;margin-top:10px;flex-wrap:wrap;">
          <a class="sk-btn sk-btn-primary sk-btn-sm" href="${applyUrl}">
            <i class="bx bx-send"></i> Postuler
          </a>
          <button class="sk-btn sk-btn-sm fav-btn ${isFav ? 'favorited' : ''}"
                  onclick="toggleFavorite(${parseInt(opp.ID)}, this)"
                  title="${isFav ? 'Retirer des favoris' : 'Ajouter aux favoris'}"
                  style="background: none; border: none; padding: 4px 8px; cursor: pointer; font-size: 1.2em;">
            <i class="bx ${isFav ? 'bxs-heart' : 'bx-heart'}" style="color: ${isFav ? '#ff4d6d' : '#ccc'}; display: inline-block;"></i>
          </button>
        </div>
      </div>
    `;
  }).join('');
}

function escapeHtml(unsafe) {
  return unsafe
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function toggleFavorite(opportunityId, btn) {
  fetch('<?= $_SERVER['PHP_SELF'] ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'favorite_action=1&opportunity_id=' + encodeURIComponent(opportunityId)
  })
  .then(r => r.json())
  .then(data => {
    if (!data.success) {
      alert(data.message || 'Erreur lors de la mise a jour du favori');
      return;
    }

    const icon = btn.querySelector('i');
    if (data.favorited) {
      btn.classList.add('favorited');
      icon.style.color = '#ff4d6d';
      icon.classList.remove('bx-heart');
      icon.classList.add('bxs-heart');
      btn.title = 'Retirer des favoris';
    } else {
      btn.classList.remove('favorited');
      icon.style.color = '#ccc';
      icon.classList.remove('bxs-heart');
      icon.classList.add('bx-heart');
      btn.title = 'Ajouter aux favoris';
    }
  })
  .catch(e => console.error('Erreur :', e));
}

// Envoyer avec la touche Entree
document.getElementById('searchQuery').addEventListener('keypress', function(e) {
  if (e.key === 'Enter') {
    sendRecherche();
  }
});

// Focus automatique
document.getElementById('searchQuery').focus();
</script>

<style>
.sk-badge-jobs      { background:rgba(91,108,255,.15); color:#7c8fff;    border:1px solid rgba(91,108,255,.25); }
.sk-badge-freelance { background:rgba(245,166,35,.15); color:#f5a623;    border:1px solid rgba(245,166,35,.25); }
.sk-badge-stage     { background:rgba(34,211,165,.15); color:#22d3a5;    border:1px solid rgba(34,211,165,.25); }
.sk-badge-actif     { background:rgba(34,211,165,.15); color:#22d3a5;    border:1px solid rgba(34,211,165,.25); }
.sk-badge-archive   { background:rgba(122,128,153,.15);color:var(--sk-muted); border:1px solid rgba(122,128,153,.25); }
.sk-badge-expire    { background:rgba(255,77,109,.15);  color:#ff4d6d;   border:1px solid rgba(255,77,109,.25); }
.fav-btn { transition: transform .2s ease; }
.fav-btn:hover { transform: scale(1.15); }
</style>
</body>
</html>


