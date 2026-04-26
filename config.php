<?php
if (!class_exists('config')) {
    class config
    {
        private static $pdo = null;

        public static function getConnexion()
        {
            if (!isset(self::$pdo)) {
                $servername = "localhost";
                $username   = "root";
                $password   = "";
                $dbname     = "bd_skiller";
                try {
                    self::$pdo = new PDO(
                        "mysql:host=$servername;dbname=$dbname;charset=utf8",
                        $username,
                        $password
                    );
                    self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    die('Erreur de connexion : ' . $e->getMessage());
                }
            }
            return self::$pdo;
        }
    }
}
config::getConnexion();
?>
