<?php

// Define la función actualizarStock fuera de la condición para que sea accesible
function actualizarStock($id_stock, $nueva_porcion) {
    // Conexión a la base de datos (debes tener tu propia lógica para establecer la conexión)
    $conexion = new mysqli("localhost", "root", "", "sistema");

    // Verificar la conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Preparar la consulta SQL para actualizar el stock
    $sql = "UPDATE stock SET porcion='$nueva_porcion', fecha_modificacion=NOW() WHERE id_stock=$id_stock";
    echo "Consulta SQL: $sql";

    // Ejecutar la consulta
    if ($conexion->query($sql) === TRUE) {
        echo "Stock actualizado correctamente.";
    } else {
        echo "Error al actualizar el stock: " . $conexion->error;
    }

    // Cerrar la conexión
    $conexion->close();
}

if(isset($_POST['id']) && isset($_POST['newValue'])) {
    $id = $_POST['id'];
    $newValue = $_POST['newValue'];
    actualizarStock($id, $newValue);

} else {
    echo "Error: Los parámetros id y newValue no están presentes en la solicitud.";
}

?>
