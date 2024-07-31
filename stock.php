<?php include ("template/cabecera.php"); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<?php
include ("administrador/config/bd.php");

// SELECT
$sentenciaSQL = $conexion->prepare("SELECT 
    stock.id_stock, 
    stock.id_insumo, 
    IFNULL(stock.fecha_modificacion, 'Sin registros') AS fecha_modificacion,
    insumo.nombre_insumo,
    IFNULL(stock.porcion, 0) AS porcion,
    CASE 
        WHEN insumo.tipo_insumo = 1 THEN 'Cobertura'
        WHEN insumo.tipo_insumo = 2 THEN 'Proteina'
        WHEN insumo.tipo_insumo = 3 THEN 'Vegetal'
        WHEN insumo.tipo_insumo = 4 THEN 'Snack'
        ELSE 'Otro'
    END AS tipo_insumo
    FROM insumo 
    RIGHT JOIN stock ON insumo.id_insumo = stock.id_insumo 
    ORDER BY tipo_insumo DESC;");
$sentenciaSQL->execute();
$listainsumos = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header text-white bg-primary mb-3 d-flex justify-content-center">
        STOCK DIARIO
    </div>
    <div class="card-body d-flex justify-content-center">
        <div class="form-check">
            <input type="checkbox" class="btn-check" name="tipo" id="cobertura_checkbox" value="cobertura" cheked>
            <label class="btn btn-danger" for="cobertura_checkbox">Cobertura</label>
            <input type="checkbox" class="btn-check" name="tipo" id="proteina_checkbox" value="proteina" checked>
            <label class="btn btn-danger" for="proteina_checkbox">Proteina</label>
            <input type="checkbox" class="btn-check" name="tipo" id="vegetal_checkbox" value="vegetal" checked>
            <label class="btn btn-danger" for="vegetal_checkbox">Vegetal</label>
            <input type="checkbox" class="btn-check" name="tipo" id="snack_checkbox" value="snack" checked>
            <label class="btn btn-danger" for="snack_checkbox">Snack</label>
        </div>
    </div>
    <table class="table table-bordered" id="tablaInsumos">
        <thead style="text-align: center;">
            <tr class="table-primary">
                <th style="font-size: 16px;">Tipo</th>
                <th style="font-size: 16px;">Nombre</th>
                <th style="font-size: 16px;">Porciones Disponibles</th>
                <th style="font-size: 16px;">Ultima Modificacion</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($listainsumos as $stock) { ?>
                <tr style="text-align: center;">
                    <td><?php echo strtoupper($stock['tipo_insumo']) ?></td>
                    <td><?php echo $stock['nombre_insumo'] ?></td>
                    <td>
                        <div class="input-group ">
                            <input type="text" class="porciones" id="porciones_<?php echo $stock['id_stock']; ?>"
                                value="<?php echo $stock['porcion']; ?>" disabled="">
                        </div>
                    </td>
                    <td><?php echo $stock['fecha_modificacion'] ?></td>
            <?php } ?>
        </tbody>
    </table>
</div>

<script>
    // Función para actualizar la tabla de insumos según los tipos seleccionados
    function actualizarTablaInsumos() {
        var tiposSeleccionados = [];
        var checkboxes = document.querySelectorAll('input[name="tipo"]');
        checkboxes.forEach(function (checkbox) {
            if (checkbox.checked) {
                tiposSeleccionados.push(checkbox.value);
            }
        });

        var insumosFiltrados = <?php echo json_encode($listainsumos); ?>.filter(function (insumo) {
            return tiposSeleccionados.includes(insumo.tipo_insumo.toLowerCase());
        });

        var tabla = document.getElementById("tablaInsumos").querySelector('tbody');
        tabla.innerHTML = '';
        insumosFiltrados.forEach(function (insumo) {
            var row = '<tr style="text-align: center;">' +
                '<td>' + insumo.tipo_insumo.toUpperCase() + '</td>' +
                '<td>' + insumo.nombre_insumo + '</td>' +
                '<td>' +
                '<div class="input-group ">' +
                '<input type="text" class="porciones" id="porciones_' + insumo.id_stock + '" value="' + insumo.porcion + '" disabled="">' +
                '</div>' +
                '</td>' +
                '<td>' + insumo.fecha_modificacion + '</td>' +
                '</tr>';
            tabla.innerHTML += row;
        });
    }

    // Agregar event listeners a los checkboxes para actualizar la tabla cuando se cambie su estado
    document.querySelectorAll('input[name="tipo"]').forEach(function (checkbox) {
        checkbox.addEventListener('change', actualizarTablaInsumos);
    });

    // Llamar a la función para inicializar la tabla
    actualizarTablaInsumos();
</script>

<?php include ("template/piepagina.php"); ?>