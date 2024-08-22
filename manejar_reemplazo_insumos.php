<?php
include ("administrador/config/bd.php");

function obtenerCoberturasDisponibles($conexion)
{
    $consulta = $conexion->prepare("
        SELECT insumo.id_insumo, insumo.nombre_insumo 
        FROM insumo 
        INNER JOIN stock ON insumo.id_insumo = stock.id_insumo
        WHERE insumo.tipo_insumo = 1 AND stock.porcion > 0");
    $consulta->execute();

    $coberturas = [];
    while ($row = $consulta->fetch(PDO::FETCH_ASSOC)) {
        $coberturas[$row['id_insumo']] = $row['nombre_insumo'];
    }

    return $coberturas;
}

function obtenerProteinasDisponibles($conexion)
{
    $consulta = $conexion->prepare("
        SELECT insumo.id_insumo, insumo.nombre_insumo 
        FROM insumo 
        INNER JOIN stock ON insumo.id_insumo = stock.id_insumo
        WHERE insumo.tipo_insumo = 2 AND stock.porcion > 0");
    $consulta->execute();

    $proteinas = [];
    while ($row = $consulta->fetch(PDO::FETCH_ASSOC)) {
        $proteinas[$row['id_insumo']] = $row['nombre_insumo'];
    }

    return $proteinas;
}

function obtenerVegetalesDisponibles($conexion)
{
    $consulta = $conexion->prepare("
        SELECT insumo.id_insumo, insumo.nombre_insumo 
        FROM insumo 
        INNER JOIN stock ON insumo.id_insumo = stock.id_insumo
        WHERE insumo.tipo_insumo = 3 AND stock.porcion > 0");
    $consulta->execute();

    $vegetales = [];
    while ($row = $consulta->fetch(PDO::FETCH_ASSOC)) {
        $vegetales[$row['id_insumo']] = $row['nombre_insumo'];
    }

    return $vegetales;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_reemplazo') {
    $id_roll = $_POST['id_roll'];
    $insumos_faltantes = $_POST['insumos_faltantes']; // Array de objeto
    
    $output = '';
    foreach ($insumos_faltantes as $insumo) {
        $tipo = $insumo['tipo'];
        $nombre = $insumo['nombre'];

        $insumos_disponibles = [];

        if ($tipo === 'Cobertura') {
            $insumos_disponibles = obtenerCoberturasDisponibles($conexion);

        } elseif ($tipo === 'Proteina') {
            $insumos_disponibles = obtenerProteinasDisponibles($conexion);

        } elseif ($tipo === 'Vegetal') {
            $insumos_disponibles = obtenerVegetalesDisponibles($conexion);
        }

        // Mensaje de depuración
        // echo "Tipo de insumo: $tipo<br>";
        // print_r($insumos_disponibles);

        $output .= '<div>';
        $output .= '<div style="text-align: center; margin-bottom: 15px;">';  // Centra el contenido y añade margen inferior
        $output .= '<span style="display: block; font-weight: bold;">Reemplazar</span>';  // Añade "Reemplazar" antes del label
        $output .= '<label for="insumo_' . htmlspecialchars($nombre) . '" style="display: block; margin-bottom: 10px;">' . $tipo . '-  <strong>' . htmlspecialchars($nombre) . '</strong> con </label>'; 
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