<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_promocion = isset($_POST['id_promocion']) ? $_POST['id_promocion'] : '';
    $comentarios = isset($_POST['comentarios']) ? $_POST['comentarios'] : '';

        // Conectar a la base de datos
        include("administrador/config/bd.php");

        // Obtener la promoción por ID
        $sentenciaSQL = $conexion->prepare("SELECT * FROM promocion WHERE id_promocion = :id_promocion");
        $sentenciaSQL->bindParam(':id_promocion', $id_promocion);
        $sentenciaSQL->execute();
        $promocion = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);

        if ($promocion) {
            // Añadir la promoción al carrito
            $_SESSION['carrito'][] = array(
                'id_promocion' => $promocion['id_promocion'],
                'nombre_promocion' => $promocion['nombre_promocion'],
                'precio' => $promocion['precio'],
                'comentarios' => $comentarios
            );

            echo json_encode(array('status' => 'success', 'message' => 'Promoción agregada al carrito.'));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Promoción no encontrada.'));
        }        
}
?>
