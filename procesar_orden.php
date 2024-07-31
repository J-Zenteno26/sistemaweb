<?php
session_start();

if ($_POST['accion'] == 'Emitir Orden') {
    // Procesar la orden de compra
    // Guardar la orden en la base de datos, enviar confirmación, etc.

    // Vaciar el carrito
    $_SESSION['carrito'] = array();

    echo "Orden de compra emitida exitosamente.";
}
?>