<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$request = json_decode(file_get_contents('php://input'), true);
if (!is_array($request)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON payload']);
    exit;
}

$languageMap = [
    'fr' => ['libre' => 'fr', 'google' => 'fr', 'mymemory' => 'fr'],
    'en' => ['libre' => 'en', 'google' => 'en', 'mymemory' => 'en'],
    'ar' => ['libre' => 'ar', 'google' => 'ar', 'mymemory' => 'ar'],
    'zh' => ['libre' => 'zh-Hans', 'google' => 'zh-CN', 'mymemory' => 'zh-CN']
];

$target = trim((string)($request['target'] ?? ''));
$texts = $request['texts'] ?? [];
$dynamicTranslation = !empty($request['dynamic']);

if (!isset($languageMap[$target])) {
    http_response_code(400);
    echo json_encode(['error' => 'Unsupported target language']);
    exit;
}

if (!is_array($texts) || count($texts) === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'No text to translate']);
    exit;
}

function normalizeText($text): string {
    return trim(html_entity_decode((string)$text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

function getPreferredUiTranslation(string $text, string $target): ?string {
    $trimmed = normalizeText($text);
    $requiredSuffix = '';
    $colonSuffix = '';

    if (str_ends_with($trimmed, '*')) {
        $requiredSuffix = ' *';
        $trimmed = trim(substr($trimmed, 0, -1));
    }

    if (str_ends_with($trimmed, ':')) {
        $colonSuffix = ':';
        $trimmed = trim(substr($trimmed, 0, -1));
    }

    $key = mb_strtolower($trimmed, 'UTF-8');
    $preferred = [
        'en' => [
            'modifier' => 'Edit',
            'supprimer' => 'Delete',
            'dates' => 'Dates',
            'trier par' => 'Sort by',
            'statut' => 'Status',
            'statut:' => 'Status:',
            'description' => 'Description',
            'description:' => 'Description:',
            'nom' => 'Name',
            'recherche' => 'Search',
            'rechercher...' => 'Search...',
            'rechercher un nom' => 'Search by name',
            'rechercher un email' => 'Search by email',
            'sélectionner une date' => 'Select a date',
            'acheter' => 'Purchase',
            'acheté' => 'Purchased',
            'achete' => 'Purchased',
            'non acheter' => 'Not purchased',
            'prix' => 'Price',
            'date fin' => 'End Date',
            'date début' => 'Start Date',
            'date debut' => 'Start Date',
            'date début asc' => 'Start date asc',
            'date debut asc' => 'Start date asc',
            'date début desc' => 'Start date desc',
            'date debut desc' => 'Start date desc',
            'date fin asc' => 'End date asc',
            'date fin desc' => 'End date desc',
            'id sponsor asc' => 'Sponsor ID asc',
            'id sponsor desc' => 'Sponsor ID desc',
            'id produit asc' => 'Product ID asc',
            'id produit desc' => 'Product ID desc',
            'prix asc' => 'Price asc',
            'prix desc' => 'Price desc',
            'actions' => 'Actions'
        ],
        'ar' => [
            'modifier' => 'تعديل',
            'supprimer' => 'حذف',
            'dates' => 'التواريخ',
            'trier par' => 'ترتيب حسب',
            'statut' => 'الحالة',
            'statut:' => 'الحالة:',
            'description' => 'الوصف',
            'description:' => 'الوصف:',
            'recherche' => 'بحث',
            'rechercher...' => 'بحث...',
            'rechercher un nom' => 'البحث بالاسم',
            'rechercher un email' => 'البحث بالبريد الإلكتروني',
            'sélectionner une date' => 'اختر تاريخا',
            'acheter' => 'شراء',
            'acheté' => 'تم الشراء',
            'achete' => 'تم الشراء',
            'non acheter' => 'لم يتم الشراء',
            'prix' => 'السعر',
            'date fin' => 'تاريخ الانتهاء',
            'date début' => 'تاريخ البداية',
            'date debut' => 'تاريخ البداية',
            'date début asc' => 'تاريخ البداية من الأقدم إلى الأحدث',
            'date debut asc' => 'تاريخ البداية من الأقدم إلى الأحدث',
            'date début desc' => 'تاريخ البداية من الأحدث إلى الأقدم',
            'date debut desc' => 'تاريخ البداية من الأحدث إلى الأقدم',
            'date fin asc' => 'تاريخ الانتهاء من الأقدم إلى الأحدث',
            'date fin desc' => 'تاريخ الانتهاء من الأحدث إلى الأقدم',
            'id sponsor asc' => 'معرّف الراعي تصاعديا',
            'id sponsor desc' => 'معرّف الراعي تنازليا',
            'id produit asc' => 'معرّف المنتج تصاعديا',
            'id produit desc' => 'معرّف المنتج تنازليا',
            'prix asc' => 'السعر من الأقل إلى الأعلى',
            'prix desc' => 'السعر من الأعلى إلى الأقل',
            'actions' => 'الإجراءات'
        ],
        'zh' => [
            'modifier' => '编辑',
            'supprimer' => '删除',
            'dates' => '日期',
            'trier par' => '排序方式',
            'statut' => '状态',
            'statut:' => '状态:',
            'description' => '描述',
            'description:' => '描述:',
            'recherche' => '搜索',
            'rechercher...' => '搜索...',
            'rechercher un nom' => '按名称搜索',
            'rechercher un email' => '按电子邮件搜索',
            'sélectionner une date' => '选择日期',
            'acheter' => '购买',
            'acheté' => '已购买',
            'achete' => '已购买',
            'non acheter' => '未购买',
            'prix' => '价格',
            'date fin' => '结束日期',
            'date début' => '开始日期',
            'date debut' => '开始日期',
            'date début asc' => '开始日期升序',
            'date debut asc' => '开始日期升序',
            'date début desc' => '开始日期降序',
            'date debut desc' => '开始日期降序',
            'date fin asc' => '结束日期升序',
            'date fin desc' => '结束日期降序',
            'id sponsor asc' => '赞助商ID升序',
            'id sponsor desc' => '赞助商ID降序',
            'id produit asc' => '产品ID升序',
            'id produit desc' => '产品ID降序',
            'prix asc' => '价格升序',
            'prix desc' => '价格降序',
            'actions' => '操作'
        ]
    ];

    if (!isset($preferred[$target][$key])) {
        return null;
    }

    return $preferred[$target][$key] . $colonSuffix . $requiredSuffix;
}

function getLibreTranslateMirrors(): array {
    $configuredMirror = getenv('LIBRETRANSLATE_URL');
    $mirrors = [];

    if (is_string($configuredMirror) && trim($configuredMirror) !== '') {
        $mirrors[] = rtrim(trim($configuredMirror), '/');
    }

    return array_values(array_unique(array_merge($mirrors, [
        'https://translate.fedilab.app',
        'https://libretranslate.de',
        'https://translate.argosopentech.com',
        'https://translate.mentality.rip'
    ])));
}

function translateWithLibreTranslate(array $texts, string $targetLang): ?array {
    if (count($texts) === 0) {
        return [];
    }

    foreach (getLibreTranslateMirrors() as $mirror) {
        $translations = [];

        foreach ($texts as $text) {
            $ch = curl_init($mirror . '/translate');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query([
                    'q' => $text,
                    'source' => 'fr',
                    'target' => $targetLang,
                    'format' => 'text'
                ]),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'Accept: application/json',
                    'User-Agent: Mozilla/5.0'
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT => 15
            ]);

            $response = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false || $statusCode !== 200) {
                $translations = [];
                break;
            }

            $result = json_decode($response, true);
            $translatedText = $result['translatedText'] ?? null;

            if (!is_string($translatedText) || $translatedText === '') {
                $translations = [];
                break;
            }

            $translations[] = $translatedText;
        }

        if (count($translations) === count($texts)) {
            return $translations;
        }
    }

    return null;
}

function translateWithGoogle(string $text, string $targetLang): ?string {
    if ($text === '') {
        return '';
    }

    $url = 'https://translate.googleapis.com/translate_a/single?' . http_build_query([
        'client' => 'gtx',
        'sl' => 'auto',
        'tl' => $targetLang,
        'dt' => 't',
        'q' => $text
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => ['User-Agent: Mozilla/5.0'],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 12
    ]);

    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $statusCode !== 200) {
        return null;
    }

    $result = json_decode($response, true);
    if (!is_array($result) || !isset($result[0]) || !is_array($result[0])) {
        return null;
    }

    $translated = '';
    foreach ($result[0] as $part) {
        if (isset($part[0])) {
            $translated .= $part[0];
        }
    }

    return $translated !== '' ? $translated : null;
}

function translateWithMyMemory(string $text, string $targetLang): ?string {
    if ($text === '') {
        return '';
    }

    $targetForApi = $targetLang === 'zh-CN' ? 'zh-CN' : $targetLang;
    $url = 'https://api.mymemory.translated.net/get?' . http_build_query([
        'q' => $text,
        'langpair' => 'fr|' . $targetForApi
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => ['User-Agent: Mozilla/5.0'],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 12
    ]);

    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $statusCode !== 200) {
        return null;
    }

    $result = json_decode($response, true);
    $translated = $result['responseData']['translatedText'] ?? null;

    return is_string($translated) && $translated !== '' ? $translated : null;
}

$targetLanguages = $languageMap[$target];
$cache = [];
$translations = [];
$uniqueTexts = [];

foreach ($texts as $text) {
    $original = normalizeText($text);
    if (($target !== 'fr' || $dynamicTranslation) && $original !== '' && getPreferredUiTranslation($original, $target) === null) {
        $uniqueTexts[$original] = $original;
    }
}

$libreTranslations = [];
if ($target !== 'fr' && count($uniqueTexts) > 0) {
    $libreResult = translateWithLibreTranslate(array_values($uniqueTexts), $targetLanguages['libre']);
    if (is_array($libreResult)) {
        $index = 0;
        foreach (array_keys($uniqueTexts) as $originalText) {
            $libreTranslations[$originalText] = $libreResult[$index] ?? null;
            $index += 1;
        }
    }
}

foreach ($texts as $text) {
    $original = normalizeText($text);
    $cacheKey = $target . '|' . $original;

    if (!array_key_exists($cacheKey, $cache)) {
        if (($target === 'fr' && !$dynamicTranslation) || $original === '') {
            $cache[$cacheKey] = $original;
        } else {
            $cache[$cacheKey] = getPreferredUiTranslation($original, $target)
                ?? $libreTranslations[$original]
                ?? translateWithGoogle($original, $targetLanguages['google'])
                ?? translateWithMyMemory($original, $targetLanguages['mymemory'])
                ?? $original;
        }
    }

    $translations[] = ['translatedText' => $cache[$cacheKey]];
}

echo json_encode([
    'target' => $target,
    'translations' => $translations
], JSON_UNESCAPED_UNICODE);
