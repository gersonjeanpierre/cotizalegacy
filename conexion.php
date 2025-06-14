    <?php
    class Cconexion{

        public static function ConexionBD(){
            $localhost='localhost';
            $dbname='CotizacionDev';
            $username='sa';
            // $password='admin123';
            $password='admin123';
            $puerto=1433;

            try{
              $conexion = new PDO("sqlsrv:Server=$localhost,$puerto;Database=$dbname;Encrypt=yes;TrustServerCertificate=yes", $username, $password);
                $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $conexion;
            } catch (PDOException $e) {
                echo "Error de conexión: " . $e->getMessage();
                return null;
            }
        }
    }
    ?>
