<?php
include ("administrador/config/bd.php");
function obtenerSnackDisponibles($conexion)
{
    $consulta = $conexion->prepare("
        SELECT insumo.id_insumo, insumo.nombre_insumo 
        FROM insumo 
        INNER JOIN stock ON insumo.id_insumo = stock.id_insumo
        WHERE insumo.tipo_insumo = 4 AND stock.porcion > 0");
    $consulta->execute();

    $snacks = [];
    while ($row = $consulta->fetch(PDO::FETCH_ASSOC)) {
        $snacks[$row['id_insumo']] = $row['nombre_insumo'];
    }
    return $snacks;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_reemplazo') {
    $idSnack = $_POST['idSnack'];
    $snack_faltantes = $_POST['snack_faltantes']; // Array de objetos

    $output = '';
    foreach ($snack_faltantes as $snack) { 
        $nombre = $snack['nombre_insumo'];
        $insumos_disponibles = obtenerSnackDisponibles($conexion);

        $output .= '<div>';
        $output .= '<div style="text-align: center; margin-bottom: 15px;">';  // Centra el contenido y añade margen inferior
        $output .= '<span style="display: block; font-weight: bold;">Reemplazar</span>';  // Añade "Reemplazar" antes del label
        $output .= '<label for="insumo_' . htmlspecialchars($nombre) . '" style="display: block; margin-bottom: 10px;">'. htmlspecialchars($nombre) . '</strong> con </label>'; 
        $output .= '<select class="form-control" id="insumo_' . htmlspecialchars($nombre) . '" name="insumo_' . htmlspecialchars($nombre) . '" style="display: inline-block; text-align: center;">';  // Ajusta el select para que sea centrado
        $output .= '<option value="" style="text-align: center;"> Seleccione insumo </option>';      
        foreach ($insumos_disponibles as $id => $nombre_insumo) {
            $output .= '<option value="' . htmlspecialchars($id) . '" style="text-align: center;">' . htmlspecialchars($nombre_insumo) . '</option>';
        }
        $output .= '</select>';
        $output .= '</div>';
        $output .= '</div>';
        
    }
    echo $output;
}    
?>

<script>
    $(document).ready(function() {
    $('select').on('change', function() {
        if ($(this).val() !== '') {  // Si el valor seleccionado no está vacío
            $(this).css('background-color', '#d4edda');  // Color verde claro
        } else {
            $(this).css('background-color', '');  // Restablecer color
        }
    });
});

</script>