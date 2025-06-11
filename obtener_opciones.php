<?php
require __DIR__ . "/conexion.php";
$conn = Cconexion::ConexionBD();

// Verificar si se recibió el ID del producto
if (!isset($_GET['producto'])) {
    echo json_encode(['error' => 'No se proporcionó ID del producto']);
    exit;
}

$id_producto = intval($_GET['producto']);

// Obtener el tipo de cliente desde la URL
$tipo_cliente = $_GET['tipo_cliente'] ?? 'final_nuevo';

// Convertir el nuevo formato de tipo cliente al formato de base de datos
function getTipoClienteBaseDatos($tipo_cliente) {
    // En la base de datos solo tenemos 'final' o 'imprentero'
    if (strpos($tipo_cliente, 'final') !== false) {
        return 'final';
    } elseif (strpos($tipo_cliente, 'imprentero') !== false) {
        return 'imprentero';
    }
    return 'final'; // Valor por defecto
}

// Convertir al formato de la base de datos
$tipo_cliente_bd = getTipoClienteBaseDatos($tipo_cliente);

// Función para obtener opciones adicionales
function obtenerOpcionesAdicionales($id_producto, $tipo_cliente) {
    global $conn;
    $opciones = [];
    $precios = [];
    $t_cliente = getTipoClienteBaseDatos($tipo_cliente);
    
    // Consulta para obtener las opciones y sus precios
    $sql = "SELECT oe.id_opcion, oe.descripcion, oe.precio_opcion 
            FROM OpcionExtra oe 
            WHERE oe.id_producto = ? AND Tipo_cliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_producto, $tipo_cliente]);
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $opciones[$row['id_opcion']] = $row['descripcion'];
        $precios[$row['id_opcion']] = $row['precio_opcion'];
    }
    
    // Si no hay opciones en la BD, usar opciones predeterminadas
    if (empty($opciones)) {
        switch ($id_producto) {
            case 1: // Gigantografía
                $opciones = [
                    5 => 'Termosellado',
                    6 => 'Pita y Tubo',
                    7 => 'Ojales',
                    8 => 'Marco'
                ];
                // Precios predeterminados
                $precios = [
                    5 => 1.5, // Termosellado
                    6 => 2.0, // Pita y Tubo
                    7 => 0.5, // Ojales
                    8 => 3.0  // Marco
                ];
                break;
            case 2: // Tomatodo
                $opciones = [
                    2 => 'UV Full Color',
                    3 => 'UV DTF',
                    1 => 'Serigrafeado'
                ];
                // Precios predeterminados
                $precios = [
                    2 => 4.0, // UV Full Color
                    3 => 3.0, // UV DTF
                    1 => 2.0  // Serigrafeado
                ];
                break;
            case 3: // Lapicero
                $opciones = [
                    1 => 'Serigrafeado',
                    4 => 'UV Full Color'
                ];
                // Precios predeterminados
                $precios = [
                    1 => 1.0, // Serigrafeado
                    4 => 1.5  // UV Full Color
                ];
                break;
        }
    }
    
      return [
        'opciones' => $opciones,
        'precios' => $precios,
        'tipo_cliente' => $t_cliente // <-- Se agrega aquí
    ];
}

// Obtener opciones y precios
$resultado = obtenerOpcionesAdicionales($id_producto, $tipo_cliente_bd);

// Devolver datos en formato JSON
header('Content-Type: application/json');
echo json_encode($resultado);
?>