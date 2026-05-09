<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$localConfigPath = __DIR__ . '/config.local.php';
if (file_exists($localConfigPath)) {
    require_once $localConfigPath;
}

function geminiApiKey()
{
    $envKey = getenv('GEMINI_API_KEY');
    if (!empty($envKey)) {
        return $envKey;
    }

    return defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
}

function appBasePath()
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $normalizedScript = str_replace('\\', '/', $scriptName);
    $pos = stripos($normalizedScript, '/view/');
    if ($pos === false) {
        $dir = rtrim(str_replace('\\', '/', dirname($normalizedScript)), '/');
        return $dir === '.' ? '' : $dir;
    }
    return substr($normalizedScript, 0, $pos);
}

function appUrl($path = '')
{
    $base = rtrim(appBasePath(), '/');
    $path = ltrim($path, '/');
    return $base . ($path !== '' ? '/' . $path : '');
}

class config
{   private static $pdo = null;
    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            $servername="localhost";
            $username="root";
            $password ="";
            $dbname=defined('DB_NAME') ? DB_NAME : "skiller_comp";
            try {
                self::$pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
                        $username,
                        $password
                   
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
               
               
            } catch (Exception $e) {
                die('Erreur: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}

function currentUserRole()
{
    return $_SESSION['user']['role'] ?? null;
}

function isLoggedIn()
{
    return isset($_SESSION['user']);
}

function loginUser($username, $password)
{
    $db = config::getConnexion();
    $query = $db->prepare("SELECT * FROM utilisateur WHERE Email = :login OR Nom = :login LIMIT 1");
    $query->execute(['login' => $username]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['MDP'])) {
        return false;
    }

    $roleMap = [
        'admin' => 'admin',
        'professionnel' => 'super_user',
        'etudiant' => 'simple_user'
    ];

    $_SESSION['user'] = [
        'id' => (int)$user['ID'],
        'username' => $user['Nom'],
        'email' => $user['Email'],
        'name' => $user['Nom'],
        'role' => $roleMap[$user['Type']] ?? 'simple_user',
        'db_type' => $user['Type']
    ];

    return true;
}

function logoutUser()
{
    unset($_SESSION['user']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ' . appUrl('View/Auth/login.php'));
        exit();
    }
}

function requireRole(array $roles)
{
    requireLogin();
    if (!in_array(currentUserRole(), $roles, true)) {
        http_response_code(403);
        echo 'Access denied.';
        exit();
    }
}

function canManageOpportunityCreate()
{
    return currentUserRole() === 'super_user';
}

function canManageOpportunityUpdateDelete()
{
    $role = currentUserRole();
    return $role === 'admin' || $role === 'super_user';
}
?>









