"<?php
/**  Calcula el precio del Celtex para un trabajo de vinil específico.
 *
 * @param float $anchoPlanchaCeltexMetros Ancho de la plancha de Celtex en metros.
 * @param float $largoPlanchaCeltexMetros Largo de la plancha de Celtex en metros.
 * @param float $precioBasePlancha Precio base de la plancha de Celtex completa.
 * @param float $anchoTrabajoMetros Ancho del trabajo de vinil a imprimir en metros.
 * @param float $largoTrabajoMetros Largo del trabajo de vinil a imprimir en metros.
 * @param float $margenSeguridadPorcentaje Porcentaje adicional para cubrir desperdicios (por defecto 5%).
 * @return float El precio estimado del Celtex para el trabajo, incluyendo el margen de seguridad.
 */
function calcularPrecioCeltex(
    $anchoPlanchaCeltexMetros,
    $largoPlanchaCeltexMetros,
    $precioBasePlancha,
    $anchoTrabajoMetros,
    $largoTrabajoMetros,
    $margenSeguridadPorcentaje = 5
) {
    // 1. Calcular el área total de la plancha de Celtex
    $areaTotalPlancha = $anchoPlanchaCeltexMetros * $largoPlanchaCeltexMetros;
    echo "Área total de la plancha de Celtex: " . number_format($areaTotalPlancha, 2) . " m²" . PHP_EOL;

    // 2. Determinar el costo por metro cuadrado de Celtex
    if ($areaTotalPlancha == 0) {
        return 0; // Evitar división por cero
    }
    $costoPorMetroCuadrado = $precioBasePlancha / $areaTotalPlancha;
    echo "Costo por metro cuadrado de Celtex: " . number_format($costoPorMetroCuadrado, 2) . " unidades/m²" . PHP_EOL;

    // 3. Calcular el área de tu trabajo de vinil
    $areaTrabajo = $anchoTrabajoMetros * $largoTrabajoMetros;
    echo "Área del trabajo a imprimir: " . number_format($areaTrabajo, 2) . " m²" . PHP_EOL;

    // Calcular el costo base del Celtex para este trabajo
    $costoBaseTrabajo = $areaTrabajo * $costoPorMetroCuadrado;

    // Aplicar el margen de seguridad
    $costoConMargen = $costoBaseTrabajo * (1 + $margenSeguridadPorcentaje / 100);
    echo "Costo base del Celtex para el trabajo (sin margen): " . number_format($costoBaseTrabajo, 2) . " unidades" . PHP_EOL;
    echo "Margen de seguridad aplicado: " . $margenSeguridadPorcentaje . "%" . PHP_EOL;

    return $costoConMargen;
}
// --- Instrucciones de uso ---

// Define las dimensiones de tu plancha de Celtex y su precio base
$anchoPlancha = 1.2;  // metros
$largoPlancha = 2.4;  // metros
$precioPlanchaBase = 37; // unidades monetarias

// Ejemplo de uso:
// Supongamos que tienes un trabajo de vinil de 1 metro de ancho por 0.5 metros de largo
$anchoTrabajoEjemplo = 1.4;  // metros
$largoTrabajoEjemplo = 1.4;  // metros

echo "--- Calculando precio para un trabajo de 1.0m x 0.5m ---" . PHP_EOL;
$precioFinalCeltex = calcularPrecioCeltex(
    $anchoPlancha,
    $largoPlancha,
    $precioPlanchaBase,
    $anchoTrabajoEjemplo,
    $largoTrabajoEjemplo
);

echo PHP_EOL . "El precio estimado del Celtex para tu trabajo es: " . number_format($precioFinalCeltex, 2) . " unidades" . PHP_EOL;

// Otro ejemplo con un margen de seguridad diferente
echo PHP_EOL . "--- Otro ejemplo con 10% de margen de seguridad ---" . PHP_EOL;
$precioFinalCeltexAltoMargen = calcularPrecioCeltex(
    $anchoPlancha,
    $largoPlancha,
    $precioPlanchaBase,
    1.4,  // otro ancho de trabajo
    1.4,  // otro largo de trabajo
    10    // 10% de margen de seguridad
);
echo PHP_EOL . "El precio estimado del Celtex para tu trabajo (con 10% de margen) es: " . number_format($precioFinalCeltexAltoMargen, 2) . " unidades" . PHP_EOL;



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
