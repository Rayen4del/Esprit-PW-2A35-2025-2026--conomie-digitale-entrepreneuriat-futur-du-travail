<?php

require_once __DIR__ .  '/../../../Service/ScraperService.php';
require_once __DIR__ . '/../../../Controller/AIController.php';

$scraper = new ScraperService();
$ai = new AiController();

$result = null;

if ($_POST) {

    // 1. SCRAPING
    $url = $_POST['url'];
    $scrapedData = $scraper->scrape($url);

    // 2. TRANSFORMER DATA → TEXTE
    $content = "";

    foreach ($scrapedData as $item) {
        if ($item['type'] == 'h1' || $item['type'] == 'h2') {
            $content .= "\n\n" . $item['content'] . "\n";
        }

        if ($item['type'] == 'p') {
            $content .= $item['content'] . "\n";
        }
    }

    // 3. IA avec contenu du site
    $result = $ai->generate(
        $_POST['titre'],
        $_POST['description'] . "\n\n CONTENU SOURCE:\n" . $content
    );
}
?>

<h2>Test IA + Scraping</h2>

<form method="POST">
    <input type="text" name="titre" placeholder="Titre formation" required><br><br>
    <textarea name="description" placeholder="Description" required></textarea><br><br>
    <input type="text" name="url" placeholder="URL (Wikipedia)" required><br><br>

    <button type="submit">Générer IA</button>
</form>

<hr>

<?php if ($result): ?>
    <pre><?php print_r($result); ?></pre>
<?php endif; ?>