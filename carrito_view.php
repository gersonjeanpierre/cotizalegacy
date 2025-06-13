<?php
// Verificar si el carrito está vacío
if (empty($_SESSION['carrito'])) {
    echo '<div class="alert alert-info">
            <i class="bi bi-cart-x"></i> El carrito de cotización está vacío.
          </div>';
} else {
    ?>
    <div class="carrito-container">
        <?php 
        $total_general_sin_igv = 0;
        $total_general_con_igv = 0;
        
        foreach ($_SESSION['carrito'] as $item_id => $item): 
            // Calcular totales generales
            $total_general_sin_igv += $item['total_sin_igv'];
            $total_general_con_igv += $item['total_con_igv'];
        ?>
            <div class="carrito-item">
                <div class="row">
                    <div class="col-md-9">
                        <h4><?php echo htmlspecialchars($item['nombre_producto']); ?></h4>
                        
                        <?php 
                        // Mostrar datos adicionales específicos del producto
                        if (isset($item['datos_adicionales']) && is_array($item['datos_adicionales'])) {
                            foreach ($item['datos_adicionales'] as $etiqueta => $valor) {
                                echo "<p><strong>$etiqueta:</strong> $valor</p>";
                            }
                        }
                        
                        // Mostrar opciones seleccionadas
                        if (isset($item['opciones_detalle']) && !empty($item['opciones_detalle'])) {
                            echo "<p><strong>Opciones adicionales:</strong></p>";
                            echo "<ul>";
                            foreach ($item['opciones_detalle'] as $opcion) {
                                $valor_formateado = number_format($opcion['valor'], 2);
                                
                                // Adaptamos la descripción según el tipo de opción
                                $detalle = "";
                                
                                if (isset($opcion['tipo'])) {
                                    if ($opcion['tipo'] == 'Ancho') {
                                        $detalle = " (Ancho)";
                                    } elseif ($opcion['tipo'] == 'Largo') {
                                        $detalle = " (Largo)";
                                    } elseif ($opcion['tipo'] == 'Ambos') {
                                        $detalle = " (Todo el perímetro)";
                                    }
                                }
                                
                                if (isset($opcion['cantidad'])) {
                                    $detalle = " ({$opcion['cantidad']} unidades)";
                                }

                                if ($opcion['tipo'] == 'Material') {
                                    $detalle = $opcion['cantidad_planchas'];
                                }
                                
                                // Obtener el nombre de la opción desde la base de datos
                                global $conn;
                                $nombre_opcion = "Opción " . $opcion['id'];
                                
                                try {
                                    $sql = "SELECT descripcion FROM OpcionExtra WHERE id_opcion = ?";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->execute([$opcion['id']]);
                                    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $nombre_opcion = $row['descripcion'];
                                    }
                                } catch (PDOException $e) {
                                    // Silenciamos el error y usamos el nombre genérico
                                }
                                
                                echo "<li>$nombre_opcion$detalle: S/ $valor_formateado</li>";
                            }
                            echo "</ul>";
                        }
                        ?>
                    </div>
                    <div class="col-md-3 text-end">
                        <p><strong>Subtotal:</strong> S/ <?php echo number_format($item['subtotal'], 2); ?></p>
                        <p><strong>Margen:</strong> S/ <?php echo number_format($item['margen'], 2); ?></p>
                        <p><strong>Total sin IGV:</strong> S/ <?php echo number_format($item['total_sin_igv'], 2); ?></p>
                        <p><strong>Total con IGV:</strong> S/ <?php echo number_format($item['total_con_igv'], 2); ?></p>
                        <a href="?eliminar_producto=<?php echo $item_id; ?>" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash"></i> Eliminar
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="total-carrito">
        <div class="row">
            <div class="col-md-6">
                <h4>Totales de la Cotización</h4>
            </div>
            <div class="col-md-6 text-end">
                <p>Total sin IGV: S/ <?php echo number_format($total_general_sin_igv, 2); ?></p>
                <p>Total con IGV (18%): S/ <?php echo number_format($total_general_con_igv, 2); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Formulario para finalizar la cotización -->
    <form method="POST" class="mt-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Finalizar Cotización</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Cliente seleccionado:</label>
                    <div class="form-control-plaintext">
                        <?php 
                        // Use the session variable instead of POST data
                        $cliente_id = $_SESSION['cliente_seleccionado'] ?? '';
                        echo !empty($cliente_id) && isset($clientes[$cliente_id]) ? $clientes[$cliente_id] : 'Ningún cliente seleccionado'; 
                        ?>
                    </div>
                    <input type="hidden" id="cliente_final" name="cliente_final" value="<?php echo $cliente_id; ?>">
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="aplicar_igv_final" name="aplicar_igv_final" value="1" <?php echo (isset($_POST['aplicar_igv_final']) && $_POST['aplicar_igv_final'] == '1') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="aplicar_igv_final">
                        Aplicar IGV (18%) a toda la cotización
                    </label>
                </div>
                
                <div class="row">
                    <div class="col text-end">
                        <a href="?vaciar_carrito=true" class="btn btn-warning" onclick="return confirm('¿Estás seguro de vaciar el carrito?');">
                            <i class="bi bi-cart-x"></i> Vaciar Carrito
                        </a>
                        <button type="submit" name="finalizar_cotizacion" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Finalizar Cotización
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php
}
?>