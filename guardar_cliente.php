<?php
include_once("conexion.php");

// Recoge y se envia los datos ingresados
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"];
    $razon_social = $_POST["razon_social"];
    $ruc = $_POST["ruc"];
    $celular = $_POST["celular"];
    $correo = $_POST["correo"];

    $conn = Cconexion::ConexionBD();

    if ($conn) {
        $sql = "INSERT INTO Cliente (nombre, razon_social, ruc, celular, correo) 
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$nombre, $razon_social, $ruc, $celular, $correo]);
        
        // Obtener el ID del cliente recién insertado
        $id_cliente = $conn->lastInsertId();
        
        if ($id_cliente) {
            header("Location:cotizacion_gigantografia.php?id_cliente=$id_cliente");
            exit;
        } else {
            echo "❌ Error al obtener el ID del cliente.";
        }
    } else {
        echo "❌ Error al conectar con la base de datos.";
    }
}
?>