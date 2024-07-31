<?php
include ("template/cabecera.php");
include ("administrador/config/bd.php"); //BD

?>
<div class="col-md-12">
    <h2>Carrito de Ordenes</h2>
    <?php if (!empty($_SESSION['carrito'])) { ?>
        <ul>
            <?php foreach ($_SESSION['carrito'] as $item) { ?>
                <li><?php echo strtoupper($item['nombre_promocion']); ?> - $<?php echo $item['precio']; ?></li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <p>El carrito está vacío.</p>
    <?php } ?>
</div>

<div class="col-md-12">
    <?php if (!empty($_SESSION['carrito'])) { ?>
        <form method="post" action="procesar_orden.php">
            <input type="submit" name="accion" value="Emitir Orden" class="btn btn-success">
        </form>
    <?php } ?>
</div>



<?php include ("template/piepagina.php"); ?>