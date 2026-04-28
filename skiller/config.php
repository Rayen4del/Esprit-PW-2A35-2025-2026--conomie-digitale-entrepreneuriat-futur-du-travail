<?php
// Dynamic BASE_URL for portability
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = dirname(dirname($scriptName)); // Go up 2 levels from any script
define('BASE_URL', rtrim($basePath, '/') . '/');
define('CONTROLLER_URL', BASE_URL . 'controller/CommentController.php');
class Config
{
    private static $pdo = null;

    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "bd_skiller";
            try {
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname;charset=utf8",
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
?>