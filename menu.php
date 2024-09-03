<?php
session_start();
include("template/cabecera.php");

include("administrador/config/bd.php"); //BD

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = []; // Inicializa el carrito como un arreglo vacío
}
$id_promocion = isset($_POST['id_promocion']) ? $_POST['id_promocion'] : '';
$total = isset($_POST['total']) ? $_POST['total'] : '';
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
        margin: 5px;
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
        min-height: 400px;
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
    }

    .modal-darken * {
        pointer-events: none;
    }

    .modal-darken textarea,
    .modal-darken button {
        background-color: rgba(0, 0, 0, 0.5) !important;
        color: transparent !important;
        border: none;
    }

    .btn-form1 {
        text-align: center;
    }

    .radio-group1 {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
    }
</style>

<div class="col-md-8">
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
                        <div class="card-body">
                            <h5 class="card-title" style="text-align: center;">
                                <?php echo strtoupper($promocion['nombre_promocion']); ?>
                            </h5>
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

<div class="col-md-4">
    <div id="carrito" class="card border-dark mb-1"
        style="min-height: 5rem; max-height: 20rem; max-width: 28rem; height: auto; overflow-y: auto;">
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
                    <div class="d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <li><span><?php echo strtoupper($item['nombre_promocion']); ?></span></li>
                            <span>$<?php echo $item['precio']; ?></span>
                        </div>
                        <div class="text-end mt-2">
                            <span><em>*<?php echo htmlspecialchars($comentarios); ?></em>.</span>
                        </div>
                    </div>
                <?php } ?>
                <hr>
                <div class="d-flex justify-content-between">
                    <li><span><strong>TOTAL</strong></span></li>
                    <input type="hidden" id="total" value="<?php echo $total; ?>">
                    <span><strong>$<?php echo $total; ?></strong></span>
                </div>
            <?php } else { ?>
                <div class="d-flex justify-content-center">
                    <i>Agregue una promoción para comenzar una orden.</i>
                </div>
            <?php } ?>
        </div>

        <div class="card-footer">
            <?php if (!empty($_SESSION['carrito'])) { ?>
                <div class="d-flex justify-content-center" role="group" aria-label="">
                    <button type="button" class="btn btn-success rounded me-2" id="continuarCompra">Continuar</button>
                    <form method="POST" action="#" class="d-flex">
                        <button type="submit" name="accion" value="Deshacer"
                            class="btn btn-warning rounded me-2">Deshacer</button>
                        <button type="submit" name="accion" value="Vaciar" class="btn btn-danger rounded">Vaciar</button>
                    </form>
                </div>
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
                    <form method="POST" action="#">
                        <div>
                            <label class="form-label mt-2">Comentarios</label>
                            <textarea class="form-control" name="comentarios" id="comentarios"
                                rows="2"><?php echo htmlspecialchars($comentarios); ?></textarea>
                        </div>
                        <input type="hidden" name="id_promocion" value="<?php echo $id_promocion; ?>">
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-warning" name="accion"
                                value="addCarrito">Añadir</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal reemplazos -->
    <div class="modal fade" id="modalReemplazarInsumo" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center" style="flex: 1;" id="modalReemplazarInsumoLabel">Reemplazo de
                        Insumos
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

    <!-- Modal clientes -->
    <div class="modal fade" id="modalCliente" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center" style="flex: 1; ">INFORMACIÓN DEL CLIENTE</h5>
                </div>
                <div class="modal-body" id="clienteContainer">
                    <div class="form-group " style="text-align: center;">
                        <label for="telefono_cliente"><strong> Teléfono (Identificador)</strong></label>
                        <input type="text" id="telefono_cliente" class="form-control"
                            style="text-align: center; width: 90%; margin: 0 auto;"
                            placeholder="Ingrese teléfono para realizar búsqueda en sistema">
                        <div id="resultadoBusqueda"></div> <!-- Aquí aparecerán los resultados de la búsqueda -->
                    </div>
                    <div class="alert alert-warning" id="alertaCliente" style="display: none;"></div>
                    <br>
                    <form id="formCliente">
                        <div style="width: 90%; margin: 0 auto;">
                            <input type="hidden" id="id_cliente" name="id_cliente">
                            <label for="nombre_cliente"><strong>Nombre</strong></label>
                            <input type="text" id="nombre_cliente" name="nombre_cliente" class="form-control">

                            <label for="direccion_cliente"><strong>Dirección</strong></label>
                            <input type="text" id="direccion_cliente" name="direccion_cliente" class="form-control">

                            <label for="referencia_cliente"><strong>Referencia</strong></label>
                            <input type="text" id="referencia_cliente" name="referencia_cliente" class="form-control"
                                placeholder="Ingrese referencia del domicilio [Opcional]">
                        </div>
                        <br>
                        <div class="btn-form1" role="group" aria-label="Basic radio toggle button group">
                            <label><strong>OPCIÓN DE ENTREGA</strong></label>
                            <br>
                            <div class="radio-group1">
                                <input type="radio" class="btn-check" id="retiro" name="metodo_entrega" value="0">
                                <label class="btn btn-outline-primary" for="retiro">Retiro</label>

                                <input type="radio" class="btn-check" id="despacho" name="metodo_entrega" value="1">
                                <label class="btn btn-outline-primary" for="despacho">Despacho</label>
                            </div>
                        </div>
                        <br>
                        <div class="btn-form" style="text-align: center; " role="group"
                            aria-label="Basic checkbox toggle button group">
                            <label><strong>SELECCIONE MEDIO DE PAGO</strong></label>
                            <br> <br>
                            <input type="checkbox" class="btn-check" id="pago_efectivo" name="metodo_pago" value="0"
                                checked="">
                            <label class="btn btn-outline-primary" for="pago_efectivo">Efectivo</label>

                            <input type="checkbox" class="btn-check" id="pago_transferencia" name="metodo_pago"
                                value="1">
                            <label class="btn btn-outline-primary" for="pago_transferencia">Transferencia</label>

                            <input type="checkbox" class="btn-check" id="pago_debito" name="metodo_pago" value="2">
                            <label class="btn btn-outline-primary" for="pago_debito">Débito / Prepago</label>

                            <input type="checkbox" class="btn-check" id="pago_credito" name="metodo_pago" value="3">
                            <label class="btn btn-outline-primary" for="pago_credito">Crédito</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="justify-content: center;">
                    <button type="button" class="btn btn-lg btn-success w-100" id="Finalizar">Finalizar
                        compra</button>
                    <button type="button" class="btn btn-lg btn-danger w-100" id="limpiarCampos">Limpiar
                        campos</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    $(document).ready(function () {
        $('.openModalReemplazoBtn').click(function () {
            $('#modalReemplazarInsumo').modal('show');
            $('.modal-backdrop').addClass('modal-darken'); // Agregar clase al fondo
        });

        $('#modalReemplazarInsumo').on('hidden.bs.modal', function () {
            $('.modal-backdrop').removeClass('modal-darken'); // Eliminar clase cuando se cierra el modal
        });

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
                }
            });

            var mensajes = listaReemplazos.map(function (item) {
                return `${item.tipo} ${item.insumoReemplazado} fue reemplazado por ${item.insumoNuevoNombre}`;
            });

            $('#comentarios').val(mensajes.join('\n')); // Enviar los mensajes a la textarea 

            $('#modalReemplazarInsumo').modal('hide'); // Cerrar modal
        });


        // Buscar el teléfono cuando se escriba en el campo
        $('#telefono_cliente').on('input', function () {
            var telefono = $(this).val();

            if (telefono.length >= 3) { // Buscar si se han ingresado al menos 3 dígitos
                $.ajax({
                    url: 'buscar_cliente.php',
                    type: 'POST',
                    data: { telefono: telefono },
                    success: function (data) {
                        var cliente = JSON.parse(data);
                        if (cliente) {
                            $('#alertaCliente').html('Se encontró un cliente existente para el número de teléfono: ' + cliente.telefono + '. Haga click <u>aquí</u> para cargar los datos');
                            $('#alertaCliente').show();

                            // Manejar la confirmación para cargar los datos
                            $('#alertaCliente').click(function () {
                                $('#id_cliente').val(cliente.id_cliente);
                                $('#nombre_cliente').val(cliente.nombre_cliente);
                                $('#direccion_cliente').val(cliente.direccion);
                                $('#telefono_cliente').val(cliente.telefono);
                                $('#alertaCliente').hide(); // Ocultar la alerta después de confirmar
                            });
                        } else {
                            $('#alertaCliente').hide();
                        }
                    }
                });
            } else {
                $('#alertaCliente').hide();
            }
        });

        // Mostrar el modal cliente
        $('#continuarCompra').click(function () {
            $('#modalCliente').modal('show');
        });

        $('#limpiarCampos').click(function () {
            $('#formCliente')[0].reset();
            $('#telefono_cliente').val('');
            $('#alertaCliente').hide();
        });

        $("#Finalizar").click(function () {
            // Cerrar el modal del cliente si está abierto
            $('#modalCliente').modal('hide');

            var idsPromociones = [];
            <?php
            if (!empty($_SESSION['carrito'])) {
                foreach ($_SESSION['carrito'] as $item) {
                    echo 'idsPromociones.push("' . $item['id_promocion'] . '");';
                }
            }
            ?>

            var datosFormulario = {
                id_cliente: $("#id_cliente").val(),
                nombre_cliente: $("#nombre_cliente").val(),
                telefono_cliente: $("#telefono_cliente").val(),
                direccion_cliente: $("#direccion_cliente").val(),
                referencia_cliente: $("#referencia_cliente").val(),
                metodo_pago: $("input[name='metodo_pago']:checked").val(),
                metodo_entrega: $("input[name='metodo_entrega']:checked").val(),
                comentarios: $("#comentarios").val(),
                total: $("#total").val(),
                promociones: idsPromociones.join(", "), // Convierte el array en una cadena de texto
                action: 'process_order' // Añadir una acción para identificar la solicitud
            };

            // console.log(datosFormulario);

            $.ajax({
                type: "POST",
                url: "procesar_orden.php",
                data: datosFormulario,
                dataType: "json",
                success: function (response) {
                    if (response.error) {
                        alert(response.error);
                    } else if (response.modal) {
                        console.log(datosFormulario);
                        // Inyectar el HTML del modal en el documento
                        $('body').append(response.modal);

                        // Mostrar el modal usando Bootstrap
                        $('#modalBoleta').modal('show');
                    }
                },
                error: function () {
                    alert("Error al finalizar la compra.");
                }
            });
        });
    });
</script>

<?php include("template/piepagina.php"); ?>