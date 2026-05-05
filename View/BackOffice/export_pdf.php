<?php
include '../../Controller/produitController.php';
include '../../Controller/sponsoringController.php';


$type = $_GET['type'] ?? 'statistics';
$produitC = new ProduitController();
$sponsoringC = new SponsoringController();

$produits = [];
$sponsors = [];
$statistics = [];

// Get data based on type
try {
    if ($type === 'products') {
        $produits_stmt = $produitC->listProduits();
        if ($produits_stmt) {
            $produits = $produits_stmt->fetchAll();
        } else {
            $produits = [];
        }
    } elseif ($type === 'sponsors') {
        $sponsors_stmt = $sponsoringC->listSponsoring();
        if ($sponsors_stmt) {
            $sponsors = $sponsors_stmt->fetchAll();
        } else {
            $sponsors = [];
        }
    } elseif ($type === 'statistics') {
        $produits_stmt = $produitC->listProduits();
        $sponsors_stmt = $sponsoringC->listSponsoring();
        
        $produits = $produits_stmt ? $produits_stmt->fetchAll() : [];
        $sponsors = $sponsors_stmt ? $sponsors_stmt->fetchAll() : [];
        
        // Calculate statistics
        $statistics = [
            'total_products' => count($produits),
            'total_sponsors' => count($sponsors),
            'total_value' => array_sum(array_column($produits, 'prix')),
            'avg_product_price' => count($produits) > 0 ? array_sum(array_column($produits, 'prix')) / count($produits) : 0,
            'product_categories' => array_count_values(array_column($produits, 'categrie')),
            'active_sponsors' => 0,
            'expired_sponsors' => 0
        ];
        
        // Count active and expired sponsors
        $current_date = date('Y-m-d');
        foreach ($sponsors as $sponsor) {
            if ($sponsor['date_fin'] >= $current_date) {
                $statistics['active_sponsors']++;
            } else {
                $statistics['expired_sponsors']++;
            }
        }
    }
} catch (Exception $e) {
    // Handle any errors
    $produits = [];
    $sponsors = [];
    $statistics = [];
    // You could log the error here if needed
}

// Generate HTML content
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Export PDF - <?php echo $type === 'statistics' ? 'Statistiques' : ($type === 'products' ? 'Liste des Produits' : 'Liste des Sponsors'); ?></title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            line-height: 1.6;
        }
        h1 { 
            color: #333; 
            text-align: center; 
            margin-bottom: 10px;
        }
        h2 { 
            color: #666; 
            border-bottom: 2px solid #ddd; 
            padding-bottom: 5px; 
            margin-top: 30px;
            margin-bottom: 15px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: left; 
            vertical-align: top;
        }
        th { 
            background-color: #f5f5f5; 
            font-weight: bold; 
        }
        .stat-card { 
            display: inline-block; 
            margin: 10px; 
            padding: 20px; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            text-align: center;
            min-width: 150px;
        }
        .stat-value { 
            font-size: 28px; 
            font-weight: bold; 
            color: #6366f1; 
            margin-bottom: 5px;
        }
        .stat-label { 
            font-size: 14px; 
            color: #666; 
        }
        .header-info { 
            text-align: center; 
            margin-bottom: 30px; 
        }
        .footer { 
            margin-top: 40px; 
            text-align: center; 
            font-size: 12px; 
            color: #666; 
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body>
    <div class="header-info">
        <h1>Skiller - <?php echo $type === 'statistics' ? 'Statistiques' : ($type === 'products' ? 'Liste des Produits' : 'Liste des Sponsors'); ?></h1>
        <p>Généré le: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>
    
    <?php if ($type === 'statistics'): ?>
        <h2>Statistiques Générales</h2>
        <div style="text-align: center; margin: 20px 0;">
            <div class="stat-card">
                <div class="stat-value"><?php echo $statistics['total_products']; ?></div>
                <div class="stat-label">Total Produits</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($statistics['total_value'], 2); ?> TND</div>
                <div class="stat-label">Valeur Totale</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $statistics['total_sponsors']; ?></div>
                <div class="stat-label">Total Sponsors</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($statistics['avg_product_price'], 2); ?> TND</div>
                <div class="stat-label">Prix Moyen</div>
            </div>
        </div>
        
        <h2>Statut des Sponsors</h2>
        <table>
            <tr><th>Type</th><th>Nombre</th><th>Pourcentage</th></tr>
            <tr>
                <td>Sponsors Actifs</td>
                <td><?php echo $statistics['active_sponsors']; ?></td>
                <td><?php echo $statistics['total_sponsors'] > 0 ? round(($statistics['active_sponsors'] / $statistics['total_sponsors']) * 100, 1) : 0; ?>%</td>
            </tr>
            <tr>
                <td>Sponsors Expirés</td>
                <td><?php echo $statistics['expired_sponsors']; ?></td>
                <td><?php echo $statistics['total_sponsors'] > 0 ? round(($statistics['expired_sponsors'] / $statistics['total_sponsors']) * 100, 1) : 0; ?>%</td>
            </tr>
        </table>
        
        <h2>Catégories de Produits</h2>
        <?php if (!empty($statistics['product_categories'])): ?>
            <table>
                <tr><th>Catégorie</th><th>Nombre de Produits</th><th>Pourcentage</th></tr>
                <?php foreach ($statistics['product_categories'] as $category => $count): ?>
                <tr>
                    <td><?php echo htmlspecialchars($category); ?></td>
                    <td><?php echo $count; ?></td>
                    <td><?php echo $statistics['total_products'] > 0 ? round(($count / $statistics['total_products']) * 100, 1) : 0; ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <div class="no-data">Aucun produit disponible</div>
        <?php endif; ?>
        
    <?php elseif ($type === 'products'): ?>
        <h2>Liste des Produits</h2>
        <p style="color: #666; font-size: 12px;">Nombre de produits trouvés: <?php echo count($produits); ?></p>
        <?php if (!empty($produits)): ?>
            <table>
                <tr>
                    <th>ID</th><th>Nom</th><th>Catégorie</th><th>Prix</th><th>Description</th><th>ID Sponsor</th>
                </tr>
                <?php foreach ($produits as $produit): ?>
                <tr>
                    <td><?php echo $produit['id_p']; ?></td>
                    <td><?php echo htmlspecialchars($produit['nom']); ?></td>
                    <td><?php echo htmlspecialchars($produit['categrie']); ?></td>
                    <td><?php echo $produit['prix']; ?> TND</td>
                    <td><?php echo htmlspecialchars($produit['description']); ?></td>
                    <td><?php echo $produit['id_sp']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <div class="no-data">Aucun produit disponible</div>
        <?php endif; ?>
        
    <?php elseif ($type === 'sponsors'): ?>
        <h2>Liste des Sponsors</h2>
        <p style="color: #666; font-size: 12px;">Nombre de sponsors trouvés: <?php echo count($sponsors); ?></p>
        <?php if (!empty($sponsors)): ?>
            <table>
                <tr>
                    <th>ID</th><th>ID User</th><th>Nom Entreprise</th><th>Date Début</th><th>Date Fin</th><th>Email</th>
                </tr>
                <?php foreach ($sponsors as $sponsor): ?>
                <tr>
                    <td><?php echo $sponsor['id_sp']; ?></td>
                    <td><?php echo $sponsor['id_u']; ?></td>
                    <td><?php echo htmlspecialchars($sponsor['nom_ent']); ?></td>
                    <td><?php echo $sponsor['date_deb']; ?></td>
                    <td><?php echo $sponsor['date_fin']; ?></td>
                    <td><?php echo htmlspecialchars($sponsor['mail_event']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <div class="no-data">Aucun sponsor disponible</div>
        <?php endif; ?>
    <?php endif; ?>
    
    <div class="footer">
        <p>© 2024 Skiller - Tous droits réservés</p>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

// Set headers for HTML display
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Serve as HTML file with auto-print
echo $html;
?>
