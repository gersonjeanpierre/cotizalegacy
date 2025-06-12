<?php
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
    if ($largoVinilMetros <= 1.2 && ($anchoVinilMetros >0 && $anchoVinilMetros <= 1.2)) {
      $costoFinal = $precioBasePlancha / 2;
      echo "El costo es 1.2 12:" . number_format($precioBasePlancha / 2, 2) . " soles" . PHP_EOL;
      return $costoFinal;
    };
    if ($anchoVinilMetros > 0.6 && $anchoVinilMetros <= 1.2) {
      if ($largoVinilMetros > 1.2 && $largoVinilMetros <= 2.4) {
        $costoFinal = $precioBasePlancha ;
        echo "El costo es 06 1.2 : " . number_format($costoFinal, 2) . " soles" . PHP_EOL;
        return $costoFinal;
      } else if ($largoVinilMetros > 2.4 && $largoVinilMetros <= 4.8) {
        $costoFinal = $precioBasePlancha*2;
        echo "El costo es 06 2.4: " . number_format($costoFinal, 2) . " soles" . PHP_EOL;
        return $costoFinal;
      }
    }
    
  }

  if ($anchoVinilMetros > $anchoPlancha && $anchoVinilMetros <= 1.5) {


    if ($largoVinilMetros <= 1.2) {
      $costoFinal = $precioBasePlancha *0.625;
      echo "El costo es 1.2 12 0625: " . number_format($costoFinal, 2) . " soles" . PHP_EOL;
      return $costoFinal;
    }

    if ($largoVinilMetros > 1.2 && $largoVinilMetros <= 1.8) {
      // $deltaX = $anchoVinilMetros - $anchoPlancha;
      // $deltaY = $largoPlancha - $largoVinilMetros;
      // $cantidadListones = $largoVinilMetros / $anchoPlancha;
      // $cantidadListones = ceil($cantidadListones);
      // echo "Cantidad de listones necesarios: " . $cantidadListones . PHP_EOL;

      // $areaSobrante = (($deltaY - $cantidadListones * $deltaX) * $anchoPlancha);
      // $areaSobrante = round($areaSobrante, 4);

      // echo "Area sobrante:" . ($areaSobrante) . PHP_EOL;
      // echo "Medidas sobrantes de la plancha Celtex: " . round($deltaY - $cantidadListones * $deltaX, 4) . " x " . 1.2 . " m" . PHP_EOL;
      $costoFinal = $precioBasePlancha ;
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
$test = calcularPrecioCeltexFoam(3.6,1.5, 37, 10);
echo "Resultado del test: " . $test . PHP_EOL;
