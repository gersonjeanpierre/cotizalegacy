<?php

function cortarDeSobrantes(&$sobrantes, $largo, $ancho) {
    foreach ($sobrantes as $idx => $s) {
        if ($s[0] >= $largo && $s[1] >= $ancho) {
            // Cortar el rectángulo solicitado y generar hasta 2 nuevos sobrantes
            $nuevoSobrante1 = [$s[0] - $largo, $s[1]]; // tira a lo largo
            $nuevoSobrante2 = [$largo, $s[1] - $ancho]; // tira a lo ancho
            unset($sobrantes[$idx]);
            if ($nuevoSobrante1[0] > 0 && $nuevoSobrante1[1] > 0) $sobrantes[] = $nuevoSobrante1;
            if ($nuevoSobrante2[0] > 0 && $nuevoSobrante2[1] > 0) $sobrantes[] = $nuevoSobrante2;
            return true;
        }
    }
    return false;
}

function calcularPrecioCeltex(
  $largoVinilMetros,
  $anchoVinilMetros,
  $precioCeltexBase,
  $margenSeguridadPorcentaje = 10,
  $totalCortesNecesarios = 1
) {
  $largoPlanchaCeltex = 2.4; // metros
  $anchoPlanchaCeltex = 1.2; // metros

  if ($largoVinilMetros > $largoPlanchaCeltex || $anchoVinilMetros > 1.5) {
    echo "El vinil es más grande que la plancha Celtex en alguna dimensión permitida." . PHP_EOL;
    return;
  }

  $sobrantes = []; // Sobrantes de Celtex: [largo, ancho]
  $cantidadPlanchas = 0;
  $cortesRestantes = $totalCortesNecesarios;

  while ($cortesRestantes > 0) {
    // Buscar si hay un sobrante de Celtex que pueda cubrir el corte de vinil
    if (cortarDeSobrantes($sobrantes, $largoVinilMetros, $anchoVinilMetros)) {
      $cortesRestantes--;
      continue;
    }

    // Si no hay sobrante, usar una plancha nueva de Celtex
    $cantidadPlanchas++;

    // Cortar el vinil y generar sobrantes en L de la plancha Celtex
    $sobrante1 = [$largoPlanchaCeltex - $largoVinilMetros, $anchoPlanchaCeltex]; // tira a lo largo
    $sobrante2 = [$largoVinilMetros, $anchoPlanchaCeltex - $anchoVinilMetros];   // tira a lo ancho

    if ($sobrante1[0] > 0 && $sobrante1[1] > 0) $sobrantes[] = $sobrante1;
    if ($sobrante2[0] > 0 && $sobrante2[1] > 0) $sobrantes[] = $sobrante2;

    // Si el ancho del vinil es mayor que la plancha, falta cubrir el resto
    if ($anchoVinilMetros > $anchoPlanchaCeltex) {
      $anchoFaltante = $anchoVinilMetros - $anchoPlanchaCeltex;
      // Intentar cubrir el faltante con los sobrantes
      if (!cortarDeSobrantes($sobrantes, $largoVinilMetros, $anchoFaltante)) {
        // Si no hay sobrante suficiente, se necesitaría otra plancha (o puedes reportar el faltante)
        // Aquí solo reportamos el faltante
        echo "Falta cubrir: $largoVinilMetros x $anchoFaltante m (no hay sobrante suficiente)\n";
      }
    }

    $cortesRestantes--;
  }

  $areaTotalPlanchas = $cantidadPlanchas * $largoPlanchaCeltex * $anchoPlanchaCeltex;
  $areaVinil = $totalCortesNecesarios * $largoVinilMetros * $anchoVinilMetros;
  $areaSobrante = round($areaTotalPlanchas - $areaVinil, 4);

  echo "Cantidad de planchas necesarias: " . $cantidadPlanchas . PHP_EOL;
  echo "Area total utilizada: " . round($areaVinil, 4) . " m2" . PHP_EOL;
  echo "Area total de planchas: " . round($areaTotalPlanchas, 4) . " m2" . PHP_EOL;
  echo "Area sobrante total: " . $areaSobrante . " m2" . PHP_EOL;

  // Mostrar sobrantes finales
  echo "Sobrantes finales (rectángulos):" . PHP_EOL;
  foreach ($sobrantes as $i => $s) {
    $area = round($s[0] * $s[1], 4);
    echo "- Sobrante " . ($i+1) . ": " . round($s[0], 4) . " x " . round($s[1], 4) . " m = " . $area . " m2" . PHP_EOL;
  }
}

// Prueba con varios largos
echo "Caso largoVinilMetros = 1.5\n";
calcularPrecioCeltex(1.5, 1.3, 37, 10, 1);
echo "\nCaso largoVinilMetros = 1.8\n";
calcularPrecioCeltex(1.8, 1.3, 37, 10, 1);
echo "\nCaso largoVinilMetros = 2\n";
calcularPrecioCeltex(2, 1.3, 37, 10, 1);
echo "\nCaso largoVinilMetros = 2.6\n";
calcularPrecioCeltex(2.6, 1.3, 37, 10, 1);