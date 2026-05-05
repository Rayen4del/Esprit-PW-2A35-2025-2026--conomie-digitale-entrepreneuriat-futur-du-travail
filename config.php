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
            $dbname="bd_skiller";
            try {
                self::$pdo = new PDO("mysql:host=$servername;dbname=$dbname",
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
    $users = [
        'admin'     => ['password' => 'admin123', 'role' => 'admin',      'name' => 'Admin'],
        'superuser' => ['password' => 'super123', 'role' => 'super_user', 'name' => 'Super User'],
        'user'      => ['password' => 'user123',  'role' => 'simple_user', 'name' => 'Simple User'],
    ];

    // Try exact username match first
    if (isset($users[$username])) {
        if ($users[$username]['password'] === $password) {
            $_SESSION['user'] = [
                'id'       => ($username === 'admin' ? 1 : ($username === 'superuser' ? 2 : 3)),
                'username' => $username,
                'name'     => $users[$username]['name'],
                'role'     => $users[$username]['role'],
            ];
            return true;
        }
    }

    // Optional: allow email-style login (for demo)
    $emailMap = [
        'admin@skiller.com'     => 'admin',
        'super@skiller.com'     => 'superuser',
        'user@skiller.com'      => 'user',
    ];

    if (isset($emailMap[$username])) {
        $realUsername = $emailMap[$username];
        if (isset($users[$realUsername]) && $users[$realUsername]['password'] === $password) {
            $_SESSION['user'] = [
                'id'       => ($realUsername === 'admin' ? 1 : ($realUsername === 'superuser' ? 2 : 3)),
                'username' => $realUsername,
                'name'     => $users[$realUsername]['name'],
                'role'     => $users[$realUsername]['role'],
            ];
            return true;
        }
    }

    return false;
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









