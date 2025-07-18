<?php
require __DIR__ . "/conexion.php";

$conn = Cconexion::ConexionBD();

// Iniciar o continuar la sesión para mantener el carrito
session_start();
if (isset($_POST['cliente']) && !empty($_POST['cliente'])) {
    $_SESSION['cliente_seleccionado'] = $_POST['cliente'];
}
// Inicializar el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
function calcularMargenPorTipoCliente($tipo_cliente)
{
    switch ($tipo_cliente) {
        case 'final_nuevo':
            return 0.5;
        case 'final_frecuente':
            return 0.3;
        case 'imprentero_nuevo':
            return  0.3;
        case 'imprentero_frecuente':
            return 0.15;
        default:
            return 0.3;
    }
}

// Función para convertir el nuevo tipo de cliente al formato base de datos
function getTipoClienteBaseDatos($tipo_cliente)
{
    // En la base de datos solo tenemos 'final' o 'imprentero'
    if (strpos($tipo_cliente, 'final') !== false) {
        return 'final';
    } elseif (strpos($tipo_cliente, 'imprentero') !== false) {
        return 'imprentero';
    }
    return 'final'; // Valor por defecto
}

// Cargar datos desde la base de datos
function obtenerProductos()
{
    global $conn;
    $productos = [];
    $sql = "SELECT id_producto, nombre FROM Producto";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $productos[$row['id_producto']] = $row['nombre'];
    }
    return $productos;
}

function obtenerClientes()
{
    global $conn;
    $clientes = [];
    $sql = "SELECT id_cliente, nombre FROM Cliente WHERE deletedAt IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $clientes[$row['id_cliente']] = $row['nombre'];
    }
    return $clientes;
}

function obtenerPrecioPorArea($id_producto, $area, $tipo_cliente)
{
    global $conn;
    $tipo_cliente_bd = getTipoClienteBaseDatos($tipo_cliente);

    $sql = "SELECT precio_unitario FROM Precios 
            WHERE id_producto = ? 
              AND cantidad_min <= ? 
              AND cantidad_max >= ?
              AND Tipo_cliente = ? ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_producto, $area, $area, $tipo_cliente_bd]);

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        return $row['precio_unitario'];
    } else {
        error_log("No se encontró precio para producto $id_producto, área $area, cliente $tipo_cliente_bd");
        return 20.00;
        $precio = 20.00;
    }

    return $precio;
}

function obtenerPrecioPorAreaNoDB($tipo_cliente, $cantidad)
{
    if ($tipo_cliente == 'final') {
        if ($cantidad <= 6 && $cantidad > 0) {
            $precio = 17.00;
        };
        if ($cantidad > 6 && $cantidad <= 18) {
            $precio = 15.00;
        };
        if ($cantidad > 18 && $cantidad <= 500) {
            $precio = 13.00;
        };
    }

    if ($tipo_cliente == 'imprentero') {
        if ($cantidad <= 6 && $cantidad > 0) {
            $precio = 13.00;
        };
        if ($cantidad > 6 && $cantidad <= 18) {
            $precio = 12.00;
        };
        if ($cantidad > 18 && $cantidad <= 500) {
            $precio = 10.00;
        };
    }

    return $precio;
}

function obtenerPrecioProducto($id_producto, $cantidad = 1, $tipo_cliente)
{
    global $conn;
    $precio = 0;
    $tipo_cliente_bd = getTipoClienteBaseDatos($tipo_cliente);

    $sql = "SELECT precio_unitario FROM Precios WHERE id_producto = ? 
            AND cantidad_min <= ? AND cantidad_max >= ? AND Tipo_cliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_producto, $cantidad, $cantidad, $tipo_cliente_bd]);

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $precio = $row['precio_unitario'];
    }
    return $precio;
}

function obtenerPrecioOpcion($id_opcion, $producto_id = null, $tipo_cliente)
{
    global $conn;
    $tipo_cliente_bd = getTipoClienteBaseDatos($tipo_cliente);

    // Intentar obtener el precio de la base de datos
    $sql = "SELECT precio_opcion FROM OpcionExtra WHERE id_opcion = ? AND id_producto = ? AND Tipo_cliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_opcion, $producto_id, $tipo_cliente_bd]);


    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Si el precio está en la base de datos y no es NULL, usarlo
        if ($row['precio_opcion'] !== null) {
            return floatval($row['precio_opcion']);
        }
    }
}

function calcularPrecioTermosellado($opcion_id, $ancho, $largo, $tipo_opcion, $cantidad, $tipo_cliente)
{
    // Obtener precio base de termosellado por metro lineal (2.00 soles)
    $precio_metro = obtenerPrecioOpcion($opcion_id, 1, $tipo_cliente); // 1 es el id_producto para Gigantografía

    $adicional = 0;
    if ($tipo_opcion == 'Ancho') {
        $adicional = 2 * $ancho * $precio_metro;
    } elseif ($tipo_opcion == 'Largo') {
        $adicional = 2 * $largo * $precio_metro;
    } elseif ($tipo_opcion == 'Ambos') {
        $adicional = 2 * ($ancho + $largo) * $precio_metro;
    }

    return $adicional * $cantidad;
}

function calcularPrecioPitaYTubo($opcion_id, $ancho, $largo, $tipo_opcion, $cantidad, $tipo_cliente)
{

    $precio_metro = obtenerPrecioOpcion($opcion_id, 1, $tipo_cliente);

    $adicional = 0;
    if ($tipo_opcion == 'Ancho') {
        $adicional = 2 * $ancho * $precio_metro;
    } elseif ($tipo_opcion == 'Largo') {
        $adicional = 2 * $largo * $precio_metro;
    } elseif ($tipo_opcion == 'Ambos') {
        $adicional = 2 * ($ancho + $largo) * $precio_metro;
    }

    return $adicional * $cantidad;
}

function calcularPrecioOjales($opcion_id, $cantidad_ojales, $tipo_cliente)
{
    $precio_ojal = obtenerPrecioOpcion($opcion_id, 1, $tipo_cliente);
    return $cantidad_ojales * $precio_ojal; // Sin multiplicar por la cantidad de producto
}

function calcularPrecioMarco($opcion_id, $ancho, $largo, $cantidad, $tipo_cliente)
{

    $precio_metro = obtenerPrecioOpcion($opcion_id, 1, $tipo_cliente);

    // El marco siempre se aplica a todo el perímetro (ambos)
    $perimetro = 2 * ($ancho + $largo);
    $interno = 0.6 * ($perimetro * $precio_metro);
    $costo_fuera = $perimetro * $precio_metro;
    $costo_material = $costo_fuera + $interno;

    // Cálculo adicional para marco (mano de obra interna, etc.)
    $mano_obra_interna = $costo_material * 0.75; // 75% de mano de obra interna para marcos

    return ($costo_material + $mano_obra_interna) * $cantidad;
}

function guardarCotizacion($id_cliente, $total, $detalles)
{
    global $conn;

    try {
        // Iniciar transacción
        $conn->beginTransaction();

        // Generar fecha actual
        $fecha_actual = date('Y-m-d H:i:s');

        // Insertar en la tabla RegistroCotizacion
        $sql = "INSERT INTO RegistroCotizacion (id_cliente, fecha) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_cliente, $fecha_actual]);

        // Obtener el ID de la cotización recién insertada
        $id_registro = $conn->lastInsertId();

        // Insertar en la tabla DetalleCotizacion para cada producto
        foreach ($detalles as $detalle) {
            $sql = "INSERT INTO DetalleCotizacion (id_registro, id_producto, cantidad, precio_total) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $id_registro,
                $detalle['id_producto'],
                $detalle['cantidad'],
                $detalle['precio_total']
            ]);

            // Obtener el ID del detalle recién insertado
            $id_detalle = $conn->lastInsertId();

            // Si hay opciones seleccionadas para este detalle, guardarlas en OpcionDetalle
            if (isset($detalle['opciones']) && is_array($detalle['opciones'])) {
                foreach ($detalle['opciones'] as $id_opcion) {
                    $sql = "INSERT INTO OpcionDetalle (id_detalle, id_opcion) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$id_detalle, $id_opcion]);
                }
            }
        }

        // Confirmar transacción
        $conn->commit();
        return $id_registro;
    } catch (PDOException $e) {
        // Revertir transacción en caso de error
        $conn->rollBack();
        echo "Error al guardar la cotización: " . $e->getMessage();
        return false;
    }
}

function procesarResultadoConIGV($subtotal, $margen, $nombre_producto, $datos_adicionales = [], $con_igv = false)
{
    // Calcular total sin IGV
    $total_sin_igv = $subtotal + $margen;

    // Calcular IGV y total con IGV
    $igv = $total_sin_igv * 0.18;
    $total_con_igv = $total_sin_igv + $igv;

    // Construir el resultado básico
    $resultado = "<div class='resultado-cotizacion'>
                    <p>Producto: $nombre_producto</p>";

    // Añadir datos adicionales (área, cantidad, etc.)
    foreach ($datos_adicionales as $etiqueta => $valor) {
        $resultado .= "<p>$etiqueta: $valor</p>";
    }

    // Añadir información de cálculo
    $resultado .= "<p>Subtotal: S/ " . number_format($subtotal, 2) . "</p>" .
        "<p>Margen (" . (calcularMargenPorTipoCliente($_POST['tipo_cliente']) * 100) . "%): S/ " . number_format($margen, 2) . "</p>" .
        "<p id='precio-sin-igv'><strong>Precio final sin IGV: S/ " . number_format($total_sin_igv, 2) . "</strong></p>";

    // Bloque de IGV (inicialmente visible o no según parámetro)
    $display_igv = $con_igv ? 'block' : 'none';
    $resultado .= "<div id='bloque-igv' style='display: $display_igv'>
                    <p>IGV (18%): S/ " . number_format($igv, 2) . "</p>
                    <p id='precio-con-igv'><strong>Precio final con IGV: S/ " . number_format($total_con_igv, 2) . "</strong></p>
                  </div>";

    // Botón para alternar IGV con JavaScript
    $texto_boton = $con_igv ? 'Quitar IGV' : 'Añadir IGV (18%)';
    $resultado .= "<div class='mt-3'>
                    <input type='hidden' id='aplicar_igv' name='aplicar_igv' value='" . ($con_igv ? '1' : '0') . "'>
                    <button type='button' id='btn-igv' class='btn btn-info' onclick='toggleIGV()'>" . $texto_boton . "</button>
                  </div>";

    $resultado .= "</div>";

    return $resultado;
}


////
function calcularPrecioManoObraPegado($ancho_plancha, $alto_plancha, $cantidad, $tipo_cliente)
{
    $area_plancha = $ancho_plancha * $alto_plancha;
    $precio_base = 0;

    if ($area_plancha >= 2.88) {
        // Plancha completa o mayor
        $precio_base = 10.00;
    } elseif ($area_plancha < 1.0) {
        // Área pequeña
        $precio_base = 5.00;
    } else {
        // Área intermedia - cálculo proporcional
        // Entre 1 y 2.88 m², precio proporcional entre 5 y 10 soles
        $factor = ($area_plancha - 1.0) / (2.88 - 1.0);
        $precio_base = 5.00 + ($factor * 5.00);
    }

    return $precio_base * $cantidad;
}

function calcularPrecioCeltexFoam(
    $largoVinilMetros,
    $anchoVinilMetros,
    $precioBasePlancha,
    // $margenSeguridadPorcentaje = 10
) {
    $largoPlancha = 2.4; // metros
    $anchoPlancha = 1.2; // metros
    $costoFinal = 0;
    $areaTotalPlancha = $largoPlancha * $anchoPlancha; // m²
    echo "Área total de la plancha: " . number_format($areaTotalPlancha, 2) . " m²" . PHP_EOL;

    if ($areaTotalPlancha == 0) {
        return 0; // Evitar división por cero
    };
    if ($anchoVinilMetros > 1.5) {
        echo "El ancho del vinil es como maximo 1.5m." . PHP_EOL;
        return 0;
    }


    // Menor iguala 1,2 metros el metro lineal
    if ($anchoVinilMetros <= $anchoPlancha) {
        if ($anchoVinilMetros <= 0.6) {
            if ($largoVinilMetros > 1.2 && $largoVinilMetros <= 2.4) {
                $costoFinal = $precioBasePlancha / 2;
                echo "El costo es 1.2 24: " . number_format($costoFinal, 2) . " soles" . PHP_EOL;
                return $costoFinal;
            } else if ($largoVinilMetros > 2.4 && $largoVinilMetros <= 4.8) {
                $costoFinal = $precioBasePlancha;
                echo "El costo es 2.4 48: " . number_format($costoFinal, 2) . " soles" . PHP_EOL;
                return $costoFinal;
            }
        }
        if ($largoVinilMetros <= 1.2 && ($anchoVinilMetros > 0 && $anchoVinilMetros <= 1.2)) {
            $costoFinal = $precioBasePlancha / 2;
            echo "El costo es 1.2 12:" . number_format($precioBasePlancha / 2, 2) . " soles" . PHP_EOL;
            return $costoFinal;
        };
        if ($anchoVinilMetros > 0.6 && $anchoVinilMetros <= 1.2) {
            if ($largoVinilMetros > 1.2 && $largoVinilMetros <= 2.4) {
                $costoFinal = $precioBasePlancha;
                echo "El costo es 06 1.2 : " . number_format($costoFinal, 2) . " soles" . PHP_EOL;
                return $costoFinal;
            } else if ($largoVinilMetros > 2.4 && $largoVinilMetros <= 4.8) {
                $costoFinal = $precioBasePlancha * 2;
                echo "El costo es 06 2.4: " . number_format($costoFinal, 2) . " soles" . PHP_EOL;
                return $costoFinal;
            }
        }
    }

    if ($anchoVinilMetros > $anchoPlancha && $anchoVinilMetros <= 1.5) {


        if ($largoVinilMetros <= 1.2) {
            $costoFinal = $precioBasePlancha * 0.625;
            echo "El costo es 1.2 12 0625: " . number_format($costoFinal, 2) . " soles" . PHP_EOL;
            return $costoFinal;
        }

        if ($largoVinilMetros > 1.2 && $largoVinilMetros <= 1.8) {
            $costoFinal = $precioBasePlancha;
            echo "El costo es 1.2 18: " . number_format($costoFinal, 2) . " soles" . PHP_EOL;
            return $costoFinal;
        } elseif ($largoVinilMetros > 1.8 && $largoVinilMetros <= 2.7) {
            $costoFinal = $precioBasePlancha * 1.5;
            echo "El costo es 1.2 27: " . number_format($costoFinal, 2) . " soles" . PHP_EOL;
            return $costoFinal;
        } elseif ($largoVinilMetros > 2.7 && $largoVinilMetros <= 3) {
            $costoFinal = $precioBasePlancha * 1.75;
            echo "El costo es 1.2 30: " . number_format($costoFinal, 2) . " soles" . PHP_EOL;
            return $costoFinal;
        } else if ($largoVinilMetros > 3 && $largoVinilMetros <= 3.6) {
            $costoFinal = $precioBasePlancha * 2;
            echo "El costo es 1.2 36: " . number_format($costoFinal, 2) . " soles" . PHP_EOL;
            return $costoFinal;
        }
    };

    echo "El ancho del vinil no es compatible con las medidas de la plancha Celtex." . PHP_EOL;
    return $costoFinal;
}

// ************************************************************************************
// Cargar datos
$productos = obtenerProductos();
$clientes = obtenerClientes();
$productoSeleccionado = $_POST['producto'] ?? '';
// Capturar el ID del cliente de GET o POST, pero mantener el desplegable
$clienteSeleccionado = $_GET['id_cliente'] ?? $_POST['cliente'] ?? '';
$resultado = '';
$guardado_exitoso = false;
$cotizacion_id = null;
$tipo_cliente = $_POST['tipo_cliente'] ?? 'final_nuevo'; // Valor por defecto cambiado a final_nuevo
// ************************************************************************************



// Vaciar el carrito si se solicita
if (isset($_GET['vaciar_carrito'])) {
    $_SESSION['carrito'] = [];
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Eliminar un producto del carrito si se solicita
if (isset($_GET['eliminar_producto']) && isset($_SESSION['carrito'][$_GET['eliminar_producto']])) {
    unset($_SESSION['carrito'][$_GET['eliminar_producto']]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Finalizar y guardar la cotización completa
if (isset($_POST['finalizar_cotizacion']) && !empty($_SESSION['carrito']) && !empty($_POST['cliente_final'])) {
    $id_cliente = $_POST['cliente_final'];
    $detalles_cotizacion = [];
    $total_general = 0;

    // Aplicar IGV global si está marcado
    $con_igv_final = isset($_POST['aplicar_igv_final']) && $_POST['aplicar_igv_final'] == '1';

    // Preparar todos los productos del carrito para guardar
    foreach ($_SESSION['carrito'] as $item) {
        $precio_total = $item['total_sin_igv'];
        if ($con_igv_final) {
            $precio_total *= 1.18; // Aplicar IGV si corresponde
        }

        $detalles_cotizacion[] = [
            'id_producto' => $item['id_producto'],
            'cantidad' => $item['cantidad'],
            'precio_total' => $precio_total,
            'opciones' => $item['opciones'] ?? []
        ];

        $total_general += $precio_total;
    }

    // Guardar toda la cotización
    $cotizacion_id = guardarCotizacion($id_cliente, $total_general, $detalles_cotizacion);
    if ($cotizacion_id) {
        $guardado_exitoso = true;
        // Vaciar el carrito después de guardar correctamente
        $_SESSION['carrito'] = [];
    }
}



// Procesar formulario para agregar producto al carrito
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_al_carrito']) && isset($_POST['producto']) && !empty($_POST['producto']) && isset($_POST['cliente']) && !empty($_POST['cliente'])) {
    $id_producto = $_POST['producto'];
    $id_cliente = $_POST['cliente'];
    $nombre_producto = $productos[$id_producto] ?? "Producto desconocido";

    // Verificar si debe incluir IGV por defecto (para todos los productos)
    $con_igv = isset($_POST['aplicar_igv']) && $_POST['aplicar_igv'] == '1';

    // Generar un ID único para este item en el carrito
    $item_id = uniqid();
    $item_data = [
        'id_producto' => $id_producto,
        'nombre_producto' => $nombre_producto,
        'id_cliente' => $id_cliente,
        'con_igv' => $con_igv
    ];

    // Para GIGANTOGRAFIA (id_producto = 1)
    if ($id_producto == 1) {
        $ancho = isset($_POST["ancho"]) ? floatval($_POST["ancho"]) : 0;
        $largo = isset($_POST["largo"]) ? floatval($_POST["largo"]) : 0;
        $cantidad = isset($_POST["cantidad_gigantografia"]) ? intval($_POST["cantidad_gigantografia"]) : 1;
        if ($ancho < 1) {
            $ancho = 1;
        }
        if ($largo < 1) {
            $largo = 1;
        }
        $area1 = $ancho * $largo;
        $area = $area1 * $cantidad;

        // Obtener precio base por metro cuadrado
        $precioBase = obtenerPrecioProducto($id_producto, $cantidad, $tipo_cliente);
        // $precioUnitario = obtenerPrecioPorArea($id_producto, $area, $tipo_cliente);
        $tipo_cliente_simple = getTipoClienteBaseDatos($tipo_cliente);

        $precioUnitario = obtenerPrecioPorAreaNoDB($tipo_cliente_simple, $cantidad);
        $factor_ganancia = calcularMargenPorTipoCliente($tipo_cliente) + 1;
        $precioArea = $precioUnitario * $area * $factor_ganancia;

        $subtotal += $precioArea; // Acumular subtotal

        // Calcular adicionales por opciones seleccionadas
        $opciones_seleccionadas = isset($_POST['opciones']) ? $_POST['opciones'] : [];

        if (!is_array($opciones_seleccionadas)) {
            $opciones_seleccionadas = [$opciones_seleccionadas]; // Convertir a array si es string
        }

        $adicionales = 0;
        $opciones_detalle = [];

        foreach ($opciones_seleccionadas as $opcion_id) {
            $tipo_opcion = isset($_POST["opcion-{$opcion_id}_tipo"]) ? $_POST["opcion-{$opcion_id}_tipo"] : 'No';

            // Aplicar cálculo específico según la opción
            switch ($opcion_id) {
                case '5': // Termosellado
                case '12':
                    $adicional_actual = calcularPrecioTermosellado($opcion_id, $ancho, $largo, $tipo_opcion, $cantidad, $tipo_cliente);
                    $adicional_actual = $factor_ganancia * $adicional_actual; // Aplicar margen
                    $adicionales += $adicional_actual;
                    $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual];
                    break;

                case '6': // Pita y Tubo
                case '13':
                    $adicional_actual = calcularPrecioPitaYTubo($opcion_id, $ancho, $largo, $tipo_opcion, $cantidad, $tipo_cliente);
                    $adicional_actual = $factor_ganancia * $adicional_actual; // Aplicar margen
                    $adicionales += $adicional_actual;
                    $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual];
                    break;

                case '7':
                case '14':
                    $cantidad_ojales = isset($_POST["cantidad_ojales"]) ? intval($_POST["cantidad_ojales"]) : 0;
                    $adicional_actual = calcularPrecioOjales($opcion_id, $cantidad_ojales, $tipo_cliente);
                    $adicional_actual = $factor_ganancia * $adicional_actual; // Aplicar margen
                    $adicionales += $adicional_actual;
                    $opciones_detalle[] = ['id' => $opcion_id, 'cantidad' => $cantidad_ojales, 'valor' => $adicional_actual];
                    break;

                case '8':
                case '15': // Marco
                    $adicional_actual = calcularPrecioMarco($opcion_id, $ancho, $largo, $cantidad, $tipo_cliente);
                    $adicional_actual = $factor_ganancia * $adicional_actual; // Aplicar margen
                    $adicionales += $adicional_actual;
                    $opciones_detalle[] = ['id' => $opcion_id, 'valor' => $adicional_actual];
                    break;

                default:
                    $opcion_precio = obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                    $adicional_actual = 0;

                    if ($tipo_opcion == 'Ancho') {
                        $adicional_actual = 2 * $ancho * $opcion_precio;
                        $adicional_actual = $factor_ganancia * $adicional_actual; // Aplicar margen
                    } elseif ($tipo_opcion == 'Largo') {
                        $adicional_actual = 2 * $largo * $opcion_precio;
                        $adicional_actual = $factor_ganancia * $adicional_actual; // Aplicar margen
                    } elseif ($tipo_opcion == 'Ambos') {
                        $adicional_actual = 2 * ($ancho + $largo) * $opcion_precio;
                        $adicional_actual = $factor_ganancia * $adicional_actual; // Aplicar margen
                    }

                    $adicionales += $adicional_actual * $cantidad;
                    $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual * $cantidad];
            }
        }

        $subtotal += $adicionales;
        $total_sin_igv = $subtotal;
        $total_con_igv = $total_sin_igv * 1.18;

        // $subtotal += $adicionales;
        // $margen = $subtotal * calcularMargenPorTipoCliente($tipo_cliente);
        // $total_sin_igv = $subtotal + $margen;
        // $total_con_igv = $total_sin_igv * 1.18;

        // Datos adicionales para mostrar
        $datos_adicionales = [
            'Área' => number_format($area, 2) . " m²",
            'Cantidad' => $cantidad,
            'Precio Unitario' => "S/." . number_format($precioUnitario * $factor_ganancia, 2) . " x m²",
            'Precio Area' => "S/." . number_format($precioArea, 2) . " soles",
        ];

        // Agregar al carrito
        $item_data = array_merge($item_data, [
            'ancho' => $ancho,
            'largo' => $largo,
            'cantidad' => $cantidad,
            'area' => $area,
            'subtotal' => $subtotal,
            'margen' => $margen,
            'total_sin_igv' => $total_sin_igv,
            'total_con_igv' => $total_con_igv,
            'opciones' => $opciones_seleccionadas,
            'opciones_detalle' => $opciones_detalle,
            'datos_adicionales' => $datos_adicionales
        ]);
    }

    //********************************************************************************
    //************************************************************************** */ */
    // Para Vinil (id_producto = 2)
    elseif ($id_producto == 2) {
        $metro_lineal = isset($_POST['metro_lineal']) ? floatval($_POST['metro_lineal']) : 0;
        $cantidad = isset($_POST['cantidad_vinil']) ? intval($_POST['cantidad_vinil']) : 1; // Añade input para cantidad

        // Obtener precio unitario del vinil
        $precioUnitario = obtenerPrecioProducto($id_producto, $cantidad, $tipo_cliente);
        // $subtotal = $metro_lineal * $precioUnitario * $cantidad;
        $subtotal = 0;
        $factor_ganancia = calcularMargenPorTipoCliente($tipo_cliente) + 1;

        // Si hay opciones adicionales
        $opciones_seleccionadas = isset($_POST['opciones']) ? $_POST['opciones'] : [];
        if (!is_array($opciones_seleccionadas)) {
            $opciones_seleccionadas = [$opciones_seleccionadas]; // Convertir a array si es string
        }

        $adicionales = 0;
        $opciones_detalle = [];

        // Precio por metro lineal
        $precio_back = 0;
        $metro_lineal_imprentero = 0;
        $metro_lineal_final = 0;

        foreach ($opciones_seleccionadas as $opcion_id) {
            $tipo_opcion = isset($_POST["opcion-{$opcion_id}_tipo"]) ? $_POST["opcion-{$opcion_id}_tipo"] : 'No';

            switch ($opcion_id) {
                /////  F I N A L 
                ///////// V I N I L    B L A N C O ===
                // case '16': // Vinil Blanco Brillo Chino
                //     $opcion_precio = $precioUnitario + obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                //     $adicional_actual = $metro_lineal * $opcion_precio * $cantidad* $factor_ganancia;
                //     $adicionales += $adicional_actual;
                //     $precio_back = $adicional_actual;
                //     $metro_lineal_final = $opcion_precio * $factor_ganancia;
                //     $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual];
                //     break;
                // case '17': // Vinil Blanco Mate Chino
                //     $opcion_precio = $precioUnitario + obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                //     $adicional_actual = $metro_lineal * $opcion_precio * $cantidad * $factor_ganancia;
                //     $adicionales += $adicional_actual;
                //     $precio_back = $adicional_actual;
                //     $metro_lineal_final = $opcion_precio * $factor_ganancia;
                //     $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual];
                //     break;
                // case '22': // Vinil Blanco Brillo Arclad
                //     $opcion_precio = $precioUnitario + obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                //     $adicional_actual = $metro_lineal * $opcion_precio * $cantidad;
                //     $adicionales += $adicional_actual;
                //     $precio_back = $adicional_actual;
                //     $metro_lineal_final = $opcion_precio;
                //     $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual];
                //     break;
                // case '23': // Vinil Blanco Mate Arclad
                //     $opcion_precio = $precioUnitario + obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                //     $adicional_actual = $metro_lineal * $opcion_precio * $cantidad;
                //     $adicionales += $adicional_actual;
                //     $precio_back = $adicional_actual;
                //     $metro_lineal_final = $opcion_precio;
                //     $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual];
                //     break;
                // /////////////////// V I N I L    T R A N S P A R E N T E    
                // case '24': // Vinil Transparente Brillo Chino
                //     $opcion_precio = $precioUnitario + obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                //     $adicional_actual = $metro_lineal * $opcion_precio * $cantidad;
                //     $adicionales += $adicional_actual;
                //     $precio_back = $adicional_actual;
                //     $metro_lineal_final = $opcion_precio;
                //     $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual];
                //     break;
                // case '25': // Vinil Transparente Mate Chino
                //     $opcion_precio = $precioUnitario + obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                //     $adicional_actual = $metro_lineal * $opcion_precio * $cantidad;
                //     $adicionales += $adicional_actual;
                //     $precio_back = $adicional_actual;
                //     $metro_lineal_final = $opcion_precio;
                //     $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual];
                //     break;
                // case '26': // Vinil Transparente Brillo Arclad
                //     $opcion_precio = $precioUnitario + obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                //     $adicional_actual = $metro_lineal * $opcion_precio * $cantidad;
                //     $adicionales += $adicional_actual;
                //     $precio_back = $adicional_actual;
                //     $metro_lineal_final = $opcion_precio;
                //     $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual];
                //     break;
                case '16':
                case '17':
                case '22':
                case '23':
                case '24':
                case '25':
                case '26':
                case '27': // Vinil Transparente Mate Arclad
                    $opcion_precio = $precioUnitario + obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                    $adicional_actual = $metro_lineal * $opcion_precio * $cantidad * $factor_ganancia;
                    $adicionales += $adicional_actual;
                    $precio_back = $adicional_actual;
                    $metro_lineal_final = $opcion_precio * $factor_ganancia;
                    $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual];
                    break;

                /////   I M P R E N T E R O 
                // V I N I L    B L A N C O ===
                // case '32':
                //     $opcion_precio = $precioUnitario + obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                //     $adicional_actual = $metro_lineal * $opcion_precio * $cantidad;
                //     $adicionales += $adicional_actual;
                //     $precio_back = $adicional_actual;
                //     $metro_lineal_imprentero = $opcion_precio;
                //     $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual];
                //     break;
                // case '33':
                //     $opcion_precio = $precioUnitario + obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                //     $adicional_actual = $metro_lineal * $opcion_precio * $cantidad;
                //     $adicionales += $adicional_actual;
                //     $precio_back = $adicional_actual;
                //     $metro_lineal_imprentero = $opcion_precio;
                //     $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual];
                //     break;
                // case '34':
                //     $opcion_precio = $precioUnitario + obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                //     $adicional_actual = $metro_lineal * $opcion_precio * $cantidad;
                //     $adicionales += $adicional_actual;
                //     $precio_back = $adicional_actual;
                //     $metro_lineal_imprentero = $opcion_precio;
                //     $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual];
                //     break;
                // case '35':
                //     $opcion_precio = $precioUnitario + obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                //     $adicional_actual = $metro_lineal * $opcion_precio * $cantidad;
                //     $adicionales += $adicional_actual;
                //     $precio_back = $adicional_actual;
                //     $metro_lineal_imprentero = $opcion_precio;
                //     $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual];
                //     break;
                // ///  V I N I L   T R A N S P A R E N T E
                // case '36':
                //     $opcion_precio = $precioUnitario + obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                //     $adicional_actual = $metro_lineal * $opcion_precio * $cantidad;
                //     $adicionales += $adicional_actual;
                //     $precio_back = $adicional_actual;
                //     $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual];
                //     break;
                // case '37':
                //     $opcion_precio = $precioUnitario + obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                //     $adicional_actual = $metro_lineal * $opcion_precio * $cantidad;
                //     $adicionales += $adicional_actual;
                //     $precio_back = $adicional_actual;
                //     $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual];
                //     break;
                // case '38':
                //     $opcion_precio = $precioUnitario + obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                //     $adicional_actual = $metro_lineal * $opcion_precio * $cantidad;
                //     $adicionales += $adicional_actual;
                //     $precio_back = $adicional_actual;
                //     $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual];
                //     break;
                case '32':
                case '33':
                case '34':
                case '35':
                case '36':
                case '37':
                case '38':
                case '39':
                    $opcion_precio = $precioUnitario + obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                    $adicional_actual = $metro_lineal * $opcion_precio * $cantidad *  $factor_ganancia;
                    $adicionales += $adicional_actual;
                    $precio_back = $adicional_actual;
                    $metro_lineal_imprentero = $opcion_precio *  $factor_ganancia;
                    $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual];
                    break;
                // LAMINADO

                case '54': // Celtex
                case '55':
                case '56':
                case '57':
                    $opcion_precio = obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                    break;
                case '58': // Celtex
                case '59':
                case '60':
                case '61':
                    $opcion_precio = obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                    break;
                case '62':
                    $alto_plancha = isset($_POST["alto_plancha_mop"]) ? floatval($_POST["alto_plancha_mop"]) : 0;
                    $ancho_plancha = isset($_POST["ancho_plancha_mop"]) ? floatval($_POST["ancho_plancha_mop"]) : 0;
                    // Verifica si Celtex está seleccionado (id 54 o 61)
                    $materialSeleccionado = false;
                    $material_id = null;
                    $material_ids = ['54', '55', '56', '57', '58', '59', '60', '61'];
                    if (isset($_POST['opciones']) && is_array($_POST['opciones'])) {
                        foreach ($_POST['opciones'] as $op) {
                            if (in_array($op, $material_ids)) {
                                $materialSeleccionado = true;
                                $material_id = $op;
                                break;
                            }
                        }
                    }

                    if ($ancho_plancha > 0 && $alto_plancha > 0 && $materialSeleccionado) {
                        $precioBaseMaterial = obtenerPrecioOpcion($material_id, $id_producto, $tipo_cliente);
                        $valor_celtexfoam = calcularPrecioCeltexFoam($alto_plancha, $ancho_plancha, $precioBaseMaterial);
                        $valor_mano_obra = calcularPrecioManoObraPegado($ancho_plancha, $alto_plancha, $cantidad, $tipo_cliente);

                        $cantidadPlanchas = number_format($valor_celtexfoam / $precioBaseMaterial, 2);

                        $adicionales += ($valor_celtexfoam + $valor_mano_obra) * $factor_ganancia;

                        // Guarda Celtex/Foam como una opción separada
                        $opciones_detalle[] = [
                            'id' => $material_id,
                            'tipo' => 'Material',
                            'cantidad_planchas' => ' (' . $cantidadPlanchas . ' planchas)',
                            'valor' => $valor_celtexfoam *  $factor_ganancia
                        ];
                        // Guarda Mano de Obra Pegado como otra opción separada
                        $opciones_detalle[] = [
                            'id' => $opcion_id, // id 62 o 63
                            'tipo' => 'Mano de Obra Pegado',
                            'valor' => $valor_mano_obra *  $factor_ganancia
                        ];
                    }
                    break;

                default:
                    $opcion_precio = obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
                    $adicional_actual = $metro_lineal * $opcion_precio * $cantidad * $factor_ganancia;
                    $adicionales += $adicional_actual;
                    $opciones_detalle[] = ['id' => $opcion_id, 'tipo' => $tipo_opcion, 'valor' => $adicional_actual];
            }
        }

        $subtotal += $adicionales;
        $total_sin_igv = $subtotal;
        $total_con_igv = $total_sin_igv * 1.18;
        // $subtotal += $adicionales;
        // $margen = $subtotal * calcularMargenPorTipoCliente($tipo_cliente);
        // $total_sin_igv = $subtotal + $margen;
        // $total_con_igv = $total_sin_igv * 1.18;

        // Datos adicionales para mostrar
        if ($tipo_cliente == 'imprentero_nuevo'  || $tipo_cliente == 'imprentero_frecuente') {
            $datos_adicionales = [
                'Cantidad' => $cantidad,
                'Precio Metro Lineal' => "S/." . number_format($metro_lineal_imprentero, 2) . " x m",
                'Metro Lineal' => number_format($metro_lineal, 2) . " m",
                'Precio Total' => "S/." . number_format($precio_back, 2) . " soles"
            ];
        } else {
            $datos_adicionales = [
                'Cantidad' => $cantidad,
                'Precio Metro Lineal' => "S/." . number_format($metro_lineal_final, 2) . " x m",
                'Metro Lineal' => number_format($metro_lineal, 2) . " m",
                'Precio Total' => "S/." . number_format($precio_back , 2) . " soles"
            ];
        }
        // Agregar al carrito
        $item_data = array_merge($item_data, [
            'metro_lineal' => $metro_lineal,
            'cantidad' => $cantidad,
            'subtotal' => $subtotal,
            'margen' => $margen,
            'total_sin_igv' => $total_sin_igv,
            'total_con_igv' => $total_con_igv,
            'opciones' => $opciones_seleccionadas,
            'opciones_detalle' => $opciones_detalle,
            'datos_adicionales' => $datos_adicionales
        ]);
    }
    // // Para Lapicero (id_producto = 3)
    // elseif ($id_producto == 3) {
    //     $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 0;

    //     // Obtener precio unitario según cantidad
    //     $precioUnitario = obtenerPrecioProducto($id_producto, $cantidad, $tipo_cliente);
    //     $subtotal = $cantidad * $precioUnitario;

    //     // Si hay opciones adicionales
    //     $opciones_seleccionadas = isset($_POST['opciones']) ? $_POST['opciones'] : [];
    //     if (!is_array($opciones_seleccionadas)) {
    //         $opciones_seleccionadas = [$opciones_seleccionadas]; // Convertir a array si es string
    //     }

    //     $opciones_detalle = [];
    //     foreach ($opciones_seleccionadas as $opcion_id) {
    //         // Usar directamente el ID de la opción desde el POST
    //         $opcion_precio = obtenerPrecioOpcion($opcion_id, $id_producto, $tipo_cliente);
    //         $adicional_actual = $cantidad * $opcion_precio;
    //         $subtotal += $adicional_actual;
    //         $opciones_detalle[] = ['id' => $opcion_id, 'valor' => $adicional_actual];
    //     }

    //     $margen = $subtotal * 0.3;
    //     $total_sin_igv = $subtotal + $margen;
    //     $total_con_igv = $total_sin_igv * 1.18;

    //     // Datos adicionales para mostrar
    //     $datos_adicionales = [
    //         'Cantidad' => $cantidad
    //     ];

    //     // Agregar al carrito
    //     $item_data = array_merge($item_data, [
    //         'cantidad' => $cantidad,
    //         'subtotal' => $subtotal,
    //         'margen' => $margen,
    //         'total_sin_igv' => $total_sin_igv,
    //         'total_con_igv' => $total_con_igv,
    //         'opciones' => $opciones_seleccionadas,
    //         'opciones_detalle' => $opciones_detalle,
    //         'datos_adicionales' => $datos_adicionales
    //     ]);
    // }

    // Agregar el item al carrito
    $_SESSION['carrito'][$item_id] = $item_data;

    // Redirigir para evitar reenvío del formulario
    header("Location: " . $_SERVER['PHP_SELF'] . "?agregado=true");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Sistema de Cotización</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            padding: 20px;
        }

        .form-section {
            margin-top: 15px;
        }

        .opciones-container {
            margin-top: 20px;
        }

        .option-type-select {
            margin-left: 10px;
            display: none;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .carrito-item {
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
        }

        .carrito-container {
            max-height: 500px;
            overflow-y: auto;
            margin-bottom: 20px;
            padding: 10px;
        }

        .total-carrito {
            font-weight: bold;
            font-size: 1.2em;
            margin-top: 10px;
            text-align: right;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }

        .tipo-cliente-selector {
            margin-bottom: 15px;
        }

        .nav-tabs {
            margin-bottom: 20px;
        }

        .laser-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 80px;
        }

        .laser {
            display: flex;
            justify-content: center;
            font-style: italic;
            letter-spacing: -0.05em;
            gap: 0.25em;
            width: 9.5em;
            height: auto;
            font-size: 2.25em;
            font-weight: bold;
            color: #fff;
            background-color: #ff0000;
        }

        .laser-color {
            color: yellow;
        }

        .laser-veloz {
            font-weight: bold;
            background: repeating-linear-gradient(#fff,
                    #fff 2px,
                    #ff0000 2px,
                    #ff0000 3px);
            background-size: 100% 100%;
            /* Asegura que el gradiente ocupe todo el texto */
            -webkit-background-clip: text;
            /* Aplica el fondo al texto */
            background-clip: text;
            /* Estándar */
            -webkit-text-fill-color: transparent;
            /* Hace que el texto sea transparente para que se vea el fondo */
        }

        .info_carrito {
            margin-bottom: 0.5em;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="laser-container">
            <div class="laser">
                <span>LASER</span>
                <span class="laser-color">COLOR</span>
                <span class="laser-veloz">VELOZ</span>
            </div>

        </div>
        <h2 class="mb-4">Sistema de Cotización</h1>

            <?php if ($guardado_exitoso): ?>
                <div class="success-message">
                    <strong>¡Cotización guardada con éxito!</strong> ID de cotización: <?php echo $cotizacion_id; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['agregado']) && $_GET['agregado'] == 'true'): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i> Producto agregado al carrito correctamente.
                </div>
            <?php endif; ?>

            <!-- Pestañas para navegar entre "Agregar Productos" y "Ver Carrito" -->
            <ul class="nav nav-tabs" id="cotizacionTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="productos-tab" data-bs-toggle="tab"
                        data-bs-target="#productos" type="button" role="tab"
                        aria-controls="productos" aria-selected="true">
                        <i class="bi bi-plus-circle"></i> Agregar Productos
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="carrito-tab" data-bs-toggle="tab"
                        data-bs-target="#carrito" type="button" role="tab"
                        aria-controls="carrito" aria-selected="false">
                        <i class="bi bi-cart"></i> Ver Carrito
                        <span class="badge bg-primary"><?php echo count($_SESSION['carrito']); ?></span>
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="cotizacionTabsContent">
                <!-- Pestaña de Agregar Productos -->
                <div class="tab-pane fade show active" id="productos" role="tabpanel" aria-labelledby="productos-tab">
                    <form method="POST" class="mb-4">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="cliente" class="form-label">Seleccione un cliente:</label>
                                <select id="cliente" name="cliente" class="form-select" required>
                                    <option value="">-- Seleccionar Cliente --</option>
                                    <?php foreach ($clientes as $id => $nombre): ?>
                                        <option value="<?php echo $id; ?>" <?php echo ($id == $clienteSeleccionado) ? 'selected' : ''; ?>><?php echo $nombre; ?></option>
                                        <?php $clienteSeleccionado = $_GET['id_cliente'] ?? $_POST['cliente'] ?? $_SESSION['cliente_seleccionado'] ?? ''; ?>
                                    <?php endforeach; ?>
                                </select>
                                <div class="mt-2">
                                    <a href="registro_clientes.php" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-person-plus"></i> Nuevo Cliente
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="tipo-cliente-selector mt-4">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="tipo_cliente" id="tipo_final_nuevo" value="final_nuevo" <?php echo ($tipo_cliente == 'final_nuevo' || !$tipo_cliente) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="tipo_final_nuevo">Cliente Final Nuevo</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="tipo_cliente" id="tipo_final_frecuente" value="final_frecuente" <?php echo ($tipo_cliente == 'final_frecuente') ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="tipo_final_frecuente">Cliente Final Frecuente</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="tipo_cliente" id="tipo_imprentero_nuevo" value="imprentero_nuevo" <?php echo ($tipo_cliente == 'imprentero_nuevo') ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="tipo_imprentero_nuevo">Cliente Imprentero Nuevo</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="tipo_cliente" id="tipo_imprentero_frecuente" value="imprentero_frecuente" <?php echo ($tipo_cliente == 'imprentero_frecuente') ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="tipo_imprentero_frecuente">Cliente Imprentero Frecuente</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="producto" class="form-label">Seleccione un producto:</label>
                            <select id="producto" name="producto" class="form-select" onchange="mostrarFormulario()" required>
                                <option value="">-- Seleccionar Producto --</option>
                                <?php foreach ($productos as $id => $nombre): ?>
                                    <option value="<?php echo $id; ?>" <?php echo ($id == $productoSeleccionado) ? 'selected' : ''; ?>><?php echo $nombre; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Formulario para Gigantografía (id=1) -->
                        <div id="form-1" class="producto-form form-section" style="display:none;">
                            <h3>Gigantografía</h3>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="ancho" class="form-label">Ancho (m):</label>
                                    <input type="number" id="ancho" name="ancho" class="form-control" step="0.01" min="0.01" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="largo" class="form-label">Alto (m):</label>
                                    <input type="number" id="largo" name="largo" class="form-control" step="0.01" min="0.01" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="cantidad_gigantografia" class="form-label">Cantidad:</label>
                                    <input type="number" id="cantidad_gigantografia" name="cantidad_gigantografia" class="form-control" min="1" value="1" required>
                                </div>
                            </div>
                        </div>

                        <!-- Formulario para Vinil (id=2) -->
                        <div id="form-2" class="producto-form form-section" style="display:none;">
                            <h3>Vinil</h3>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="metro_lineal" class="form-label">Metro Lineal:</label>
                                    <input type="number" id="metro_lineal" name="metro_lineal" class="form-control" step="0.1" min="0.1" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cantidad_vinil" class="form-label">Cantidad:</label>
                                    <input type="number" id="cantidad_vinil" name="cantidad_vinil" class="form-control" min="1" value="1" required>
                                </div>
                            </div>
                        </div>

                        <!-- Formulario para Lapicero (id=3) -->
                        <div id="form-3" class="producto-form form-section" style="display:none;">
                            <h3>Lapicero</h3>
                            <div class="mb-3">
                                <label for="cantidad-lapicero" class="form-label">Cantidad:</label>
                                <input type="number" id="cantidad-lapicero" name="cantidad" class="form-control" min="1" required>
                            </div>
                        </div>

                        <!-- Opciones adicionales -->
                        <div id="opciones-container" class="opciones-container" style="display:none;">
                            <h3>Opciones</h3>
                            <div id="opciones-dinamicas">
                                <!-- Aquí se cargarán dinámicamente las opciones -->
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="aplicar_igv" name="aplicar_igv" value="1" <?php echo (isset($_POST['aplicar_igv']) && $_POST['aplicar_igv'] == '1') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="aplicar_igv">
                                Aplicar IGV (18%)
                            </label>
                        </div>

                        <div class="mt-4">
                            <button type="submit" name="agregar_al_carrito" class="btn btn-primary">
                                <i class="bi bi-cart-plus"></i> Agregar al Carrito
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-house"></i> Inicio
                            </a>
                            <a href="resumen_cotizacion.php" class="btn btn-info">
                                <i class="bi bi-file-earmark-text"></i> Ver Resumen
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Pestaña de Ver Carrito -->
                <div class="tab-pane fade" id="carrito" role="tabpanel" aria-labelledby="carrito-tab">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h3 class="mb-0"><i class="bi bi-cart4"></i> Carrito de Cotización</h3>
                        </div>
                        <div class="card-body">
                            <?php include 'carrito_view.php'; // Incluir vista parcial del carrito 
                            ?>
                        </div>
                    </div>
                </div>
            </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar si hay un producto seleccionado
            const productoSeleccionado = document.getElementById('producto').value;
            if (productoSeleccionado) {
                mostrarFormulario();
            }

            // Si hay mensajes de éxito, mostrar la pestaña del carrito automáticamente
            <?php if (isset($_GET['agregado']) && $_GET['agregado'] == 'true'): ?>
                document.getElementById('carrito-tab').click();
            <?php endif; ?>


            // Actualizar opciones cuando cambia el tipo de cliente
            const tipoClienteRadios = document.querySelectorAll('input[name="tipo_cliente"]');
            tipoClienteRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    const productoActual = document.getElementById('producto').value;
                    if (productoActual) {
                        console.log('Cambiando tipo de cliente a:', this.value);
                        cargarOpciones(productoActual, this.value);
                    }
                    mostrarFormulario();
                });
            });
        });

        function seleccionarUnicoVinilBlanco(checkbox) {
            // Desmarcar todos los del grupo excepto el actual
            document.querySelectorAll('.vinil-blanco-grupo').forEach(cb => {
                if (cb !== checkbox) cb.checked = false;
            });
        }

        function seleccionarUnicoLaminado(checkbox) {
            // Desmarcar todos los del grupo excepto el actual
            document.querySelectorAll('.laminado').forEach(cb => {
                if (cb !== checkbox) cb.checked = false;
            });
        }

        function seleccionarUnicoCeltexFoam(checkbox) {
            // Desmarcar todos los del grupo excepto el actual
            document.querySelectorAll('.celtexfoam').forEach(cb => {
                if (cb !== checkbox) cb.checked = false;
            });
        }

        // function seleccionarUnicoFoam(checkbox) {
        //     // Desmarcar todos los del grupo excepto el actual
        //     document.querySelectorAll('.foam').forEach(cb => {
        //         if (cb !== checkbox) cb.checked = false;
        //     });
        // }



        function mostrarFormulario() {
            const producto = document.getElementById("producto").value;
            const tipoCliente = document.querySelector('input[name="tipo_cliente"]:checked').value;

            // Ocultar todos los formularios
            document.querySelectorAll('.producto-form').forEach(form => {
                form.style.display = "none";
                form.querySelectorAll("input, select").forEach(el => {
                    el.disabled = true;
                });
            });

            // Ocultar contenedor de opciones
            document.getElementById('opciones-container').style.display = "none";

            // Mostrar el formulario correspondiente al producto seleccionado
            if (producto) {
                const formToShow = document.getElementById("form-" + producto);
                if (formToShow) {
                    formToShow.style.display = "block";
                    formToShow.querySelectorAll("input, select").forEach(el => {
                        el.disabled = false;
                    });

                    // Cargar opciones para el producto seleccionado
                    cargarOpciones(producto, tipoCliente);

                    // --- BLOQUE NUEVO ---
                    // Si es Vinil y cliente imprentero, setear cantidad mínima a 19
                    if (producto == "2" && (tipoCliente === "imprentero_nuevo" || tipoCliente === "imprentero_frecuente")) {
                        const cantidadVinil = document.getElementById("cantidad_vinil");
                        if (cantidadVinil) {
                            cantidadVinil.min = 19;
                            if (parseInt(cantidadVinil.value) < 19) {
                                cantidadVinil.value = 19;
                            }
                        }
                    };
                    if (producto == "2" && (tipoCliente === "final_nuevo" || tipoCliente === "final_frecuente")) {
                        // Si es otro tipo de cliente, restaurar mínimo a 1
                        const cantidadVinil = document.getElementById("cantidad_vinil");
                        if (cantidadVinil) {
                            cantidadVinil.min = 1;
                            cantidadVinil.value = 1;

                        }
                    }
                    // --- FIN BLOQUE NUEVO ---
                }
            }
        }

        function cargarOpciones(productoId, tipoCliente) {
            tipoCliente = tipoCliente || document.querySelector('input[name="tipo_cliente"]:checked').value;

            const selectedOptions = [];
            const currentOptions = document.querySelectorAll('input[name="opciones[]"]:checked');
            currentOptions.forEach(option => {
                const optionId = option.value;
                selectedOptions.push({
                    id: optionId,
                    tipo: document.querySelector(`select[name="opcion-${optionId}_tipo"]`) ?
                        document.querySelector(`select[name="opcion-${optionId}_tipo"]`).value : null,
                    cantidadOjales: document.getElementById('cantidad_ojales') ?
                        document.getElementById('cantidad_ojales').value : null
                });
            });
            console.log('cliente:', tipoCliente); // Verificar la estructura de datos recibida

            // Realizar una solicitud AJAX para obtener las opciones del producto
            fetch('obtener_opciones.php?producto=' + productoId + '&tipo_cliente=' + tipoCliente)
                .then(response => response.json())
                .then(data => {
                    console.log('Opciones recibidas:', data); // Verificar la estructura de datos recibida
                    if (data.error) {
                        console.error('Error:', data.error);
                        return;
                    }

                    const opcionesContainer = document.getElementById('opciones-dinamicas');
                    opcionesContainer.innerHTML = ''; // Limpiar opciones anteriores

                    // Si hay opciones, mostrar el contenedor
                    if (Object.keys(data.opciones).length > 0) {
                        document.getElementById('opciones-container').style.display = "block";

                        let vinilBlancoMostrado = false;
                        let vinilTransparenteMostrado = false;
                        let laminadoChinoMostrado = false;
                        let laminadoArcladMostrado = false;
                        let celtexMostrado = false;
                        let foamMostrado = false;
                        // Generar HTML para cada opción
                        Object.entries(data.opciones).forEach(([id, descripcion]) => {
                            const precioOpcion = data.precios[id] || '0.00';
                            let opcionHTML = '';

                            if (productoId == 1) { // Gigantografía
                                if (id == 8 || id == 15) { // Marco (ID 8) - sin selector de tipo porque siempre es para todo el perímetro
                                    opcionHTML = `
                                    <div class="mb-3 option-item">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="opcion-${id}" 
                                                   name="opciones[]" value="${id}">
                                            <label class="form-check-label" for="opcion-${id}">${descripcion} (S/ ${precioOpcion} por metro)</label>
                                            <small class="form-text text-muted">Se aplica a todo el perímetro.</small>
                                        </div>
                                    </div>
                                `;
                                } else if (id == 7 || id == 14) { // Ojales (ID 7) - con input para cantidad en lugar de selector
                                    opcionHTML = `
                                    <div class="mb-3 option-item">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="opcion-${id}" 
                                                   name="opciones[]" value="${id}" 
                                                   onchange="toggleOjalesInput('opcion-${id}')">
                                            <label class="form-check-label" for="opcion-${id}">${descripcion} (S/ ${precioOpcion} por ojal)</label>
                                        </div>
                                        <div id="opcion-${id}_input_container" style="display:none; margin-top:10px;">
                                            <label for="cantidad_ojales" class="form-label">Cantidad de ojales:</label>
                                            <input type="number" id="cantidad_ojales" name="cantidad_ojales" 
                                                   class="form-control" min="1" value="4">
                                            <small class="form-text text-muted">Introduce el número exacto de ojales que necesitas.</small>
                                        </div>
                                    </div>
                                `;
                                } else {
                                    // Crear opción con selector de tipo (ancho, largo, ambos) para otras opciones
                                    opcionHTML = `
                                    <div class="mb-3 option-item">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="opcion-${id}" 
                                                   name="opciones[]" value="${id}" 
                                                   onchange="toggleSelect('opcion-${id}')">
                                            <label class="form-check-label" for="opcion-${id}">${descripcion} (S/ ${precioOpcion})</label>
                                        </div>
                                        <select name="opcion-${id}_tipo" id="opcion-${id}_select" class="form-select option-type-select">
                                            <option value="No">No</option>
                                            <option value="Ancho">Ancho</option>
                                            <option value="Largo">Alto</option>
                                            <option value="Ambos">Ambos</option>
                                        </select>
                                    </div>
                                `;
                                }
                            };
                            if (productoId == 2) {
                                if (id == 16 || id == 17 || id == 22 || id == 23) {
                                    if (!vinilBlancoMostrado) {
                                        opcionesContainer.innerHTML += `<h5>Vinil blanco</h5>`;
                                        vinilBlancoMostrado = true;
                                    }
                                    opcionHTML = `
                                <div class="mb-3 option-item">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input vinil-blanco-grupo" id="opcion-${id}" name="opciones[]" value="${id}" onclick="seleccionarUnicoVinilBlanco(this)">
                                        <label class="form-check-label" for="opcion-${id}">${descripcion} </label>
                                    </div>
                                </div>
                            `;
                                };
                                if ((id == 24 || id == 25 || id == 26 || id == 27)) {
                                    if (!vinilTransparenteMostrado) {
                                        opcionesContainer.innerHTML += `
                                    <h5>Vinil transparente</h5>`;
                                        vinilTransparenteMostrado = true;
                                    }
                                    opcionHTML = `
                                <div class="mb-3 option-item">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input vinil-blanco-grupo" id="opcion-${id}" name="opciones[]" value="${id}" onclick="seleccionarUnicoVinilBlanco(this)">
                                        <label class="form-check-label" for="opcion-${id}">${descripcion} </label>
                                    </div>
                                </div>
                            `;
                                };
                                if (id == 32 || id == 33 || id == 34 || id == 35) {
                                    if (!vinilBlancoMostrado) {
                                        opcionesContainer.innerHTML += `<h5>Vinil blanco</h5>`;
                                        vinilBlancoMostrado = true;
                                    }
                                    opcionHTML = `
                                <div class="mb-3 option-item">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input vinil-blanco-grupo" id="opcion-${id}" name="opciones[]" value="${id}" onclick="seleccionarUnicoVinilBlanco(this)">
                                        <label class="form-check-label" for="opcion-${id}">${descripcion} </label>
                                    </div>
                                </div>
                            `;
                                };
                                if ((id == 36 || id == 37 || id == 38 || id == 39)) {
                                    if (!vinilTransparenteMostrado) {
                                        opcionesContainer.innerHTML += `
                                    <h5>Vinil transparente</h5>`;
                                        vinilTransparenteMostrado = true;
                                    }
                                    opcionHTML = `
                                <div class="mb-3 option-item">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input vinil-blanco-grupo" id="opcion-${id}" name="opciones[]" value="${id}" onclick="seleccionarUnicoVinilBlanco(this)">
                                        <label class="form-check-label" for="opcion-${id}">${descripcion}</label>
                                    </div>
                                </div>
                            `;
                                };
                                if (id == 46 || id == 47 || id == 48 || id == 49) {
                                    if (!laminadoChinoMostrado) {
                                        opcionesContainer.innerHTML += `<h5>Laminado Chino</h5>`;
                                        laminadoChinoMostrado = true;
                                    }
                                    opcionHTML = `
                                <div class="mb-3 option-item">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input laminado" id="opcion-${id}" name="opciones[]" value="${id}" onclick="seleccionarUnicoLaminado(this)">
                                        <label class="form-check-label" for="opcion-${id}">${descripcion}</label>
                                    </div>
                                </div> `;


                                };
                                if (id == 50 || id == 51 || id == 52 || id == 53) {
                                    if (!laminadoArcladMostrado) {
                                        opcionesContainer.innerHTML += `<h5>Laminado Arclad</h5>`;
                                        laminadoArcladMostrado = true;
                                    }
                                    opcionHTML = `
                                <div class="mb-3 option-item">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input laminado" id="opcion-${id}" name="opciones[]" value="${id}" onclick="seleccionarUnicoLaminado(this)">
                                        <label class="form-check-label" for="opcion-${id}">${descripcion}</label>
                                    </div>
                                </div> `;
                                };
                                if (id == 54 || id == 55 || id == 56 || id == 57) {
                                    if (!celtexMostrado) {
                                        opcionesContainer.innerHTML += `<h5>Celtex</h5>`;
                                        celtexMostrado = true;
                                    }
                                    opcionHTML = `
                                <div class="mb-3 option-item">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input celtexfoam" id="opcion-${id}" name="opciones[]" value="${id}" onclick="seleccionarUnicoCeltexFoam(this)">
                                        <label class="form-check-label" for="opcion-${id}">${descripcion}</label>
                                    </div>
                                </div> `;
                                };
                                if (id == 58 || id == 59 || id == 60 || id == 61) {
                                    if (!foamMostrado) {
                                        opcionesContainer.innerHTML += `<h5>Foam</h5>`;
                                        foamMostrado = true;
                                    }
                                    opcionHTML = `
                                <div class="mb-3 option-item">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input celtexfoam" id="opcion-${id}" name="opciones[]" value="${id}" onclick="seleccionarUnicoCeltexFoam(this)">
                                        <label class="form-check-label" for="opcion-${id}">${descripcion}</label>
                                    </div>
                                </div> `;
                                };
                                if (id == 62 || id == 63) {
                                    opcionesContainer.innerHTML += `<hr>`;

                                    opcionHTML = `
                                                <div class="mb-3 option-item">
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" id="opcion-${id}" 
                                                            name="opciones[]" value="${id}" 
                                                            onchange="toggleManoObraInput('opcion-${id}')">
                                                        <label class="form-check-label" for="opcion-${id}">${descripcion}</label>
                                                        <small class="form-text text-muted">Precio según área de plancha: ≥2.88m² = S/10, <1m² = S/5</small>
                                                    </div>
                                                    <div id="opcion-${id}_input_container" style="display:none; margin-top:10px;">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                            <div class="col-md-6">
                                                                <label for="alto_plancha_mop" class="form-label">Metro lineal(m):</label>
                                                                <input type="number" id="alto_plancha_mop" name="alto_plancha_mop" 
                                                                    class="form-control" step="0.01" min="0.01" ">
                                                            </div>
                                                                <label for="ancho_plancha_mop" class="form-label">Ancho plancha (m):</label>
                                                                <input type="number" id="ancho_plancha_mop" name="ancho_plancha_mop" 
                                                                    class="form-control" step="0.01" min="0.01" max="1.5">
                                                            </div>
                                                        </div>
                                                        <div class="mt-2">
                                                            <small class="text-info">Área calculada: <span id="area_plancha_display">0.00</span> m²</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            `;
                                }
                            }

                            opcionesContainer.innerHTML += opcionHTML;
                        });
                    }
                })
                .catch(error => {
                    console.error('Error en la solicitud AJAX:', error);
                });
        }

        // Función para mostrar/ocultar el selector de tipo de opción
        function toggleSelect(id) {
            const checkbox = document.getElementById(id);
            const select = document.getElementById(id + '_select');

            if (checkbox && select) {
                if (checkbox.checked) {
                    select.style.display = 'block';
                    select.value = 'Ancho'; // Valor predeterminado
                } else {
                    select.style.display = 'none';
                    select.value = 'No';
                }
            }
        }

        function toggleOjalesInput(id) {
            const checkbox = document.getElementById(id);
            const inputContainer = document.getElementById(id + '_input_container');

            if (checkbox && inputContainer) {
                if (checkbox.checked) {
                    inputContainer.style.display = 'block';
                } else {
                    inputContainer.style.display = 'none';
                }
            }
        }

        // Función para alternar IGV
        function toggleIGV() {
            // Obtener elementos del DOM
            const precioSinIGV = document.getElementById('precio-sin-igv');
            const bloqueIGV = document.getElementById('bloque-igv');
            const precioConIGV = document.getElementById('precio-con-igv');
            const btnIGV = document.getElementById('btn-igv');
            const inputIGV = document.getElementById('aplicar_igv');

            // Verificar si estamos aplicando IGV actualmente
            const aplicarIGV = inputIGV.value === '1';

            if (aplicarIGV) {
                // Quitamos el IGV
                inputIGV.value = '0';
                btnIGV.textContent = 'Añadir IGV (18%)';

                // Ocultar bloque IGV y precio con IGV
                if (bloqueIGV) bloqueIGV.style.display = 'none';
                if (precioConIGV) precioConIGV.style.display = 'none';

                // Asegurarnos que el precio sin IGV sea visible
                if (precioSinIGV) precioSinIGV.style.display = 'block';
            } else {
                // Añadimos el IGV
                inputIGV.value = '1';
                btnIGV.textContent = 'Quitar IGV';

                // Mostrar bloque IGV y precio con IGV
                if (bloqueIGV) bloqueIGV.style.display = 'block';
                if (precioConIGV) precioConIGV.style.display = 'block';

                // Mantener visible el precio sin IGV también
                if (precioSinIGV) precioSinIGV.style.display = 'block';
            }

            return false; // Evitar el envío del formulario
        }

        // function toggleManoObraInput(checkboxId) {
        //     const checkbox = document.getElementById(checkboxId);
        //     const container = document.getElementById(checkboxId + '_input_container');


        //     if (checkbox.checked) {
        //         container.style.display = 'block';

        //         // Agregar event listeners para calcular área en tiempo real
        //         const anchoInput = document.getElementById('ancho_plancha_mop');
        //         const altoInput = document.getElementById('alto_plancha_mop');
        //         const areaDisplay = document.getElementById('area_plancha_display');
        //         const metroLinealInput = document.getElementById('metro_lineal');

        //         function actualizarArea() {
        //             const ancho = parseFloat(anchoInput.value) || 0;
        //             const alto = parseFloat(altoInput.value) || 0;
        //             const area = ancho * alto;
        //             areaDisplay.textContent = area.toFixed(2);
        //         }

        //         anchoInput.addEventListener('input', actualizarArea);
        //         altoInput.addEventListener('input', actualizarArea);

        //     } else {
        //         container.style.display = 'none';
        //         // Limpiar valores
        //         document.getElementById('ancho_plancha_mop').value = '';
        //         document.getElementById('alto_plancha_mop').value = '';
        //         document.getElementById('area_plancha_display').textContent = '0.00';
        //     }
        // }
        function toggleManoObraInput(checkboxId) {
            const checkbox = document.getElementById(checkboxId);
            const container = document.getElementById(checkboxId + '_input_container');

            if (checkbox.checked) {
                container.style.display = 'block';

                // Espera a que el DOM esté listo (por si el HTML es dinámico)
                setTimeout(() => {
                    const anchoInput = document.getElementById('ancho_plancha_mop');
                    const altoInput = document.getElementById('alto_plancha_mop');
                    const areaDisplay = document.getElementById('area_plancha_display');
                    const metroLinealInput = document.getElementById('metro_lineal');

                    function actualizarArea() {
                        const ancho = parseFloat(anchoInput.value) || 0;
                        const alto = parseFloat(altoInput.value) || 0;
                        const area = ancho * alto;
                        areaDisplay.textContent = area.toFixed(2);
                    }

                    // Copiar el valor de metro_lineal al altoInput al activar el checkbox
                    if (metroLinealInput && altoInput) {
                        altoInput.value = metroLinealInput.value;
                    }

                    // Listeners para recalcular área
                    if (anchoInput && altoInput && areaDisplay) {
                        anchoInput.addEventListener('input', actualizarArea);
                        altoInput.addEventListener('input', actualizarArea);
                        actualizarArea();
                    }

                    // Si el usuario cambia metro_lineal después, también actualizar altoInput y área
                    if (metroLinealInput && altoInput && areaDisplay) {
                        metroLinealInput.addEventListener('input', function() {
                            altoInput.value = metroLinealInput.value;
                            actualizarArea();
                        });
                    }
                }, 10);

            } else {
                container.style.display = 'none';
                const anchoInput = document.getElementById('ancho_plancha_mop');
                const altoInput = document.getElementById('alto_plancha_mop');
                const areaDisplay = document.getElementById('area_plancha_display');
                if (anchoInput) anchoInput.value = '';
                if (altoInput) altoInput.value = '';
                if (areaDisplay) areaDisplay.textContent = '0.00';
            }
        }
    </script>
</body>

</html>