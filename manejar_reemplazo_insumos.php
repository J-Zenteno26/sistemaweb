<?php

include ("administrador/config/bd.php");
function obtenerCoberturasDisponibles($conexion) {
    $consulta = $conexion->prepare("
        SELECT insumo.id_insumo, insumo.nombre_insumo 
        FROM insumo 
        INNER JOIN stock ON insumo.id_insumo = stock.id_insumo
        WHERE insumo.tipo_insumo = 1 AND stock.porcion > 0");
    $consulta->execute();

    $coberturas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    return $coberturas;
}

function obtenerProteinasDisponibles($conexion) {
    $consulta = $conexion->prepare("
        SELECT insumo.id_insumo, insumo.nombre_insumo 
        FROM insumo 
        INNER JOIN stock ON insumo.id_insumo = stock.id_insumo
        WHERE insumo.tipo_insumo = 2 AND stock.porcion > 0");
    $consulta->execute();

    $proteinas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    return $proteinas;
}

function obtenerVegetalesDisponibles($conexion) {
    $consulta = $conexion->prepare("
        SELECT insumo.id_insumo, insumo.nombre_insumo 
        FROM insumo 
        INNER JOIN stock ON insumo.id_insumo = stock.id_insumo
        WHERE insumo.tipo_insumo = 3 AND stock.porcion > 0");
    $consulta->execute();

    $vegetales = $consulta->fetchAll(PDO::FETCH_ASSOC);
    return $vegetales;
}



if (isset($_POST['action']) && $_POST['action'] == 'get_reemplazo') {
    $id_roll = $_POST['id_roll'];
    $insumos_faltantes = $_POST['insumos_faltantes'];

    $output = '';
    foreach ($insumos_faltantes as $insumo) {
        $insumos_disponibles = [];

        if (strpos($insumo, 'cobertura') !== false) {
            $insumos_disponibles = obtenerCoberturasDisponibles($conexion);
        } elseif (strpos($insumo, 'proteina') !== false) {
            $insumos_disponibles = obtenerProteinasDisponibles($conexion);
        } elseif (strpos($insumo, 'vegetal_1') !== false || strpos($insumo, 'vegetal_2') !== false) {
            $insumos_disponibles = obtenerVegetalesDisponibles($conexion);
        }        

        $output .= '<div class="form-group">';
        $output .= '<label for="insumo_' . $insumo . '">Reemplazar ' . $insumo . ' con :</label>';
        $output .= '<select class="form-control" id="insumo_' . $insumo . '" name="insumo_' . $insumo . '">';
        foreach ($insumos_disponibles as $disponible) {
            $output .= '<option value="' . $disponible['id_insumo'] . '">' . $disponible['nombre_insumo'] . '</option>';
        }
        $output .= '</select>';
        $output .= '</div>';
    }

    echo $output;
}

?>
