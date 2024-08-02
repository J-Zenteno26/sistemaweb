<?php
include ("administrador/config/bd.php");

// DEFINICION DE VARIABLES
$id_promocion = $_POST['id_promocion'];

// Obtener los detalles de los rolls
$sentenciaSQL1 = $conexion->prepare("SELECT DISTINCT roll.id_roll, roll.nombre_roll,
    (SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.cobertura AND insumo.tipo_insumo = 1) AS cobertura,
    (SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.proteina AND insumo.tipo_insumo = 2) AS proteina,
    (SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.vegetal_1 AND insumo.tipo_insumo = 3) AS vegetal_1,
    (SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.vegetal_2 AND insumo.tipo_insumo = 3) AS vegetal_2
    FROM roll WHERE roll.id_promocion = :id_promocion");
$sentenciaSQL1->bindParam(':id_promocion', $id_promocion, PDO::PARAM_INT);
$sentenciaSQL1->execute();
$listaroll = $sentenciaSQL1->fetchAll(PDO::FETCH_ASSOC);

// Obtener los detalles de los snacks
$sentencia_snack = $conexion->prepare("SELECT DISTINCT snack.id_snack, snack.nombre_snack, snack.cantidad 
    FROM snack WHERE snack.id_promocion = :id_promocion");
$sentencia_snack->bindParam(':id_promocion', $id_promocion, PDO::PARAM_INT);
$sentencia_snack->execute();
$listasnack = $sentencia_snack->fetchAll(PDO::FETCH_ASSOC);

// Obtener el stock de los ingredientes de cada roll
$stock_roll = $conexion->prepare("SELECT DISTINCT 
    roll.id_roll, roll.nombre_roll,
    cobertura.nombre_insumo AS cobertura,
    cobertura.id_insumo as cobertura_id,
    proteina.nombre_insumo AS proteina,
    proteina.id_insumo as proteina_id,
    vegetal1.nombre_insumo AS vegetal_1,
    vegetal1.id_insumo as vegetal1_id,
    vegetal2.nombre_insumo AS vegetal_2,
    vegetal2.id_insumo as vegetal2_id,
    coberturaStock.porcion AS cobertura_porcion,
    proteinaStock.porcion AS proteina_porcion,
    vegetal1Stock.porcion AS vegetal_1_porcion,
    vegetal2Stock.porcion AS vegetal_2_porcion
FROM roll
LEFT JOIN insumo AS cobertura ON cobertura.id_insumo = roll.cobertura AND cobertura.tipo_insumo = 1
LEFT JOIN insumo AS proteina ON proteina.id_insumo = roll.proteina AND proteina.tipo_insumo = 2
LEFT JOIN insumo AS vegetal1 ON vegetal1.id_insumo = roll.vegetal_1 AND vegetal1.tipo_insumo = 3
LEFT JOIN insumo AS vegetal2 ON vegetal2.id_insumo = roll.vegetal_2 AND vegetal2.tipo_insumo = 3
LEFT JOIN stock AS coberturaStock ON coberturaStock.id_insumo = roll.cobertura
LEFT JOIN stock AS proteinaStock ON proteinaStock.id_insumo = roll.proteina
LEFT JOIN stock AS vegetal1Stock ON vegetal1Stock.id_insumo = roll.vegetal_1
LEFT JOIN stock AS vegetal2Stock ON vegetal2Stock.id_insumo = roll.vegetal_2
WHERE roll.id_promocion = :id_promocion");
$stock_roll->bindParam(':id_promocion', $id_promocion, PDO::PARAM_INT);
$stock_roll->execute();
$lista_stock_roll = $stock_roll->fetchAll(PDO::FETCH_ASSOC);


$stock_snack = $conexion->prepare("SELECT DISTINCT
snack.id_snack,
snack.nombre_snack,
snack.cantidad,
snack.id_promocion,
stock.id_insumo,
stock.porcion,
stock.fecha_modificacion
FROM snack
INNER JOIN stock on snack.id_snack = stock.id_insumo
where snack.id_promocion=:id_promocion");
$stock_snack->bindParam(':id_promocion', $id_promocion, PDO::PARAM_INT);
$stock_snack->execute();
$lista_stock_snack = $stock_snack->fetchAll(PDO::FETCH_ASSOC);

// Array para tipos de insumo
$tipos_insumo = [
    1 => 'Cobertura',
    2 => 'Proteina',
    3 => 'Vegetal'
];

$output = '';
// Generar HTML para rolls
if (!empty($listaroll)) {
    $output .= '<ul>';
    foreach ($listaroll as $roll) {
        // Reiniciar disponibilidad para cada roll
        $disponible = true;
        $ingredientes_faltantes = [];

        foreach ($lista_stock_roll as $stock) {
            if ($stock['id_roll'] == $roll['id_roll']) {
                if ($stock['cobertura_porcion'] <= 0) {
                    $disponible = false;
                    $ingredientes_faltantes[] = $tipos_insumo[1] . ' - ' . $stock['cobertura'];
                }
                if ($stock['proteina_porcion'] <= 0) {
                    $disponible = false;
                    $ingredientes_faltantes[] = $tipos_insumo[2] . ' - ' . $stock['proteina'];
                }
                if ($stock['vegetal_1_porcion'] <= 0) {
                    $disponible = false;
                    $ingredientes_faltantes[] = $tipos_insumo[3] . ' - ' . $stock['vegetal_1'];
                }
                if ($stock['vegetal_2_porcion'] <= 0) {
                    $disponible = false;
                    $ingredientes_faltantes[] = $tipos_insumo[3] . ' - ' . $stock['vegetal_2'];
                }
            }
        }
        $output .= '<li style="margin-bottom: 20px;">';
        $output .= '<div style="display: flex; justify-content: space-between; align-items: center;">';
        $output .= '<div>';
        $output .= '<strong>Roll ' . strtolower($roll['nombre_roll']) . '</strong> :<br>';
        $output .= '<strong>Cobertura </strong>' . $roll['cobertura'] . '<br>';
        $output .= '<strong>Proteina </strong>' . $roll['proteina'] . '<br>';
        $output .= '<strong>Vegetales </strong>' . $roll['vegetal_1'] . ' y ' . $roll['vegetal_2'] . '.<br>';
        $output .= '</div>';
        if (!$disponible) {
            $output .= '<button class="btn btn-danger btn-sm editarRollBtn" data-id="' . $roll['id_roll'] . '" data-insumos="' . implode(',', $ingredientes_faltantes) . '" title="Modificar insumo faltante">!</button>';
        }
        $output .= '</div>';
        if (!empty($ingredientes_faltantes)) {
            $output .= '<div class="list-group-item list-group-item-warning d-flex justify-content-between align-items-center" style="margin-top: 10px;">';
            $output .= '<div> <strong> Insumo faltante: </strong>' . implode(', ', $ingredientes_faltantes) . ' </div>';
            $output .= '</div>';
        }
        $output .= '</li>';
    }
    $output .= '</ul>';
}

// Generar HTML para snacks
if (!empty($listasnack)) {
    $output .= '<ul>';
    foreach ($listasnack as $snack) {
        $disponible = true; // Reiniciar disponibilidad para cada snack     
        $snack_faltantes = [];

        foreach ($lista_stock_snack as $stock) {
            if ($stock['id_snack'] == $snack['id_snack']) {
                if ($stock['porcion'] <= 0) {
                    $disponible = false;
                    $snack_faltantes[] = $stock['nombre_snack'];
                }
            }
        }
        $output .= '<li style="margin-bottom: 20px;">';
        $output .= '<div style="display: flex; justify-content: space-between; align-items: center;">';
        $output .= '<div>';
        $output .= '<strong>Snack:</strong> ' . strtolower($snack['nombre_snack']) . ' - ' . $snack['cantidad'] . ' unid.<br>';
        $output .= '</div>';
        if (!$disponible) {
            $output .= '<button class="btn btn-danger btn-sm editarSnackBtn" data-id="' . $snack['id_snack'] . '" title="Modificar insumo faltante">!</button>';
        }
        $output .= '</div>';
        if (!empty($snack_faltantes)) {
            $output .= '<div class="list-group-item list-group-item-warning d-flex justify-content-between align-items-center" style="margin-top: 10px;">';
            $output .= '<div> <strong> Insumo faltante: </strong>' . implode(', ', $snack_faltantes) . ' </div>';
            $output .= '</div>';
        }
        $output .= '</li>';
    }
    $output .= '</ul>';
}
if ((!empty($snack_faltantes)) || (!empty($ingredientes_faltantes))) {
    $output .= '<div class="alert alert-dismissible alert-danger">';
    $output .= '<strong>!Atención! </strong>No están todos los insumos disponibles. </div>';
    $stock_disponible = true;
} else {
    $stock_disponible = false;
}

echo $output;
?>

<script>
$(document).ready(function () {
    $('.editarRollBtn').on('click', function () {
        var idRoll = $(this).data('id');
        var insumosFaltantes = $(this).data('insumos');
        
        // Transformar insumosFaltantes en un array de objetos con 'tipo' y 'nombre'
        var insumosArray = [];
        if (typeof insumosFaltantes === 'string') {
            insumosFaltantes.split(',').forEach(function(insumo) {
                var partes = insumo.split(' - ');
                insumosArray.push({ tipo: partes[0], nombre: partes[1] });
            });
        }

        $.ajax({
            url: 'manejar_reemplazo_insumos.php',
            method: 'POST',
            data: { action: 'get_reemplazo', id_roll: idRoll, insumos_faltantes: insumosArray },
            success: function (response) {
                $('#reemplazoContainer').html(response);
                $('#modalForm .modal-content').addClass('modal-darken'); // Oscurecer el primer modal
                $('#modalReemplazarInsumo').modal('show');
            }
        });
    });

    $('#modalReemplazarInsumo').on('hidden.bs.modal', function () {
        $('#modalForm .modal-content').removeClass('modal-darken'); // Quitar oscurecimiento cuando se cierre el segundo modal
    });
});

</script>