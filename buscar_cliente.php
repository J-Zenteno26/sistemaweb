<?php
// Incluir la conexión a la base de datos
include("administrador/config/bd.php"); // BD

if (isset($_POST['telefono'])) {
    $telefono = $_POST['telefono'];

    try {
        // Consulta para buscar el cliente por número de teléfono
        $sentenciaSQL_cliente = $conexion->prepare("SELECT id_cliente, nombre_cliente, telefono, direccion, referencia FROM cliente WHERE telefono LIKE :telefono");
        $telefonoParam = "%{$telefono}%";
        $sentenciaSQL_cliente->bindParam(':telefono', $telefonoParam);
        $sentenciaSQL_cliente->execute();

        $cliente = $sentenciaSQL_cliente->fetch(PDO::FETCH_ASSOC);

        if ($cliente) {
            echo json_encode($cliente); // Retornar los datos del cliente como JSON
        } else {
            echo json_encode(null); // No se encontró ningún cliente
        }
    } catch (PDOException $e) {
        echo json_encode(null); // En caso de error, retornar null
    }
}
?>
