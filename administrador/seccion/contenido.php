<?php
include ("../template/cabecera.php"); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<?php
include ("../config/bd.php");

// IDS
// Obtener la ID de promoción de la URL
$id_promocion = $_GET['id_promocion'];

$id_roll = isset($_POST['id_roll']) ? $_POST['id_roll'] : "";
$id_snack = isset($_POST['id_snack']) ? $_POST['id_snack'] : "";

// ROLL
$nombre_roll = isset($_POST['nombre_roll']) ? $_POST['nombre_roll'] : "";
$cobertura = isset($_POST['cobertura']) ? $_POST['cobertura'] : "";
$proteina = isset($_POST['proteina']) ? $_POST['proteina'] : "";
$vegetal1 = isset($_POST['vegetal1']) ? $_POST['vegetal1'] : "";
$vegetal2 = isset($_POST['vegetal2']) ? $_POST['vegetal2'] : "";

// SNACK
$nombre_snack = isset($_POST['nombre_snack']) ? $_POST['nombre_snack'] : "";
$cantidad = isset($_POST['cantidad']) ? $_POST['cantidad'] : "";

$tipo_seleccionado = isset($_POST['tipo_seleccionado']) ? $_POST['tipo_seleccionado'] : ""; // TIPO OBTENIDO DE LA CONSULTA POR EL ITEM
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : ""; // TIPO DEFINIDO POR RADIOBUTTON

$accion = (isset($_POST['accion'])) ? $_POST['accion'] : "";

switch ($accion) {
    case "Agregar";
        if ($tipo == 'roll') {
            try {
                // INSERTA EN TABLA ROLL
                $sentenciaSQL = $conexion->prepare("INSERT INTO roll (id_roll, id_promocion, nombre_roll, cobertura, proteina, vegetal_1, vegetal_2) 
                VALUES (NULL, :id_promocion, :nombre_roll, :cobertura, :proteina, :vegetal_1, :vegetal_2)");
                $sentenciaSQL->bindParam(':id_promocion', $id_promocion);
                $sentenciaSQL->bindParam(':nombre_roll', $nombre_roll);
                $sentenciaSQL->bindParam(':cobertura', $cobertura);
                $sentenciaSQL->bindParam(':proteina', $proteina);
                $sentenciaSQL->bindParam(':vegetal_1', $vegetal1);
                $sentenciaSQL->bindParam(':vegetal_2', $vegetal2);
                $sentenciaSQL->execute();
            } catch (PDOException $e) {
                echo "Error al agregar el roll: " . $e->getMessage();
            }
        } else if ($tipo == 'snack') {
            try {
                //INSERTA EN TABLA SNACKS
                $sentenciaSQL_otro = $conexion->prepare("INSERT INTO snack (id_snack, nombre_snack, cantidad, id_promocion) 
                VALUES (NULL, :nombre_snack, :cantidad, :id_promocion)");
                $sentenciaSQL_otro->bindParam(':nombre_snack', $nombre_snack);
                $sentenciaSQL_otro->bindParam(':cantidad', $cantidad);
                $sentenciaSQL_otro->bindParam(':id_promocion', $id_promocion);
                $sentenciaSQL_otro->execute();

                // LUEGO DE AÑADIR EL SNACK COMO TAL A LA TABLA DE SNACK, SE DEBE AÑADIR A LA TABLA DE INSUMOS PARA PODER CONTROLAR EL STOCK.
                try {
                    $sentenciaSQL2 = $conexion->prepare("INSERT INTO insumo (id_insumo, tipo_insumo, nombre_insumo) VALUES (NULL, :tipo_insumo, :nombre_insumo)");
                    $tipo_insumo = 4; // tipo de insumo es siempre 4
                    $sentenciaSQL2->bindParam(':tipo_insumo', $tipo_insumo);
                    $sentenciaSQL2->bindParam(':nombre_insumo', $nombre_snack);
                    $sentenciaSQL2->execute();

                } catch (PDOException $e) {
                    echo "Error al insertar snack: " . $e->getMessage();
                }
            } catch (PDOException $e) {
                echo "Error al insertar snack: " . $e->getMessage();
            }
        } else {
            echo "No se realizó la inserción. ";
        }
        break;

    case "Modificar":
        if ($tipo == 'roll') {
            try {
                // Realizar la actualización en la base de datos
                $sentenciaSQL = $conexion->prepare("UPDATE roll SET nombre_roll=:nombre_roll, cobertura=:cobertura, proteina=:proteina, vegetal_1=:vegetal_1, vegetal_2=:vegetal_2 WHERE id_roll=:id_roll");
                $sentenciaSQL->bindParam(':nombre_roll', $nombre_roll);
                $sentenciaSQL->bindParam(':cobertura', $cobertura);
                $sentenciaSQL->bindParam(':proteina', $proteina);
                $sentenciaSQL->bindParam(':vegetal_1', $vegetal1);
                $sentenciaSQL->bindParam(':vegetal_2', $vegetal2);
                $sentenciaSQL->bindParam(':id_roll', $id_roll);
                $sentenciaSQL->execute();


                $query = $sentenciaSQL->queryString;
                echo "Consulta SQL: $query\n"; // Imprimir consulta SQL en consola
                echo "id_roll:  $id_roll\n";

                // Redireccionar después de la modificación exitosa
                header("Location: contenido.php?id_promocion=$id_promocion");

            } catch (PDOException $e) {
                echo "Error al modificar el roll: " . $e->getMessage();
            }
        } elseif ($tipo == 'snack') {
            try {
                $sentenciaSQL1 = $conexion->prepare("UPDATE snack SET nombre_snack=:nombre_snack, cantidad=:cantidad WHERE id_snack=:id_snack");
                $sentenciaSQL1->bindParam(':nombre_snack', $nombre_snack);
                $sentenciaSQL1->bindParam(':cantidad', $cantidad);
                $sentenciaSQL1->bindParam(':id_snack', $id_snack);
                $sentenciaSQL1->execute();


                // Obtener la consulta SQL
                $query = $sentenciaSQL1->queryString;
                echo "Consulta SQL: $query\n"; // Imprimir consulta SQL en consola
                echo "id_snack:  $id_snack\n";
                echo "nombre_snack:  $nombre_snack)\n";


                header("Location: contenido.php?id_promocion=$id_promocion");


            } catch (PDOException $e) {
                echo "Error al modificar el snack: " . $e->getMessage();
            }
        } else {
            echo "No se ha seleccionado una opción válida.";
        }
        break;

    case "Cancelar": { // REDIRIGE A PÁGINA CON LA MISMA URL ANTERIOR
        header("Location: " . $_SERVER['HTTP_REFERER']);
        break;
    }

    // ---------------------- SELECCIONAR PARA LLENAR EL FORM
    case "Seleccionar":
        if ($tipo_seleccionado == 'roll') {
            $sentenciaSQL = $conexion->prepare("SELECT * FROM roll where id_roll=:id_roll");
            $sentenciaSQL->bindParam(':id_roll', $id_roll);
            $sentenciaSQL->execute();
            $roll = $sentenciaSQL->fetch(PDO::FETCH_LAZY);
            try {
                $nombre_roll = $roll['nombre_roll'];
                $cobertura = $roll['cobertura'];
                $proteina = $roll['proteina'];
                $vegetal1 = $roll['vegetal_1'];
                $vegetal2 = $roll['vegetal_2'];
            } catch (PDOException $e) {
            }
        } else if ($tipo_seleccionado == 'snack') {
            $tipo = $tipo_seleccionado;
            $sentenciaSQL2 = $conexion->prepare("SELECT * FROM snack where id_snack=:id_snack");
            $sentenciaSQL2->bindParam(':id_snack', $id_snack);
            $sentenciaSQL2->execute();

            $snack = $sentenciaSQL2->fetch(PDO::FETCH_LAZY);
            try {
                $nombre_snack = $snack['nombre_snack'];
                $cantidad = $snack['cantidad'];
            } catch (PDOException $e) {
                echo "Error al agregar el roll: " . $e->getMessage();
            }
        }
        break;

    case "Borrar":
        if ($tipo_seleccionado == 'roll') {
            echo "<script>
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: '¿Estás seguro de que deseas eliminar el Roll?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminarlo',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Ejecutar la eliminación del registro
                        window.location.href = 'borrar.php?id=" . $id_roll . "&tipo=roll';
                    } else {
                        // Si el usuario cancela, no hacer nada
                    }
                });
             </script>";
        } else if ($tipo_seleccionado == 'snack') {
            echo "<script>
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¿Estás seguro de que deseas eliminar este Snack?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminarlo',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Ejecutar la eliminación del registro
                    window.location.href = 'borrar.php?id=" . $id_snack . "&tipo=snack';
                } else {
                    // Si el usuario cancela, no hacer nada
                }
            });
         </script>";
        }
        break;
}

// OBTENIENDO DATOS PROMOCION 
$sentenciaSQL = $conexion->prepare("SELECT * FROM promocion WHERE id_promocion=:id_promocion");
$sentenciaSQL->bindParam(':id_promocion', $id_promocion, PDO::PARAM_INT);
$sentenciaSQL->execute();
$listapromocion = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
$nombre_promocion = strtoupper($listapromocion[0]['nombre_promocion']);
// FIN PROMOCION

// OBTENIENDO CONTENIDO ROLL
$sentenciaSQL1 = $conexion->prepare("SELECT DISTINCT roll.id_roll, roll.nombre_roll,
(SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.cobertura AND insumo.tipo_insumo = 1) AS cobertura,
(SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.proteina AND insumo.tipo_insumo = 2) AS proteina,
(SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.vegetal_1 AND insumo.tipo_insumo = 3) AS vegetal_1,
(SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.vegetal_2 AND insumo.tipo_insumo = 3) AS vegetal_2
FROM roll WHERE roll.id_promocion = :id_promocion");
$sentenciaSQL1->bindParam(':id_promocion', $id_promocion, PDO::PARAM_INT);
$sentenciaSQL1->execute();
$listaroll = $sentenciaSQL1->fetchAll(PDO::FETCH_ASSOC);

// FIN CONTENIDO ROLL

// OBTENIENDO CONTENIDO SNACK
$sentencia_snack = $conexion->prepare("SELECT DISTINCT snack.id_snack, snack.nombre_snack , snack.cantidad 
FROM snack WHERE snack.id_promocion = :id_promocion");
$sentencia_snack->bindParam(':id_promocion', $id_promocion, PDO::PARAM_INT);
$sentencia_snack->execute();
if ($sentencia_snack->rowCount() > 0) {
    $listasnack = $sentencia_snack->fetchAll(PDO::FETCH_ASSOC);
}
// FIN CONTENIDO SNACK

?>
<div class="col-md-4">
    <div class="card">
        <div class="card-header text-white bg-primary mb-3 d-flex justify-content-center">
            Detalle
        </div>
        <div class="card-body">
            <form action="#" method="POST" enctype="multipart/form-data">
                <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                    <input type="radio" class="btn-check" name="tipo" id="roll" value="roll" <?php echo ($tipo == "roll" || $tipo == "") ? "checked" : ""; ?> style="margin-right: 10px;">
                    <label class="btn btn-outline-primary" style="margin-right: 10px;" for="roll">Roll</label>
                    <br>
                    <input type="radio" class="btn-check" name="tipo" id="snack" value="snack" <?php echo ($tipo == "snack") ? "checked" : ""; ?> style="margin-right: 10px;">
                    <label class="btn btn-outline-primary" style="margin-right: 10px;" for="snack">Snack</label>
                </div>
                <br>
                <br>
                <div id="campos_roll" style="display: none;">
                    Nombre Roll
                    <input type="text" class="form-control" value="<?php echo $nombre_roll; ?>" name="nombre_roll"
                        id="nombre_roll" placeholder="Nombre Roll">
                    <div>
                        <label for="exampleSelect1" class="form-label mt-4">Cobertura</label>
                        <select class="form-select" id="cobertura" name="cobertura">
                            <?php
                            $coberturaStmt = $conexion->prepare("SELECT id_insumo, nombre_insumo FROM insumo WHERE tipo_insumo = 1");
                            $coberturaStmt->execute();
                            while ($fila = $coberturaStmt->fetch(PDO::FETCH_ASSOC)) {
                                $selected = ($fila['id_insumo'] == $cobertura) ? 'selected' : '';
                                echo "<option value=\"{$fila['id_insumo']}\" $selected>{$fila['nombre_insumo']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="exampleSelect3" class="form-label mt-4">Proteina</label>
                        <select class="form-select" id="proteina" name="proteina">
                            <?php
                            $proteinaStmt = $conexion->prepare("SELECT id_insumo, nombre_insumo FROM insumo WHERE tipo_insumo = 2");
                            $proteinaStmt->execute();
                            while ($fila = $proteinaStmt->fetch(PDO::FETCH_ASSOC)) {
                                $selected = ($fila['id_insumo'] == $proteina) ? 'selected' : '';
                                echo "<option value=\"{$fila['id_insumo']}\" $selected>{$fila['nombre_insumo']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="exampleSelect2" class="form-label mt-4">Vegetal 1</label>
                        <select class="form-select" id="vegetal1" name="vegetal1">
                            <?php
                            $vegetalStmt = $conexion->prepare("SELECT id_insumo, nombre_insumo FROM insumo WHERE tipo_insumo = 3");
                            $vegetalStmt->execute();
                            while ($fila = $vegetalStmt->fetch(PDO::FETCH_ASSOC)) {
                                $selected = ($fila['id_insumo'] == $vegetal1) ? 'selected' : '';
                                echo "<option value=\"{$fila['id_insumo']}\" $selected>{$fila['nombre_insumo']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="exampleSelect4" class="form-label mt-4">Vegetal 2</label>
                        <select class="form-select" id="vegetal2" name="vegetal2">
                            <?php
                            $vegetalStmt = $conexion->prepare("SELECT id_insumo, nombre_insumo FROM insumo WHERE tipo_insumo = 3");
                            $vegetalStmt->execute();
                            while ($fila = $vegetalStmt->fetch(PDO::FETCH_ASSOC)) {
                                $selected = ($fila['id_insumo'] == $vegetal2) ? 'selected' : '';
                                echo "<option value=\"{$fila['id_insumo']}\" $selected>{$fila['nombre_insumo']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div id="campo_otro" style="display: none;">
                    Nombre <input type="text" name="nombre_snack" class="form-control"
                        value="<?php echo $nombre_snack; ?>" placeholder="Ej: Sashimi"><br>
                    Cantidad <input type="text" name="cantidad" class="form-control" value="<?php echo $cantidad; ?>"
                        placeholder="Unidades/Porciones"><br>
                </div>
                <br>
                <!-- BOTON ACCION -->
                <div class="btn-group d-flex justify-content-center" role="group" aria-label="">
                    <button type="submit" name="accion" <?php echo ($accion == "Seleccionar") ? "disabled" : ""; ?>
                        value="Agregar" class="btn btn-primary">Agregar</button>
                    <button type="submit" name="accion" <?php echo ($accion != "Seleccionar") ? "disabled" : ""; ?>
                        value="Modificar" class="btn btn-warning">Modificar</button>
                    <button type="submit" name="accion" value="Cancelar" class="btn btn-info">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="col-md-8">
    <table class="table table-bordered">
        <thead style="text-align: center;">
            <tr class="table-primary">
                <td colspan="4">Nombre : <?php echo $nombre_promocion ?></td>
            </tr>
            <tr class="table-primary">
                <th style="font-size: 18px;">Categoria</th>
                <th style="font-size: 18px;"> Ítem</th>
                <th style="font-size: 18px;">Detalle</th>
                <th style="font-size: 18px;">Modificar</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($listaroll)) { ?>
                <?php foreach ($listaroll as $roll) { ?>
                    <tr>
                        <td><?php echo "ROLL" ?></td>
                        <td><?php echo strtoupper($roll['nombre_roll']) ?></td>
                        <td>
                            Cobertura: <?php echo $roll['cobertura'] ?> <br>
                            Proteina: <?php echo $roll['proteina'] ?><br>
                            Vegetal 1: <?php echo $roll['vegetal_1'] ?><br>
                            Vegetal 2: <?php echo $roll['vegetal_2'] ?><br>
                        </td>
                        <td>
                            <div class="btn-group d-flex justify-content-center" role="group" aria-label="">
                                <form method="post">
                                    <input type="hidden" name="id_roll" id="id_roll" value="<?php echo $roll['id_roll'] ?>">
                                    <input type="submit" name="accion" value="Seleccionar" class="btn btn-primary">
                                    <input type="hidden" name="tipo_seleccionado" value="roll">
                                    <input type="submit" name="accion" value="Borrar" class="btn btn-danger">
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>

            <?php if (!empty($listasnack)) { ?>
                <?php foreach ($listasnack as $snack) { ?>
                    <tr>
                        <td><?php echo "SNACK" ?></td>
                        <td><?php echo strtoupper($snack['nombre_snack']) ?></td>
                        <td>
                            <?php echo $snack['nombre_snack'] ?> <br>
                            Cantidad: <?php echo $snack['cantidad'] ?><br>
                        </td>
                        <td>
                            <div class="btn-group d-flex justify-content-center" role="group" aria-label="">
                                <form method="post">
                                    <input type="hidden" name="id_snack" id="id_snack" value="<?php echo $snack['id_snack'] ?>">
                                    <input type="submit" name="accion" value="Seleccionar" class="btn btn-primary">
                                    <input type="hidden" name="tipo_seleccionado" value="snack">
                                    <input type="submit" name="accion" value="Borrar" class="btn btn-danger">
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
    </table>
</div>


<script>
    window.addEventListener('DOMContentLoaded', (event) => {
        // Mostrar el div correspondiente al radio button seleccionado al cargar la página
        const rollRadio = document.getElementById('roll');
        const snackRadio = document.getElementById('snack');
        const camposRoll = document.getElementById('campos_roll');
        const campoOtro = document.getElementById('campo_otro');

        function mostrarCampos() {
            if (rollRadio.checked) {
                camposRoll.style.display = 'block';
                campoOtro.style.display = 'none';
            } else if (snackRadio.checked) {
                camposRoll.style.display = 'none';
                campoOtro.style.display = 'block';
            }
        }

        // Mostrar los campos correctos al cargar la página
        mostrarCampos();

        // Función para mostrar u ocultar los divs según la opción seleccionada en el radio button
        document.querySelectorAll('input[name="tipo"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                mostrarCampos();
            });
        });

        // Marcar automáticamente el radio button correspondiente cuando se presiona "Seleccionar"
        const seleccionarButtons = document.querySelectorAll('input[value="Seleccionar"]');
        seleccionarButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const tipoSeleccionado = button.closest('form').querySelector('input[name="tipo_seleccionado"]').value;
                if (tipoSeleccionado === 'snack') {
                    snackRadio.checked = true;
                } else if (tipoSeleccionado === 'roll') {
                    rollRadio.checked = true;
                }
                mostrarCampos();
            });
        });
    });
</script>