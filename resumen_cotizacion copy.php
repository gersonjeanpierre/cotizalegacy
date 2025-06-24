<?php
require __DIR__ . "/conexion.php";
$conn = Cconexion::ConexionBD();

function obtenerNombreCliente($id_cliente) {
    global $conn;
    $sql = "SELECT nombre FROM Cliente WHERE id_cliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_cliente]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        return $row['nombre'];
    }
    return "Cliente desconocido";
}

function obtenerNombreProducto($id_producto) {
    global $conn;
    $sql = "SELECT nombre FROM Producto WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_producto]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        return $row['nombre'];
    }
    return "Producto desconocido";
}

function obtenerDescripcionOpcion($id_opcion) {
    global $conn;
    $sql = "SELECT descripcion FROM OpcionExtra WHERE id_opcion = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_opcion]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        return $row['descripcion'];
    }
    return "Opción desconocida";
}

// Eliminar cotización
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_registro = intval($_GET['id']);

    try {
        // Iniciar transacción
        $conn->beginTransaction();

        // Obtener primero los IDs de detalles para eliminar opciones relacionadas
        $sql = "SELECT id_detalle FROM DetalleCotizacion WHERE id_registro = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_registro]);
        $detalles_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Eliminar opciones de detalles (si existe la tabla OpcionDetalle)
        if (!empty($detalles_ids)) {
            $placeholders = implode(',', array_fill(0, count($detalles_ids), '?'));
            $sql = "DELETE FROM OpcionDetalle WHERE id_detalle IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            $stmt->execute($detalles_ids);
        }

        // Eliminar los detalles
        $sql = "DELETE FROM DetalleCotizacion WHERE id_registro = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_registro]);

        // Luego eliminar el registro principal
        $sql = "DELETE FROM RegistroCotizacion WHERE id_registro = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_registro]);

        // Confirmar transacción
        $conn->commit();

        $mensaje = "Cotización eliminada con éxito";
        header("Location: resumen_cotizacion.php?mensaje=" . urlencode($mensaje));
        exit;
    } catch (PDOException $e) {
        // Revertir en caso de error
        $conn->rollBack();
        $error = "Error al eliminar la cotización: " . $e->getMessage();
    }
}

// Obtener listado de cotizaciones
$cotizaciones = [];
$sql = "SELECT rc.id_registro, rc.id_cliente, rc.fecha, c.nombre AS cliente_nombre,
                SUM(dc.precio_total) AS total
        FROM RegistroCotizacion rc
        JOIN Cliente c ON rc.id_cliente = c.id_cliente
        JOIN DetalleCotizacion dc ON rc.id_registro = dc.id_registro
        GROUP BY rc.id_registro, rc.id_cliente, rc.fecha, c.nombre
        ORDER BY rc.fecha DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$cotizaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si se solicita ver detalles de una cotización específica
$detalles = [];
$cotizacion_actual = null;
$cliente_actual = null;

if (isset($_GET['id'])) {
    $id_registro = intval($_GET['id']);

    // Obtener información de la cotización
    $sql = "SELECT rc.id_registro, rc.id_cliente, rc.fecha, c.nombre AS cliente_nombre,
                    c.razon_social, c.ruc, c.celular, c.correo
           FROM RegistroCotizacion rc
           JOIN Cliente c ON rc.id_cliente = c.id_cliente
           WHERE rc.id_registro = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_registro]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cotizacion_actual = $row;
        $cliente_actual = [
            'nombre' => $row['cliente_nombre'],
            'razon_social' => $row['razon_social'],
            'ruc' => $row['ruc'],
            'celular' => $row['celular'],
            'correo' => $row['correo']
        ];
    }

    // Obtener detalles de la cotización
            $sql = "SELECT dc.id_detalle, dc.id_producto, dc.cantidad, dc.precio_total,
            p.nombre AS producto_nombre
        FROM DetalleCotizacion dc
        JOIN Producto p ON dc.id_producto = p.id_producto
        WHERE dc.id_registro = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_registro]);
        $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Ahora obtener las opciones para cada detalle
        foreach ($detalles as $key => $detalle) {
        // Consulta para obtener opciones asociadas al detalle
        $sql = "SELECT oe.id_opcion, oe.descripcion
        FROM OpcionDetalle od
        JOIN OpcionExtra oe ON od.id_opcion = oe.id_opcion
        WHERE od.id_detalle = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$detalle['id_detalle']]);
        $opciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $detalles[$key]['opciones'] = $opciones;
        }

}

?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Cotizaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="container mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Resumen de Cotizaciones</h1>
            <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg flex items-center transition duration-300 ease-in-out">
                <i class="fas fa-home mr-2"></i>Inicio
            </a>
        </div>

        <?php if (isset($_GET['mensaje'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <?php echo htmlspecialchars($_GET['mensaje']); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <?php if ($cotizacion_actual): ?>
        <div class="bg-white shadow-lg rounded-lg p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">Cotización #<?php echo $cotizacion_actual['id_registro']; ?></h2>
                <div>
                    <button id="downloadPdfBtn"
                            data-cotizacion='<?php echo json_encode($cotizacion_actual); ?>'
                            data-cliente='<?php echo json_encode($cliente_actual); ?>'
                            data-detalles='<?php echo json_encode($detalles); ?>'
                            data-id-registro="<?php echo $cotizacion_actual['id_registro']; ?>"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg flex items-center mr-2 transition duration-300 ease-in-out">
                        <i class="fas fa-file-pdf mr-2"></i>Descargar PDF
                    </button>
                    <a href="resumen_cotizacion.php?action=delete&id=<?php echo $cotizacion_actual['id_registro']; ?>"
                       class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg flex items-center transition duration-300 ease-in-out mt-2 md:mt-0"
                       onclick="return confirm('¿Está seguro de eliminar esta cotización? Esto eliminará todos los detalles y opciones relacionadas.')">
                        <i class="fas fa-trash-alt mr-2"></i>Eliminar
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <h3 class="text-xl font-medium text-gray-700 mb-3">Información del Cliente</h3>
                    <p class="text-gray-600"><strong class="font-semibold">Nombre:</strong> <?php echo htmlspecialchars($cliente_actual['nombre']); ?></p>
                    <p class="text-gray-600"><strong class="font-semibold">Razón Social:</strong> <?php echo htmlspecialchars($cliente_actual['razon_social']); ?></p>
                    <p class="text-gray-600"><strong class="font-semibold">RUC:</strong> <?php echo htmlspecialchars($cliente_actual['ruc']); ?></p>
                </div>
                <div>
                    <h3 class="text-xl font-medium text-gray-700 mb-3">Información de Contacto</h3>
                    <p class="text-gray-600"><strong class="font-semibold">Celular:</strong> <?php echo htmlspecialchars($cliente_actual['celular']); ?></p>
                    <p class="text-gray-600"><strong class="font-semibold">Correo:</strong> <?php echo htmlspecialchars($cliente_actual['correo']); ?></p>
                    <p class="text-gray-600"><strong class="font-semibold">Fecha de Cotización:</strong> <?php echo date('d/m/Y H:i', strtotime($cotizacion_actual['fecha'])); ?></p>
                </div>
            </div>

            <h3 class="text-xl font-medium text-gray-700 mb-4">Detalles de la Cotización</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="py-3 px-4 text-left">Producto</th>
                            <th class="py-3 px-4 text-left">Cantidad</th>
                            <th class="py-3 px-4 text-left">Opciones</th>
                            <th class="py-3 px-4 text-right">Precio Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_general = 0;
                        foreach ($detalles as $detalle):
                            $total_general += $detalle['precio_total'];
                        ?>
                        <tr class="border-b border-gray-200 last:border-b-0">
                            <td class="py-3 px-4"><?php echo htmlspecialchars($detalle['producto_nombre']); ?></td>
                            <td class="py-3 px-4"><?php echo $detalle['cantidad']; ?></td>
                            <td class="py-3 px-4">
                                <?php if (!empty($detalle['opciones'])): ?>
                                    <ul class="list-disc list-inside text-sm text-gray-600">
                                    <?php foreach ($detalle['opciones'] as $opcion): ?>
                                        <li><?php echo htmlspecialchars($opcion['descripcion']); ?></li>
                                    <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <em class="text-gray-500 text-sm">Sin opciones adicionales</em>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-right">S/ <?php echo number_format($detalle['precio_total'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50">
                            <th colspan="3" class="py-3 px-4 text-right text-lg font-semibold text-gray-800">Total General:</th>
                            <th class="py-3 px-4 text-right text-lg font-bold text-gray-900">S/ <?php echo number_format($total_general, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-8 text-center">
                <a href="resumen_cotizacion.php" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg flex items-center justify-center mx-auto w-fit transition duration-300 ease-in-out">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al Listado
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-white shadow-lg rounded-lg p-6">
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">Listado de Cotizaciones</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="py-3 px-4 text-left">#</th>
                            <th class="py-3 px-4 text-left">Cliente</th>
                            <th class="py-3 px-4 text-left">Fecha</th>
                            <th class="py-3 px-4 text-right">Total</th>
                            <th class="py-3 px-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cotizaciones)): ?>
                        <tr>
                            <td colspan="5" class="py-4 px-4 text-center text-gray-500">No hay cotizaciones registradas</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($cotizaciones as $cotizacion): ?>
                            <tr class="border-b border-gray-200 last:border-b-0">
                                <td class="py-3 px-4"><?php echo $cotizacion['id_registro']; ?></td>
                                <td class="py-3 px-4"><?php echo date('d/m/Y H:i', strtotime($cotizacion['fecha'])); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($cotizacion['cliente_nombre']); ?></td>
                                <td class="py-3 px-4 text-right">S/ <?php echo number_format($cotizacion['total'], 2); ?></td>
                                <td class="py-3 px-4 text-center">
                                    <a href="resumen_cotizacion.php?id=<?php echo $cotizacion['id_registro']; ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-3 rounded-lg text-sm inline-flex items-center mr-2 transition duration-300 ease-in-out">
                                        <i class="fas fa-eye mr-1"></i>Ver
                                    </a>
                                    <a href="resumen_cotizacion.php?action=delete&id=<?php echo $cotizacion['id_registro']; ?>"
                                       class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-lg text-sm inline-flex items-center transition duration-300 ease-in-out"
                                       onclick="return confirm('¿Está seguro de eliminar esta cotización? Esto eliminará todos los detalles y opciones relacionadas.')">
                                        <i class="fas fa-trash-alt mr-1"></i>Eliminar
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="./cotizacion_pdf/generar_pdf.js"></script>
</body>
</html>