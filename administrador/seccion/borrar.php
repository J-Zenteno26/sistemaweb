<?php
// Verificar si se recibió un ID válido a través del método GET
include ("../config/bd.php");


if (isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['tipo'])) {
    $id = $_GET['id'];
    $tipo = $_GET['tipo'];
    echo $id;
    echo $tipo;
    switch ($tipo) {
        case 'promocion':
            // Consulta para obtener la imagen antes de eliminarla
            $sentenciaSQL = $conexion->prepare("SELECT imagen FROM promocion WHERE id_promocion = :id_promocion");
            $sentenciaSQL->bindParam(':id_promocion', $id);
            $sentenciaSQL->execute();
            $promocion = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);

            // Si existe una imagen asociada al registro, elimínala
            if (isset($promocion["imagen"]) && $promocion["imagen"] != "imagen.jpg") {
                if (file_exists("../../img/" . $promocion["imagen"])) {
                    unlink("../../img/" . $promocion["imagen"]);
                }
            }

            // Eliminar el registro de la base de datos
            $sentenciaSQL = $conexion->prepare("DELETE FROM promocion WHERE id_promocion = :id_promocion");
            $sentenciaSQL->bindParam(':id_promocion', $id);
            $sentenciaSQL->execute();

            // Verificar si la eliminación fue exitosa y redirigir a la página de productos
            if ($sentenciaSQL->rowCount() > 0) {
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit();
            } else {
                echo "Error al eliminar el registro.";
            }
            break;

        case 'roll':
            $sentenciaSQL2 = $conexion->prepare("DELETE FROM `roll` WHERE id_roll = :id");
            $sentenciaSQL2->bindParam(':id', $id);
            $sentenciaSQL2->execute();

            if ($sentenciaSQL2->rowCount() > 0) {
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit();
            } else {
                echo "Error al eliminar el registro.";
            }
            break;

        case 'snack':
            $sentenciaSQL1 = $conexion->prepare("DELETE FROM `snack` WHERE id_snack = :id");
            $sentenciaSQL1->bindParam(':id', $id);
            $sentenciaSQL1->execute();

            if ($sentenciaSQL1->rowCount() > 0) {
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit();
            } else {
                echo "Error al eliminar el registro.";
            }
            break;

        default:
            echo "Tipo no válido.";
            break;
    }
} else {
    echo "ID o tipo no válidos.";
}






?>