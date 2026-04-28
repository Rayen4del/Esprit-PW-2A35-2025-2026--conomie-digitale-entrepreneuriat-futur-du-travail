<?php
include_once __DIR__ . '/../../../../config.php';
include_once __DIR__ . '/../../../../controller/evenement/EvenementController.php';

$controller = new EvenementController();
$stats = $controller->getStatistics();

$typeLabels = [
    'workshop'   => 'Workshop',
    'conference' => 'Conférence',
    'seminaire'  => 'Séminaire',
    'hackathon'  => 'Hackathon',
    'formation'  => 'Formation',
    'webinar'    => 'Webinaire',
    'autre'      => 'Autre'
];
?>
<!DOCTYPE html>
<html lang="fr" class="light-style layout-menu-fixed" dir="ltr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
  <title>Statistiques - Backoffice</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <style>
    :root{--bs-primary:#696cff;--menu-bg:#fff;--menu-text:#566a7f;--menu-active-bg:#f1f1ff;
      --menu-active-text:#696cff;--body-bg:#f5f5f9;--navbar-bg:#fff;
      --sidebar-width:260px;--header-height:64px;--success:#71dd37;--danger:#ff3e1d;
      --warning:#ffab00;--info:#03c3ec}
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Public Sans',sans-serif;font-size:.9375rem;background:var(--body-bg);color:#566a7f}
    .layout-wrapper{display:flex;min-height:100vh}
    .layout-container{display:flex;flex:1}
    .layout-menu{width:var(--sidebar-width);background:var(--menu-bg);flex-shrink:0;
      position:fixed;top:0;left:0;bottom:0;z-index:1100;overflow-y:auto;
      box-shadow:0 0 0 1px rgba(67,89,113,.05),0 2px 6px rgba(67,89,113,.12)}
    .app-brand{display:flex;align-items:center;padding:1.5rem 1.5rem .5rem;min-height:var(--header-height)}
    .app-brand-logo{width:32px;height:32px;display:flex;align-items:center;justify-content:center}
    .app-brand-text{font-size:1.25rem;font-weight:700;color:#566a7f;margin-left:.6rem}
    .menu-inner{list-style:none;padding:.5rem 0 1rem}
    .menu-header{padding:.75rem 1.5rem .25rem;font-size:.6875rem;font-weight:600;color:#a1acb8;
      letter-spacing:.8px;text-transform:uppercase}
    .menu-item{position:relative}
    .menu-link{display:flex;align-items:center;padding:.625rem 1.5rem;color:var(--menu-text);
      text-decoration:none;border-radius:.375rem;margin:.1rem .75rem;
      transition:background .15s,color .15s;font-size:.9rem;cursor:pointer}
    .menu-link:hover{background:var(--menu-active-bg);color:var(--menu-active-text)}
    .menu-link.active{background:var(--menu-active-bg);color:var(--menu-active-text);font-weight:600}
    .menu-icon{font-size:1.1rem;margin-right:.75rem;opacity:.85}
    .menu-sub{list-style:none;padding:0;display:none}
    .menu-item.open>.menu-sub{display:block}
    .menu-sub .menu-link{padding-left:3rem;font-size:.875rem}
    .menu-toggle::after{content:'\F285';font-family:'bootstrap-icons';margin-left:auto;
      font-size:.8rem;transition:transform .2s}
    .menu-item.open>.menu-toggle::after{transform:rotate(90deg)}
    .layout-page{margin-left:var(--sidebar-width);display:flex;flex-direction:column;flex:1;min-width:0}
    .layout-navbar{background:var(--navbar-bg);height:var(--header-height);display:flex;
      align-items:center;padding:0 1.5rem;box-shadow:0 1px 0 rgba(67,89,113,.1);
      position:sticky;top:0;z-index:1000;gap:1rem}
    .navbar-search{flex:1;max-width:300px}
    .navbar-search .input-group{background:var(--body-bg);border-radius:.375rem}
    .navbar-search input{background:transparent;border:none;font-size:.875rem;color:#566a7f}
    .navbar-search input:focus{box-shadow:none;outline:none}
    .navbar-search .input-group-text{background:transparent;border:none;color:#a1acb8}
    .navbar-nav-right{display:flex;align-items:center;gap:.25rem;margin-left:auto}
    .nav-icon-btn{background:none;border:none;color:#566a7f;font-size:1.2rem;padding:.5rem;
      border-radius:.375rem;cursor:pointer;transition:background .15s,color .15s}
    .nav-icon-btn:hover{background:var(--body-bg);color:var(--bs-primary)}
    .user-avatar{width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#696cff,#a3a4ff);
      display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;
      font-size:.9rem;cursor:pointer;margin-left:.5rem;position:relative}
    .content-wrapper{flex:1;padding:2rem}
    .page-title{font-size:1.5rem;font-weight:700;color:#566a7f;margin-bottom:.5rem}
    .page-subtitle{font-size:.95rem;color:#a1acb8;margin-bottom:2rem}
    .stat-card{background:#fff;border:none;border-radius:.75rem;
      box-shadow:0 2px 8px rgba(67,89,113,.1);padding:1.75rem;margin-bottom:1.5rem;
      transition:transform .2s,box-shadow .2s;position:relative;overflow:hidden}
    .stat-card:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,89,113,.15)}
    .stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;
      background:linear-gradient(90deg,#696cff,#a3a4ff)}
    .stat-card.success::before{background:linear-gradient(90deg,#71dd37,#a3ff37)}
    .stat-card.danger::before{background:linear-gradient(90deg,#ff3e1d,#ff6b54)}
    .stat-card.warning::before{background:linear-gradient(90deg,#ffab00,#ffc107)}
    .stat-card.info::before{background:linear-gradient(90deg,#03c3ec,#00d4ff)}
    .stat-label{font-size:.9rem;color:#a1acb8;margin-bottom:.5rem;display:flex;align-items:center;gap:.5rem}
    .stat-icon{width:40px;height:40px;border-radius:.5rem;display:flex;align-items:center;
      justify-content:center;font-size:1.25rem;margin-right:.75rem}
    .stat-card.success .stat-icon{background:rgba(113,221,55,.1);color:#71dd37}
    .stat-card.danger .stat-icon{background:rgba(255,62,29,.1);color:#ff3e1d}
    .stat-card.warning .stat-icon{background:rgba(255,171,0,.1);color:#ffab00}
    .stat-card.info .stat-icon{background:rgba(3,195,236,.1);color:#03c3ec}
    .stat-value{font-size:2.5rem;font-weight:700;color:#566a7f}
    .stat-change{font-size:.875rem;color:#a1acb8;margin-top:.5rem}
    .stat-change.positive{color:#71dd37}
    .stat-header{display:flex;align-items:center;gap:.75rem}
    .chart-container{background:#fff;border-radius:.75rem;
      box-shadow:0 2px 8px rgba(67,89,113,.1);padding:1.75rem;margin-bottom:2rem;
      position:relative;height:400px}
    .chart-title{font-size:1.1rem;font-weight:600;color:#566a7f;margin-bottom:1.5rem;
      display:flex;align-items:center;gap:.5rem}
    .chart-icon{width:32px;height:32px;border-radius:.5rem;
      background:linear-gradient(135deg,#696cff22,#a3a4ff44);
      display:flex;align-items:center;justify-content:center;color:#696cff;font-size:1rem}
    .event-row{display:flex;align-items:center;justify-content:space-between;
      padding:1rem;border-bottom:1px solid rgba(67,89,113,.05);
      transition:background .2s}
    .event-row:hover{background:rgba(67,89,113,.02)}
    .event-row:last-child{border-bottom:none}
    .event-info{flex:1;display:flex;flex-direction:column;gap:.25rem}
    .event-title{font-weight:600;color:#566a7f;font-size:.95rem}
    .event-type{font-size:.8rem;color:#a1acb8}
    .badge-event{display:inline-flex;align-items:center;gap:.5rem;
      background:rgba(105,108,255,.1);color:#696cff;
      padding:.375rem .75rem;border-radius:.5rem;font-size:.85rem;font-weight:600}
    .progress-bar-custom{height:8px;border-radius:4px;background:rgba(67,89,113,.1);
      overflow:hidden;margin-top:.5rem}
    .progress-fill{height:100%;border-radius:4px;background:linear-gradient(90deg,#696cff,#a3a4ff);
      transition:width .3s}
    .empty-state{text-align:center;padding:3rem 1rem;color:#a1acb8}
    .empty-icon{font-size:3rem;margin-bottom:1rem;opacity:.5}
    .content-footer{padding:1rem 1.5rem;display:flex;justify-content:space-between;align-items:center;
      font-size:.8125rem;color:#a1acb8;border-top:1px solid rgba(67,89,113,.08);margin-top:auto}
  </style>
</head>
<body>
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">

    <!-- SIDEBAR -->
    <aside class="layout-menu">
      <div class="app-brand">
        <div class="app-brand-logo">
          <svg viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12.5 2L22 7V18L12.5 23L3 18V7L12.5 2Z" fill="#696cff" fill-opacity=".15" stroke="#696cff" stroke-width="1.5"/>
            <path d="M12.5 7L17 9.5V14.5L12.5 17L8 14.5V9.5L12.5 7Z" fill="#696cff"/>
          </svg>
        </div>
        <span class="app-brand-text">EventHub</span>
      </div>
      <ul class="menu-inner">
        <li class="menu-header">ADMINISTRATION</li>
        <li class="menu-item open">
          <a href="#" class="menu-link menu-toggle active">
            <i class="bi bi-shield-lock menu-icon"></i> Backoffice
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="/projet/view/evenement/html/backoffice/backoffice_evenements.php" class="menu-link">
                <i class="bi bi-grid-3x3 menu-icon"></i> Événements
              </a>
            </li>
            <li class="menu-item">
              <a href="/projet/view/evenement/html/backoffice/statistics_dashboard.php" class="menu-link active">
                <i class="bi bi-bar-chart menu-icon"></i> Statistiques
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </aside>

    <div class="layout-page">

      <!-- NAVBAR -->
      <nav class="layout-navbar">
        <div style="font-weight:600;color:#566a7f">Tableau de bord</div>
        <div class="navbar-nav-right">
          <button class="nav-icon-btn" title="Rafraîchir">
            <i class="bi bi-arrow-clockwise"></i>
          </button>
          <button class="nav-icon-btn" title="Paramètres">
            <i class="bi bi-gear"></i>
          </button>
          <div class="user-avatar">
            <span>A</span>
            <div class="online-dot"></div>
          </div>
        </div>
      </nav>

      <!-- CONTENT -->
      <div class="content-wrapper">
        <div class="page-title">
          <i class="bi bi-bar-chart-line me-2" style="color:#696cff"></i>Tableau de Bord Statistiques
        </div>
        <div class="page-subtitle">Bienvenue dans votre centre de contrôle • Mise à jour en temps réel</div>

        <!-- KPI Cards Row 1 -->
        <div class="row g-3 mb-4">
          <div class="col-md-6 col-lg-3">
            <div class="stat-card">
              <div class="stat-header">
                <div class="stat-icon"><i class="bi bi-calendar-event"></i></div>
                <div>
                  <div class="stat-label">Événements Total</div>
                </div>
              </div>
              <div class="stat-value"><?= $stats['totalEvents'] ?></div>
            </div>
          </div>

          <div class="col-md-6 col-lg-3">
            <div class="stat-card success">
              <div class="stat-header">
                <div class="stat-icon"><i class="bi bi-people"></i></div>
                <div>
                  <div class="stat-label">Inscriptions Total</div>
                </div>
              </div>
              <div class="stat-value"><?= $stats['totalRegistrations'] ?></div>
            </div>
          </div>

          <div class="col-md-6 col-lg-3">
            <div class="stat-card info">
              <div class="stat-header">
                <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
                <div>
                  <div class="stat-label">Événements à Venir</div>
                </div>
              </div>
              <div class="stat-value"><?= $stats['upcomingEvents'] ?></div>
            </div>
          </div>

          <div class="col-md-6 col-lg-3">
            <div class="stat-card warning">
              <div class="stat-header">
                <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
                <div>
                  <div class="stat-label">Événements Passés</div>
                </div>
              </div>
              <div class="stat-value"><?= $stats['pastEvents'] ?></div>
            </div>
          </div>
        </div>

        <!-- KPI Cards Row 2 -->
        <div class="row g-3 mb-4">
          <div class="col-md-6 col-lg-3">
            <div class="stat-card danger">
              <div class="stat-header">
                <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                <div>
                  <div class="stat-label">Inscrits Confirmés</div>
                </div>
              </div>
              <div class="stat-value"><?= $stats['registrationsByStatus']['confirmé'] ?? 0 ?></div>
            </div>
          </div>

          <div class="col-md-6 col-lg-3">
            <div class="stat-card success">
              <div class="stat-header">
                <div class="stat-icon"><i class="bi bi-person-check"></i></div>
                <div>
                  <div class="stat-label">Inscrits</div>
                </div>
              </div>
              <div class="stat-value"><?= $stats['registrationsByStatus']['inscrit'] ?? 0 ?></div>
            </div>
          </div>

          <div class="col-md-6 col-lg-3">
            <div class="stat-card">
              <div class="stat-header">
                <div class="stat-icon"><i class="bi bi-person-x"></i></div>
                <div>
                  <div class="stat-label">Annulés</div>
                </div>
              </div>
              <div class="stat-value"><?= $stats['registrationsByStatus']['annulé'] ?? 0 ?></div>
            </div>
          </div>

          <div class="col-md-6 col-lg-3">
            <div class="stat-card info">
              <div class="stat-header">
                <div class="stat-icon"><i class="bi bi-percent"></i></div>
                <div>
                  <div class="stat-label">Moy. par Événement</div>
                </div>
              </div>
              <div class="stat-value"><?= $stats['avgRegistrationsPerEvent'] ?></div>
            </div>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-3 mb-4">
          <div class="col-lg-6">
            <div class="chart-container">
              <div class="chart-title">
                <div class="chart-icon"><i class="bi bi-pie-chart"></i></div>
                Événements par Statut
              </div>
              <canvas id="statusChart"></canvas>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="chart-container">
              <div class="chart-title">
                <div class="chart-icon"><i class="bi bi-bar-chart"></i></div>
                Événements par Type
              </div>
              <canvas id="typeChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Registrations Status Chart -->
        <div class="row g-3 mb-4">
          <div class="col-lg-6">
            <div class="chart-container">
              <div class="chart-title">
                <div class="chart-icon"><i class="bi bi-graph-up"></i></div>
                Inscriptions par Statut
              </div>
              <canvas id="regStatusChart"></canvas>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="chart-container">
              <div class="chart-title">
                <div class="chart-icon"><i class="bi bi-calendar-range"></i></div>
                Répartition Temporelle
              </div>
              <canvas id="timeChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Top Events Table -->
        <div class="chart-container">
          <div class="chart-title">
            <div class="chart-icon"><i class="bi bi-trophy"></i></div>
            Top 5 Événements les Plus Populaires
          </div>
          <?php if (!empty($stats['popularEvents'])): ?>
            <?php foreach ($stats['popularEvents'] as $idx => $event): 
              $reg_count = intval($event['reg_count'] ?? 0);
              $maxReg = 50; // For visualization
              $percentage = min(100, ($reg_count / $maxReg) * 100);
              $typeLabel = $typeLabels[$event['Type']] ?? ucfirst($event['Type']);
              $date = date('d/m/Y', strtotime($event['dateEvent']));
            ?>
            <div class="event-row">
              <div style="flex:0 0 auto;margin-right:1rem">
                <div style="width:40px;height:40px;border-radius:50%;
                  background:linear-gradient(135deg,#696cff,#a3a4ff);
                  display:flex;align-items:center;justify-content:center;
                  color:#fff;font-weight:700;font-size:1rem">
                  <?= $idx + 1 ?>
                </div>
              </div>
              <div class="event-info">
                <div class="event-title"><?= htmlspecialchars($event['Titre']) ?></div>
                <div class="event-type">
                  <span class="badge-event"><?= htmlspecialchars($typeLabel) ?></span>
                  <span style="color:#a1acb8;font-size:.8rem">• <?= $date ?></span>
                </div>
              </div>
              <div style="flex:0 0 auto;text-align:right">
                <div style="font-size:1.5rem;font-weight:700;color:#696cff"><?= $reg_count ?></div>
                <div style="font-size:.8rem;color:#a1acb8">inscriptions</div>
              </div>
            </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-state">
              <div class="empty-icon"><i class="bi bi-inbox"></i></div>
              <p>Aucun événement enregistré</p>
            </div>
          <?php endif; ?>
        </div>

      </div><!-- /content-wrapper -->

      <footer class="content-footer">
        <div>© <span id="year"></span> EventHub — Plateforme d'événements</div>
        <div>
          <a href="#" style="color:#a1acb8;text-decoration:none;margin-left:1rem">Aide</a>
          <a href="#" style="color:#a1acb8;text-decoration:none;margin-left:1rem">Support</a>
        </div>
      </footer>

    </div><!-- /layout-page -->

  </div><!-- /layout-container -->
</div><!-- /layout-wrapper -->

<script>
  document.getElementById('year').textContent = new Date().getFullYear();

  // Status Chart (Pie)
  const statusCtx = document.getElementById('statusChart');
  if (statusCtx) {
    new Chart(statusCtx, {
      type: 'doughnut',
      data: {
        labels: ['Ouvert', 'Fermé', 'Complet'],
        datasets: [{
          data: [<?= $stats['eventsByStatus']['ouvert'] ?>, <?= $stats['eventsByStatus']['ferme'] ?>, <?= $stats['eventsByStatus']['complet'] ?>],
          backgroundColor: ['#71dd37', '#ff3e1d', '#ffab00'],
          borderColor: '#fff',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              padding: 15,
              font: { size: 13 },
              color: '#a1acb8'
            }
          }
        }
      }
    });
  }

  // Type Chart (Bar)
  const typeCtx = document.getElementById('typeChart');
  if (typeCtx) {
    const typeLabelsMap = <?= json_encode($typeLabels) ?>;
    const types = <?= json_encode(array_keys($stats['eventsByType'])) ?>;
    const typeCounts = <?= json_encode(array_values($stats['eventsByType'])) ?>;
    const typeNames = types.map(t => typeLabelsMap[t] || t);
    
    new Chart(typeCtx, {
      type: 'bar',
      data: {
        labels: typeNames,
        datasets: [{
          label: 'Nombre d\'événements',
          data: typeCounts,
          backgroundColor: '#696cff',
          borderColor: '#696cff',
          borderWidth: 1,
          borderRadius: 8
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          x: {
            grid: { color: 'rgba(67,89,113,.05)' }
          },
          y: {
            grid: { display: false }
          }
        }
      }
    });
  }

  // Registration Status Chart (Doughnut)
  const regStatusCtx = document.getElementById('regStatusChart');
  if (regStatusCtx) {
    new Chart(regStatusCtx, {
      type: 'doughnut',
      data: {
        labels: ['Confirmé', 'Inscrit', 'Annulé'],
        datasets: [{
          data: [<?= $stats['registrationsByStatus']['confirmé'] ?? 0 ?>, <?= $stats['registrationsByStatus']['inscrit'] ?? 0 ?>, <?= $stats['registrationsByStatus']['annulé'] ?? 0 ?>],
          backgroundColor: ['#03c3ec', '#696cff', '#ffab00'],
          borderColor: '#fff',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              padding: 15,
              font: { size: 13 },
              color: '#a1acb8'
            }
          }
        }
      }
    });
  }

  // Time Chart (Upcoming vs Past)
  const timeCtx = document.getElementById('timeChart');
  if (timeCtx) {
    new Chart(timeCtx, {
      type: 'bar',
      data: {
        labels: ['À venir', 'Passés'],
        datasets: [{
          label: 'Événements',
          data: [<?= $stats['upcomingEvents'] ?>, <?= $stats['pastEvents'] ?>],
          backgroundColor: ['#71dd37', '#a1acb8'],
          borderColor: ['#71dd37', '#a1acb8'],
          borderWidth: 1,
          borderRadius: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: { color: 'rgba(67,89,113,.05)' }
          },
          x: {
            grid: { display: false }
          }
        }
      }
    });
  }
</script>

</body>
</html>
