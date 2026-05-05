<?php
// =====================
// PATH CONSTANTS
// =====================
define("BASE_PATH", __DIR__);

define("UPLOAD_DIR", BASE_PATH . "/View/gestionformation/uploads/");
define("UPLOAD_URL", "/skiller7finalversion/View/gestionformation/uploads/");

class config
{   private static $pdo = null;
    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            $servername="localhost";
            $username="root";
            $password ="";
            $dbname="skiller";
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
config::getConnexion();
?>









