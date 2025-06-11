<?php
include 'conexion.php';
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
<html>
<head>
    <title>Resumen de Cotizaciones</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding: 20px; }
        .table-container { margin-top: 20px; }
        .message { margin-bottom: 20px; padding: 10px; border-radius: 5px; }
        .message.success { background-color: #d4edda; color: #155724; }
        .message.error { background-color: #f8d7da; color: #721c24; }
        .btn-icon { margin-right: 5px; }
        .print-header { display: none; }
        
        @media print {
            .no-print { display: none; }
            .print-header { display: block; margin-bottom: 20px; }
            .card { border: none; }
            .container { width: 100%; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="print-header">
            <h2 class="text-center">RESUMEN DE COTIZACIÓN</h2>
            <p class="text-center">Fecha de impresión: <?php echo date('d/m/Y H:i'); ?></p>
            <hr>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h1>Resumen de Cotizaciones</h1>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-home btn-icon"></i>Inicio</a>
        </div>
        
        <?php if (isset($_GET['mensaje'])): ?>
        <div class="message success">
            <?php echo htmlspecialchars($_GET['mensaje']); ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="message error">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($cotizacion_actual): ?>
        <!-- Vista detallada de la cotización -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Cotización #<?php echo $cotizacion_actual['id_registro']; ?></h3>
                <div class="no-print">
                    <button onclick="window.print()" class="btn btn-light btn-sm me-2">
                        <i class="fas fa-print btn-icon"></i>Imprimir
                    </button>
                    <a href="resumen_cotizacion.php?action=delete&id=<?php echo $cotizacion_actual['id_registro']; ?>" 
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('¿Está seguro de eliminar esta cotización?')">
                        <i class="fas fa-trash-alt btn-icon"></i>Eliminar
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Información del Cliente</h5>
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($cliente_actual['nombre']); ?></p>
                        <p><strong>Razón Social:</strong> <?php echo htmlspecialchars($cliente_actual['razon_social']); ?></p>
                        <p><strong>RUC:</strong> <?php echo htmlspecialchars($cliente_actual['ruc']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5>Información de Contacto</h5>
                        <p><strong>Celular:</strong> <?php echo htmlspecialchars($cliente_actual['celular']); ?></p>
                        <p><strong>Correo:</strong> <?php echo htmlspecialchars($cliente_actual['correo']); ?></p>
                        <p><strong>Fecha de cotización:</strong> <?php echo date('d/m/Y H:i', strtotime($cotizacion_actual['fecha'])); ?></p>
                    </div>
                </div>
                
                <h5>Detalles de la Cotización</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Opciones</th>
                                <th>Precio Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_general = 0;
                            foreach ($detalles as $detalle): 
                                $total_general += $detalle['precio_total'];
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($detalle['producto_nombre']); ?></td>
                                <td><?php echo $detalle['cantidad']; ?></td>
                                <td>
                                    <?php if (!empty($detalle['opciones'])): ?>
                                        <ul class="mb-0">
                                        <?php foreach ($detalle['opciones'] as $opcion): ?>
                                            <li><?php echo htmlspecialchars($opcion['descripcion']); ?></li>
                                        <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <em>Sin opciones adicionales</em>
                                    <?php endif; ?>
                                </td>
                                <td>S/ <?php echo number_format($detalle['precio_total'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total General:</th>
                                <th>S/ <?php echo number_format($total_general, 2); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="mt-4 no-print">
                    <a href="resumen_cotizacion.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left btn-icon"></i>Volver al Listado
                    </a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Listado de cotizaciones -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Listado de Cotizaciones</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th class="no-print">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($cotizaciones)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay cotizaciones registradas</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($cotizaciones as $cotizacion): ?>
                                <tr>
                                    <td><?php echo $cotizacion['id_registro']; ?></td>
                                    <td><?php echo htmlspecialchars($cotizacion['cliente_nombre']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($cotizacion['fecha'])); ?></td>
                                    <td>S/ <?php echo number_format($cotizacion['total'], 2); ?></td>
                                    <td class="no-print">
                                        <a href="resumen_cotizacion.php?id=<?php echo $cotizacion['id_registro']; ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye btn-icon"></i>Ver
                                        </a>
                                        <a href="resumen_cotizacion.php?action=delete&id=<?php echo $cotizacion['id_registro']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('¿Está seguro de eliminar esta cotización?')">
                                            <i class="fas fa-trash-alt btn-icon"></i>Eliminar
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>