<?php include ("../template/cabecera.php"); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<?php
include ("../config/bd.php");

// RECEPCION DE DATOS
$insumo = (isset($_POST['insumo'])) ? $_POST['insumo'] : "";
$porcion = (isset($_POST['porcion'])) ? $_POST['porcion'] : "";
$id_stock = (isset($_POST['id_stock'])) ? $_POST['id_stock'] : "";
$accion = (isset($_POST['accion'])) ? $_POST['accion'] : "";
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : null;

function obtenerTipoInsumo($tipo)
{
    switch ($tipo) {
        case 'cobertura':
            return 1;
        case 'proteina':
            return 2;
        case 'vegetal':
            return 3;
        case 'snack':
            return 4;
        default:
            return 0; // Tipo de insumo no seleccionado
    }
}

switch ($accion) {
    case "Agregar":
        // Obtener el tipo de insumo para insertarlo
        $tipo_insumo = obtenerTipoInsumo($tipo);

        // SE INSERTA UN NUEVO INSUMO EN TABLA - INSUMO -
        $sentenciaSQL = $conexion->prepare("INSERT INTO `insumo`(`id_insumo`, `tipo_insumo`, `nombre_insumo`) VALUES (NULL, :tipo_insumo, :nombre_insumo)");
        $sentenciaSQL->bindParam(':tipo_insumo', $tipo_insumo);
        $sentenciaSQL->bindParam(':nombre_insumo', $insumo);
        $sentenciaSQL->execute();

        // EXTRAYENDO ID DEL INSUMO RECIEN INSERTADO
        $sentenciaSQL1 = $conexion->prepare("SELECT * FROM `insumo` ORDER BY id_insumo DESC LIMIT 1");
        $sentenciaSQL1->execute();
        $nuevo_insumo = $sentenciaSQL1->fetch(PDO::FETCH_LAZY);
        try {
            $id_nuevo_insumo = $nuevo_insumo['id_insumo'];
        } catch (PDOException $e) {
            echo "Error al SELECCIONAR LA ID DEL INSUMO  " . $e->getMessage();
        }

        // SE INSERTA EL STOCK CON LA ID DEL INSUMO OBTENIDA ANTERIORMENTE
        $sentenciaSQL2 = $conexion->prepare("INSERT INTO `stock`(`id_stock`, `id_insumo`, `porcion`, `fecha_modificacion`) VALUES (NULL, :id_insumo, :porcion, NOW())");
        $sentenciaSQL2->bindParam(':id_insumo', $id_nuevo_insumo);
        $sentenciaSQL2->bindParam(':porcion', $porcion);
        $sentenciaSQL2->execute();

        header("Location:stock.php");
        break;

    case "Modificar":
        $sentenciaSQL = $conexion->prepare("UPDATE stock SET porcion=:porcion, fecha_modificacion=NOW() WHERE id_stock=:id_stock");
        $sentenciaSQL->bindParam(':porcion', $porcion);
        $sentenciaSQL->bindParam(':id_stock', $id_stock);
        $sentenciaSQL->execute();
        header("Location:stock.php");
        break;

    case "Cancelar":
        header("Location:stock.php");
        break;

    case "Borrar":
        $sentenciaSQL = $conexion->prepare("DELETE FROM stock WHERE id_stock=:id_stock");
        $sentenciaSQL->bindParam(':id_stock', $id_stock);
        $sentenciaSQL->execute();


        $sentenciaSQL1 = $conexion->prepare("DELETE FROM insumo WHERE id_insumo=:id_insumo");
        $sentenciaSQL1->bindParam(':id_insumo', $id_insumo);
        $sentenciaSQL1->execute();

        header("Location:stock.php");
        break;

    case "Editar":
        $sentenciaSQL = $conexion->prepare("SELECT stock.id_stock, stock.id_insumo, stock.fecha_modificacion,insumo.id_insumo, insumo.nombre_insumo, insumo.tipo_insumo,
        IFNULL(stock.porcion, 0) AS porcion,
        CASE 
            WHEN insumo.tipo_insumo = 1 THEN 'Cobertura'
            WHEN insumo.tipo_insumo = 2 THEN 'Proteina'
            WHEN insumo.tipo_insumo = 3 THEN 'Vegetal'
            WHEN insumo.tipo_insumo = 4 THEN 'Snack'
            ELSE 'Otro'
        END AS tipo_insumo
        FROM id_insumo 
        RIGHT JOIN stock ON insumo.id_insumo = stock.id_insumo
        WHERE id_stock= :id_stock
        ORDER BY porcion DESC , tipo_insumo ASC");
        $sentenciaSQL->bindParam(':id_stock', $id_stock);
        $sentenciaSQL->execute();
        $stock = $sentenciaSQL->fetch(PDO::FETCH_LAZY);

        try {
            $id_insumo = $stock['id_insumo'];
            $insumo = $stock['nombre_insumo'];
            $porcion = $stock['porcion'];
            $tipo = $stock['tipo_insumo'];
        } catch (\Throwable $th) {

        }
        break;
}

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
FROM 
insumo 
RIGHT JOIN 
stock 
ON 
insumo.id_insumo = stock.id_insumo 
ORDER BY 
fecha_modificacion DESC;");
$sentenciaSQL->execute();
$listainsumos = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- CRUD -->
<div class="col-md-4">
    <div class="card">
        <div class = "card-header text-white bg-primary mb-3 d-flex justify-content-center">
            AÑADIR INSUMOS
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <!-- TIPO DE INSUMO -->
                <div>
                    <input type="radio" name="tipo" id="cobertura_radio" value="cobertura" style="margin-left: 10px;">
                    <label for="cobertura_radio">Cobertura</label>
                    <input type="radio" name="tipo" id="proteina_radio" value="proteina" style="margin-left: 10px;">
                    <label for="proteina_radio">Proteina</label>
                    <input type="radio" name="tipo" id="vegetal_radio" value="vegetal" style="margin-left: 10px;">
                    <label for="vegetal_radio">Vegetal</label>
                    <input type="radio" name="tipo" id="snack_radio" value="snack" style="margin-left: 10px;">
                    <label for="snack_radio">Snack</label>
                </div>
                <br>
                <!-- NOMBRE -->
                <div class="form-group">
                    <label for="insumo">Nombre</label>
                    <input type="text" required class="form-control" value="<?php echo $insumo; ?>" name="insumo"
                        id="insumo" placeholder="Ej: Camaron">
                </div>
                <br>
                <!-- CANTIDAD -->
                <div class="form-group">
                    <label for="porcion">Porciones</label>
                    <input type="int" required class="form-control" value="<?php echo $porcion; ?>" name="porcion"
                        id="porcion" placeholder="Ej: 10">
                </div>
                <br>
                <!-- BOTON ACCION -->
                <div class="btn-group d-flex justify-content-center" role="group" aria-label="">
                    <button type="submit" name="accion" <?php echo ($accion == "Editar") ? "disabled" : ""; ?>
                        value="Agregar" class="btn btn-success">Agregar</button>
                    <button type="submit" name="accion" <?php echo ($accion != "Editar") ? "disabled" : ""; ?>
                        value="Modificar" class="btn btn-warning">Modificar</button>
                    <button type="submit" name="accion" value="Cancelar" class="btn btn-info">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- TABLA DE INGREDIENTES-->
<div class="col-md-8">
    <div class="card">
        <div class = "card-header text-white bg-primary mb-3 d-flex justify-content-center">
            INSUMOS
        </div>
        <div class="card-body d-flex justify-content-center">
            <div class="form-check">
                <input type="checkbox" class="btn-check" name="tipo" id="cobertura_checkbox" value="cobertura" checked>
                <label class="btn btn-danger" for="cobertura_checkbox">Cobertura</label>
                <input type="checkbox" class="btn-check" name="tipo" id="proteina_checkbox" value="proteina" checked>
                <label class="btn btn-danger" for="proteina_checkbox">Proteina</label>
                <input type="checkbox" class="btn-check" name="tipo" id="vegetal_checkbox" value="vegetal" checked>
                <label class="btn btn-danger" for="vegetal_checkbox">Vegetal</label>
                <input type="checkbox" class="btn-check" name="tipo" id="snack_checkbox" value="snack" checked>
                <label class="btn btn-danger" for="snack_checkbox">Snack</label>
            </div>
        </div>
    </div>
    <div >
        <table class="table table-hover" id="tablaInsumos">
            <thead >
                <tr class="table-primary"> 
                    <th style="font-size: 16px;">Tipo</th>
                    <th style="font-size: 16px;">Nombre</th>
                    <th style="font-size: 16px;">Porciones<br>Disponibles</th>
                    <th style="font-size: 16px;">Ultima<br> Modificacion</th>
                    <th style="font-size: 16px;">Acciones</th>
                </tr>
            </thead>
            <br>
            <tbody>
                <?php foreach ($listainsumos as $stock) { ?>
                    <tr>
                        <td><?php echo strtoupper($stock['tipo_insumo']) ?></td>
                        <td><?php echo $stock['nombre_insumo'] ?></td>
                        <td>
                            <div class="input-group ">
                                <button class="btn1 btn-outline-secondary" type="button" onclick="decreaseValue(<?php echo $stock['id_stock']; ?>)">-</button>
                                <input type="text" class="porciones" id="porciones_<?php echo $stock['id_stock']; ?>" value="<?php echo $stock['porcion']; ?>">
                                <button class="btn1 btn-outline-secondary" type="button" onclick="increaseValue(<?php echo $stock['id_stock']; ?>)">+</button>
                            </div>
                        </td>
                        <td><?php echo $stock['fecha_modificacion'] ?></td>
                        <td>
                            <div class="btn-group d-flex justify-content-center" role="group" aria-label="">
                                <form method="post">
                                    <input type="hidden" name="id_stock" id="id_stock" value="<?php echo $stock['id_stock'] ?>">
                                    <input type="submit" name="accion" value="Editar" class="btn btn-primary">
                                    <input type="submit" name="accion" value="Borrar" class="btn btn-danger" >
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Scripts -->
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

        var tabla = document.getElementById("tablaInsumos");
        tabla.innerHTML = '<thead><tr><th>Tipo</th><th>Nombre</th><th>Porciones Disponibles</th><th>Ultima Modificacion</th><th>Acciones</th></tr></thead><tbody>';
        insumosFiltrados.forEach(function (insumo) {
            tabla.innerHTML += '<tr><td>' + insumo.tipo_insumo + '</td><td>' + insumo.nombre_insumo + '</td><td>' +
                '<div class="input-group"><button class="btn btn-outline-secondary" type="button" onclick="decreaseValue(' + insumo.id_stock + ')">-</button>' +
                '<input type="text" class="form-control" id="porciones_' + insumo.id_stock + '" value="' + insumo.porcion + '">' +
                '<button class="btn btn-outline-secondary" type="button" onclick="increaseValue(' + insumo.id_stock + ')">+</button></div>' +
                '</td><td>' + insumo.fecha_modificacion + '</td><td><form method="post"><input type="hidden" name="id_stock" id="id_stock" value="' +
                insumo.id_stock + '"><input type="submit" name="accion" value="Editar" class="btn btn-primary"><input type="submit" name="accion" value="Borrar" class="btn btn-danger"></form></td></tr>';
        });
        tabla.innerHTML += '</tbody>';
    }

    window.onload = function () {
        actualizarTablaInsumos();
        document.querySelectorAll('input[name="tipo"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                actualizarTablaInsumos();
            });
        });
    };

    function increaseValue(id) {
        var input = document.getElementById('porciones_' + id);
        var newValue = parseInt(input.value) || 0;
        newValue = newValue + 1;
        input.value = newValue;
        guardarCambiosEnBaseDeDatos(id, newValue);
        console.log("value:", newValue);
    }

    function decreaseValue(id) {
        var input = document.getElementById('porciones_' + id);
        var newValue = parseInt(input.value) || 0;
        if (newValue > 0) {
            newValue = newValue - 1;
            input.value = newValue;
            guardarCambiosEnBaseDeDatos(id, newValue);
            console.log("value:", newValue);
        }
    }

    function guardarCambiosEnBaseDeDatos(id, newValue) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "guardar_cambios.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    console.log(xhr.responseText);
                    location.reload();
                } else {
                    console.error('Error en la solicitud AJAX');
                }
            }
        };
        xhr.send("id=" + id + "&newValue=" + newValue);
    }
</script>


<?php include ("../template/piepagina.php"); ?>