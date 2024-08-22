<?php
session_start();
include ("template/cabecera.php");

include ("administrador/config/bd.php"); //BD

// Inicializar o crear carrito
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = array();
}

// Limpiar el carrito al cargar la página por primera vez
if (!isset($_SESSION['pagina_cargada'])) {
    $_SESSION['carrito'] = array();
    $_SESSION['pagina_cargada'] = true;
}

// Procesar la acción del formulario - VARIABLES UTILIZADAS
$stock_disponible = isset($_POST['stock_disponible']) ? $_POST['stock_disponible'] : "";
$id_promocion = isset($_POST['id_promocion']) ? $_POST['id_promocion'] : '';
$comentarios = isset($_POST['comentarios']) ? $_POST['comentarios'] : '';

// Obtener todas las promociones
$sentenciaSQL = $conexion->prepare("SELECT * FROM promocion");
$sentenciaSQL->execute();
$listapromocion = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);


// GESTIONES DE CARRITO
$accion = (isset($_POST['accion'])) ? $_POST['accion'] : "";

switch ($accion) {
    case "addCarrito"; //AGREGAR AL CARRO
        if (!in_array($id_promocion, array_column($_SESSION['carrito'], 'id_promocion'))) {
            foreach ($listapromocion as $promocion) {
                if ($promocion['id_promocion'] == $id_promocion) {

                    $_SESSION['carrito'][] = array(
                        'id_promocion' => $promocion['id_promocion'],
                        'nombre_promocion' => $promocion['nombre_promocion'],
                        'precio' => $promocion['precio'],
                        'comentarios' => $comentarios
                    );
                    break;
                }
            }
        }
        //continuar con la orden de compra
        break;

    case "Deshacer";
        if (!empty($_SESSION['carrito'])) {
            array_pop($_SESSION['carrito']); // Eliminar el último elemento del carrito
        }
        break;

    case "Vaciar";
        $_SESSION['carrito'] = array(); // Vaciar completamente el carrito
        break;
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
                            <p class="card-text">VALOR $<?php echo $promocion['precio']; ?></p>
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
                                <button type="button" class="btn btn-warning openModalBtn" title="Ver detalle"
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
    <div class="card border-dark mb-1"
        style="min-height: 5rem; max-height: 20rem; max-width: 20rem; height: auto; overflow-y: auto;">
        <div class="card-header d-flex justify-content-center">
            <h4>Orden en proceso</h4>
        </div>
        <div class="card-body">
            <?php if (!empty($_SESSION['carrito'])) { ?>
                <?php
                $total = 0;
                foreach ($_SESSION['carrito'] as $item) {
                    $total += $item['precio'];
                    ?>
                    <div class="d-flex justify-content-between">
                        <span><?php echo strtoupper($item['nombre_promocion']); ?></span>
                        <span>$<?php echo $item['precio']; ?></span>
                    </div>
                <?php } ?>
                <div class="d-flex justify-content-between">
                    <span><strong>TOTAL</strong></span>
                    <span><strong>$<?php echo $total; ?></strong></span>
                </div>
            <?php } else { ?>
                <i> Agregue una promoción para comenzar una orden. </i>
            <?php } ?>
        </div>
        <div class="card-footer">
            <?php if (!empty($_SESSION['carrito'])) { ?>
                <form action="#" method="POST" enctype="multipart/form-data">
                    <div class="btn-group d-flex justify-content-center" role="group" aria-label="">
                        <button type="submit" name="accion" value="Continuar" class="btn btn-primary">Continuar</button>
                        <button type="submit" name="accion" value="Deshacer" class="btn btn-warning">Deshacer</button>
                        <button type="submit" name="accion" value="Vaciar" class="btn btn-info">Vaciar</button>
                    </div>
                </form>
            <?php } ?>
        </div>
    </div>

    <!-- Modal principal para detalles de la promoción -->
    <div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <p id="modalNombrePromocion" class="text-center" style="flex: 1;"></p>
                    </div>
                    <br>
                    <div id="modalRollsSnacks">
                        <!-- Detalles de rolls y snacks irán aquí -->
                    </div>
                    <div id="mensajeAlerta" class="alert alert-dismissible alert-danger">
                        <strong>!Atención! </strong>No están todos los insumos disponibles.
                    </div>
                    <div>
                        <label for="comentarios" class="form-label mt-2">Comentarios</label>
                        <textarea class="form-control" id="comentarios" rows="3"></textarea>
                    </div>
                    <p id="modalPrecio" class="text-right"></p>
                </div>

                <form action="#" method="POST" enctype="multipart/form-data">
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                        <input type="hidden" name="id_promocion" value="<?php echo $id_promocion; ?>">
                        <button type="submit" class="btn btn-warning" name="accion" value="addCarrito" <?php echo $stock_disponible ? '' : 'disabled'; ?>>Añadir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalReemplazarInsumo" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center" style="flex: 1;" id="modalReemplazarInsumoLabel">Reemplazo de Insumos
                    </h5>
                </div>
                <div class="modal-body" id="reemplazoContainer">
                    <!-- Aquí se cargarán las listas de selección de insumos faltantes -->
                </div>
                <div class="modal-footer" style="justify-content: center;">
                    <button type="button" class="btn btn-lg btn-success w-100" id="guardarReemplazos">Guardar
                        cambios</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $('#modalForm').on('show.bs.modal', function (e) {
                var button = $(e.relatedTarget);
                var id_promocion = button.data('id');
                $(this).find('input[name="id_promocion"]').val(id_promocion);
            });

            $('.openModalBtn').click(function () {
                var button = $(this);
                var id_promocion = button.data('id');
                var nombre_promocion = button.data('nombre');
                var precio = button.data('precio');

                $('#modalNombrePromocion').text(nombre_promocion).data('id', id_promocion); // Guarda el ID en el elemento
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

            $('#guardarReemplazos').click(function () {
                var listaReemplazos = [];

                $('#reemplazoContainer select').each(function () {
                    var tipo = $(this).closest('div').find('label').text().split('-')[0].trim(); // Extraer el tipo
                    var insumoReemplazado = $(this).attr('name').replace('insumo_', ''); // Obtener el nombre del insumo reemplazado
                    var insumoNuevoID = $(this).val(); // Obtener el ID del insumo nuevo
                    var insumoNuevoNombre = $(this).find('option:selected').text(); // Obtener el nombre del insumo nuevo

                    if (insumoNuevoID) { // Si hay un insumo seleccionado
                        listaReemplazos.push({
                            tipo: tipo,
                            insumoReemplazado: insumoReemplazado,
                            insumoNuevoNombre: insumoNuevoNombre
                        });

                        // Si se selecciona un insumo se desbloquea el botón "addCarrito" y se oculta el mensaje de alerta
                        $('button[name="accion"][value="addCarrito"]').prop('disabled', false); // Obteniendo button por su value
                        mensajeAlerta.style.display = "none";
                    }
                });

                var mensajes = listaReemplazos.map(function (item) {
                    return `${item.tipo} ${item.insumoReemplazado} fue reemplazado por ${item.insumoNuevoNombre}`;
                });

                $('#comentarios').val(mensajes.join('\n')); // Enviar los mensajes a la textarea 

                $('#modalReemplazarInsumo').modal('hide'); // Cerrar modal
            });
        });
    </script>


    <?php include ("template/piepagina.php"); ?>