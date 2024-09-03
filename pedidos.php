<?php
include("template/cabecera.php");
include("administrador/config/bd.php");

// ORDENES
$sentenciaOrdenes = $conexion->prepare("SELECT 
        id_orden, id_promocion, total, comentario,
        CASE 
            WHEN forma_pago = 0 THEN 'Efectivo'
            WHEN forma_pago = 1 THEN 'Transferencia'
            WHEN forma_pago = 2 THEN 'Débito/Prepago'
            WHEN forma_pago = 3 THEN 'Crédito'
            ELSE 'Sin datos'
        END AS forma_pago,
        CASE 
            WHEN metodo_entrega = 0 THEN 'Retiro'
            WHEN metodo_entrega = 1 THEN 'Despacho'
            ELSE 'Sin datos'
        END AS metodo_entrega, fecha, cliente_id_cliente FROM orden");
$sentenciaOrdenes->execute();
$lista_ordenes = $sentenciaOrdenes->fetchAll(PDO::FETCH_ASSOC);

// ROLLS
$sentenciaRolls = $conexion->prepare("SELECT roll.id_roll, roll.nombre_roll,
        (SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.cobertura AND insumo.tipo_insumo = 1) AS cobertura,
        (SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.proteina AND insumo.tipo_insumo = 2) AS proteina,
        (SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.vegetal_1 AND insumo.tipo_insumo = 3) AS vegetal_1,
        (SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.vegetal_2 AND insumo.tipo_insumo = 3) AS vegetal_2
    FROM roll WHERE roll.id_promocion = :id_promocion");
// SNACKS
$sentenciaSnacks = $conexion->prepare("SELECT snack.id_snack, snack.nombre_snack, snack.cantidad FROM snack
    WHERE snack.id_promocion = :id_promocion");

// CLIENTES
$sentenciaCliente = $conexion->prepare("SELECT id_cliente, nombre_cliente, telefono, direccion, referencia FROM cliente
    WHERE cliente.id_cliente = :id_cliente");

?>
<style>
    table tbody td {
        vertical-align: middle;
    }

    table thead tr {
        vertical-align: middle;
    }
</style>

<div style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); background-color: #f8f9fa; padding: 10px; margin-top: 20px;">
    <table class="table table-bordered mb-0">
        <thead style="text-align: center;">
            <tr>
                <th colspan="8" style="font-size: 1.5em; padding: 10px 0;" class="table-primary">ORDENES REALIZADAS</th>
            </tr>
            <tr class="table-primary">
                <th>ID</th>
                <th>CLIENTE</th>
                <th>PROMOCION</th>
                <th>COMENTARIO</th>
                <th>TOTAL</th>
                <th>PAGO</th>
                <th>ENTREGA</th>
                <th>FECHA</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lista_ordenes as $orden) {
                // Obtener detalles de rollos
                $sentenciaRolls->bindParam(':id_promocion', $orden['id_promocion'], PDO::PARAM_INT);
                $sentenciaRolls->execute();
                $rolls = $sentenciaRolls->fetchAll(PDO::FETCH_ASSOC);

                // Obtener detalles de snacks
                $sentenciaSnacks->bindParam(':id_promocion', $orden['id_promocion'], PDO::PARAM_INT);
                $sentenciaSnacks->execute();
                $snacks = $sentenciaSnacks->fetchAll(PDO::FETCH_ASSOC);

                // Obtener detalles de clientes
                $sentenciaCliente->bindParam(':id_cliente', $orden['cliente_id_cliente'], PDO::PARAM_INT);
                $sentenciaCliente->execute();
                $clientes = $sentenciaCliente->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($orden['id_orden']); ?></td>
                    <td style="text-align: center;">
                        <?php foreach ($clientes as $cliente) { ?>
                            <div>
                                ID: <strong><?php echo strtoupper(htmlspecialchars($cliente['telefono'])); ?></strong><br>
                                <?php echo strtoupper(htmlspecialchars($cliente['nombre_cliente'])); ?><br>
                            </div>
                        <?php } ?>
                    </td>
                    <td>
                        <?php foreach ($rolls as $roll) { ?>
                            <div>
                                <li><strong>Roll <?php echo strtolower(htmlspecialchars($roll['nombre_roll'])); ?></strong></li>
                                <strong>Cobertura - </strong><?php echo htmlspecialchars($roll['cobertura']); ?><br>
                                <strong>Proteína - </strong><?php echo htmlspecialchars($roll['proteina']); ?><br>
                                <strong>Vegetales - </strong><?php echo htmlspecialchars($roll['vegetal_1']); ?> y
                                <?php echo htmlspecialchars($roll['vegetal_2']); ?>.<br>
                            </div>
                        <?php } ?>

                        <?php foreach ($snacks as $snack) { ?>
                            <div>
                                <strong>Snack <?php echo strtoupper(htmlspecialchars($snack['nombre_snack'])); ?></strong><br>
                                Cantidad: <?php echo htmlspecialchars($snack['cantidad']); ?><br>
                            </div>
                        <?php } ?>
                    </td>
                    <td style="text-align: center;">
                        <?php echo !empty($orden['comentario']) ? htmlspecialchars($orden['comentario']) : 'Sin comentarios'; ?>
                    </td>
                    <td style="text-align: center;"><strong>$<?php echo htmlspecialchars($orden['total']); ?></strong></td>
                    <td style="text-align: center;"><?php echo htmlspecialchars($orden['forma_pago']); ?></td>
                    <td style="text-align: center;"><?php echo htmlspecialchars($orden['metodo_entrega']); ?></td>
                    <td style="text-align: center;"><?php echo htmlspecialchars($orden['fecha']); ?></td>
               
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
