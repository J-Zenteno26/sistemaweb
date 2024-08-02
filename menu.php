<?php
session_start();
include ("template/cabecera.php");

// Limpiar el carrito al cargar la página por primera vez
if (!isset($_SESSION['pagina_cargada'])) {
    $_SESSION['carrito'] = array();
    $_SESSION['pagina_cargada'] = true;
}

// Inicializar o crear carrito
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = array();
}

// Procesar la acción del formulario - VARIABLES UTILIZADAS
$accion = isset($_POST['accion']) ? $_POST['accion'] : "";
$seleccion = isset($_POST['seleccion']) ? $_POST['seleccion'] : "";
$editar = isset($_POST['editar']) ? $_POST['editar'] : "";
$stock_disponible = isset($_POST['stock_disponible']) ? $_POST['stock_disponible'] : "";

include ("administrador/config/bd.php"); //BD

// Obtener todas las promociones
$sentenciaSQL = $conexion->prepare("SELECT * FROM promocion");
$sentenciaSQL->execute();
$listapromocion = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);

if ($accion == "Agregar") {
    // Obtengo la ID promocion seleccionada
    $id_promocion = $_POST['id_promocion'];
    // Añadir la promoción al carrito
    if (!in_array($id_promocion, array_column($_SESSION['carrito'], 'id_promocion'))) {
        foreach ($listapromocion as $promocion) {
            if ($promocion['id_promocion'] == $id_promocion) {
                $_SESSION['carrito'][] = $promocion;
                break;
            }
        }
    }
} else if ($accion == "Deshacer") {
    array_pop($_SESSION['carrito']);

} else if ($accion == "Eliminar") {
    $_SESSION['carrito'] = array();
}
?>
<style>
    .card-container {
        perspective: 1000px;
        width: calc(25% - 60px);
        margin: 20px;
        box-sizing: border-box;
    }

    .cards-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
    }

    .card {
        position: relative;
        width: 100%;
        height: 100%;
        transition: transform 0.6s;
        transform-style: preserve-3d;
        border: 1px solid #007bff;
        border-radius: 1rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        min-height: 500px;
    }

    .card-container:hover .card {
        transform: rotateY(180deg);
    }

    .card-front,
    .card-back {
        position: absolute;
        width: 100%;
        height: 100%;
        backface-visibility: hidden;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 10px;
        box-sizing: border-box;
    }

    .card-front {
        z-index: 2;
        transform: rotateY(0deg);
    }

    .card-back {
        transform: rotateY(180deg);
    }

    .card-img-top {
        height: 200px;
        object-fit: cover;
    }

    .card-body {
        padding: 5px;
        text-align: left;
    }

    .card-title {
        font-size: 1.50em;
        margin-bottom: 10px;
        font-weight: bold;
    }

    .card-text {
        margin-bottom: 10px;
        text-align: center;
    }

    .custom-border {
        border: 2px solid #000;
        border-radius: 5px;
        padding: 10px;
    }

    .modal-title.text-center {
        width: 100%;
        text-align: center;
        margin: 0 auto;
    }

    .modal-body .text-center {
        width: 100%;
        text-align: center;
        margin: 0 auto;
    }

    .modal-body .text-right {
        width: 100%;
        text-align: right;
        margin: 0;
    }

    .modal-body .editarPromocionBtn {
        float: right;
    }

    .flex-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-darken {
        background-color: rgba(0, 0, 0, 0.5);
        pointer-events: none;
        color: transparent;
        /* Hacer el texto invisible */
    }

    .modal-darken * {
        pointer-events: none;
        /* Desactivar interactividad de todos los elementos hijos */
    }

    .modal-darken textarea,
    .modal-darken button {
        background-color: rgba(0, 0, 0, 0.5) !important;
        color: transparent !important;
        /* Hacer el texto invisible */
        border: none;
        /* Remover el borde si es necesario */
    }
</style>

<div class="col-md-9">
    <div class="cards-container">
        <?php foreach ($listapromocion as $promocion) { ?>
            <div class="card-container">
                <div class="card">
                    <div class="card-front">
                        <div class="card-header">
                            <img class="card-img-top" src="./img/<?php echo $promocion['imagen']; ?>" alt=""
                                style="border-radius: 50%; border: 2px solid #007bff;">
                        </div>
                        <br>
                        <br>
                        <div class="card-body">
                            <h3 class="card-title"><?php echo strtoupper($promocion['nombre_promocion']); ?></h3>
                            <p class="card-text">Valor $<?php echo $promocion['precio']; ?></p>
                        </div>
                    </div>
                    <div class="card-back">
                        <div class="card-body">
                            <p class="card-text">
                                <?php
                                $sentenciaSQL1 = $conexion->prepare("SELECT DISTINCT roll.id_roll, roll.nombre_roll,
                                    (SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.cobertura AND insumo.tipo_insumo = 1) AS cobertura,
                                    (SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.proteina AND insumo.tipo_insumo = 2) AS proteina,
                                    (SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.vegetal_1 AND insumo.tipo_insumo = 3) AS vegetal_1,
                                    (SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.vegetal_2 AND insumo.tipo_insumo = 3) AS vegetal_2
                                    FROM roll WHERE roll.id_promocion = :id_promocion");
                                $sentenciaSQL1->bindParam(':id_promocion', $promocion['id_promocion'], PDO::PARAM_INT);
                                $sentenciaSQL1->execute();
                                $listaroll = $sentenciaSQL1->fetchAll(PDO::FETCH_ASSOC);

                                $sentencia_snack = $conexion->prepare("SELECT DISTINCT snack.id_snack, snack.nombre_snack, snack.cantidad 
                                    FROM snack WHERE snack.id_promocion = :id_promocion");
                                $sentencia_snack->bindParam(':id_promocion', $promocion['id_promocion'], PDO::PARAM_INT);
                                $sentencia_snack->execute();
                                $listasnack = $sentencia_snack->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <?php if (!empty($listaroll)) { ?>
                                <ul>
                                    <?php foreach ($listaroll as $roll) { ?>
                                        <li style="margin-bottom: 20px;">
                                            <strong>Roll </strong> '<?php echo strtolower($roll['nombre_roll']); ?>':<br>
                                            <strong>Cobertura </strong> <?php echo $roll['cobertura']; ?> <br>
                                            <strong>Proteina</strong> <?php echo $roll['proteina']; ?> <br>
                                            <strong>Vegetales </strong><?php echo $roll['vegetal_1']; ?> y
                                            <?php echo $roll['vegetal_2']; ?>.
                                        </li>
                                    <?php } ?>
                                </ul>
                            <?php } ?>
                            <?php if (!empty($listasnack)) { ?>
                                <ul>
                                    <?php foreach ($listasnack as $snack) { ?>
                                        <li style="margin-bottom: 20px;">
                                            <strong>Snack:</strong> <?php echo strtolower($snack['nombre_snack']); ?> -
                                            <?php echo $snack['cantidad']; ?> unid.
                                        </li>
                                    <?php } ?>
                                </ul>
                            <?php } ?>
                            </p>
                            <div class="btn-group d-flex justify-content-center" role="group" aria-label="">
                                <button type="button" class="btn btn-primary openModalBtn" title="Añadir a la orden"
                                    data-toggle="modal" data-target="#modalForm"
                                    data-id="<?php echo $promocion['id_promocion']; ?>"
                                    data-nombre="<?php echo strtoupper($promocion['nombre_promocion']); ?>"
                                    data-precio="<?php echo $promocion['precio']; ?>">Agregar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<div class="col-md-3">
    <table class="table table-bordered">
        <thead style="text-align: center;">
            <tr class="table-primary">
                <td colspan="4">Resumen Órdenes</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <?php if (!empty($_SESSION['carrito'])) { ?>
                    <td>
                        <?php foreach ($_SESSION['carrito'] as $item) { ?>
                            <li><?php echo strtoupper($item['nombre_promocion']); ?> - $<?php echo $item['precio']; ?></li>
                        <?php } ?>
                    </td>
                <?php } else { ?>
                    <td style="text-align: center;">
                        <i> Agregue una promoción para comenzar una orden. </i>
                    </td>
                <?php } ?>
            </tr>
            <tr>
                <td>
                    <div class="btn-group d-flex justify-content-center" role="group" aria-label="">
                        <?php if (!empty($_SESSION['carrito'])) { ?>
                            <form method="post" action="procesar_orden.php">
                                <input type="submit" name="accion" value="Emitir Orden" class="btn btn-info"
                                    style="margin-right: 10px;">
                            </form>
                            <form method="post">
                                <input type="submit" name="accion" value="Deshacer"
                                    title="Deshace el ultimo registro añadido" class="btn btn-warning"
                                    style="margin-right: 10px;">
                                <input type="submit" name="accion" value="Eliminar" title="Elimina el carrito"
                                    class="btn btn-danger">
                            </form>
                        <?php } ?>
                    </div>
                </td>

            </tr>
        </tbody>
    </table>
</div>

<!-- Modal principal para detalles de la promoción -->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header 
            <div class="modal-header">
                <h4 class="modal-title text-center" id="modalTitle" style="flex: 1;">Contenido</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>-->

            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center">
                    <p id="modalNombrePromocion" class="text-center" style="flex: 1;"></p>
                </div>
                <br>
                <div id="modalRollsSnacks">
                    <!-- Detalles de rolls y snacks irán aquí -->
                </div>

                <div>
                    <label for="exampleTextarea" class="form-label mt-2">Comentarios</label>
                    <textarea class="form-control" id="exampleTextarea" rows="3"></textarea>
                </div>
                <p id="modalPrecio" class="text-right"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" id="addOrderBtn" <?php echo $stock_disponible ? '' : 'disabled'; ?>>Añadir</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalReemplazarInsumo" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-center" style="flex: 1;" id="modalReemplazarInsumoLabel">Insumos Faltantes</h5>
                </button>
            </div>
            <div class="modal-body" id="reemplazoContainer">
                <!-- Aquí se cargarán las listas de selección de insumos faltantes -->
            </div>
            <div class="modal-footer" style="justify-content: center;">
                <button type="button" class="btn btn-lg btn-primary w-100" id="guardarReemplazos">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Código para abrir el modal de detalles de promoción
        $('.openModalBtn').click(function () {
            var button = $(this);
            var id_promocion = button.data('id');
            var nombre_promocion = button.data('nombre');
            var precio = button.data('precio');

            $('#modalNombrePromocion').text(nombre_promocion);
            $('#modalPrecio').text('Precio $' + precio);

            $.ajax({
                url: 'obtener_detalles_promocion.php',
                type: 'POST',
                data: { id_promocion: id_promocion },
                success: function (data) {
                    $('#modalRollsSnacks').html(data);
                    $('#modalForm').modal('show');
                }
            });
        });
    });
</script>


<?php include ("template/piepagina.php"); ?>