<?php include("template/cabecera.php"); ?>

<?php
$id_cliente = (isset($_POST['id_cliente'])) ? $_POST['id_cliente'] : "";
$nombre_cliente = (isset($_POST['nombre_cliente'])) ? $_POST['nombre_cliente'] : "";
$telefono = (isset($_POST['telefono'])) ? $_POST['telefono'] : "";
$direccion = (isset($_POST['direccion'])) ? $_POST['direccion'] : "";
$accion = (isset($_POST['accion'])) ? $_POST['accion'] : "";

//CONEXIÓN BD
include("administrador/config/bd.php");

switch ($accion) {
    //INSERT//
    case "Agregar":
        //INSERT INTO `cliente`(`id_cliente`, `nombre_cliente`, `telefono`, `direccion`) VALUES ('[value-1]','[value-2]','[value-3]','[value-4]')
        $sentenciaSQL = $conexion->prepare("INSERT INTO cliente (id_cliente, nombre_cliente, telefono, direccion) VALUES (NULL, :nombre_cliente, :telefono, :direccion);");
        $sentenciaSQL->bindParam(':nombre_cliente', $nombre_cliente);
        $sentenciaSQL->bindParam(':telefono', $telefono);
        $sentenciaSQL->bindParam(':direccion', $direccion);
        $sentenciaSQL->execute();

        header("Location:clientes.php");
        break;

    case "Modificar":
        echo "Modificando cliente: $id_cliente\n";
        echo "Nombre: $nombre_cliente, Teléfono: $telefono, Dirección: $direccion\n";

        $sentenciaSQL = $conexion->prepare("UPDATE cliente SET nombre_cliente=:nombre_cliente, telefono=:telefono, direccion=:direccion WHERE id_cliente=:id_cliente");
        $sentenciaSQL->bindParam(':nombre_cliente', $nombre_cliente);
        $sentenciaSQL->bindParam(':telefono', $telefono);
        $sentenciaSQL->bindParam(':direccion', $direccion);
        $sentenciaSQL->bindParam(':id_cliente', $id_cliente);

        if ($sentenciaSQL->execute()) {
            echo "Cliente modificado exitosamente.\n";
        } else {
            echo "Error al modificar cliente.\n";
        }

        // Obtener la consulta SQL
        $query = $sentenciaSQL->queryString;
        echo "Consulta SQL: $query\n"; // Imprimir consulta SQL en consola
        echo "id_cliente: $id_cliente\n";

        header("Location:clientes.php");
        break;

    case "Cancelar":
        header("Location:clientes.php");
        break;

    case "Seleccionar":
        $sentenciaSQL = $conexion->prepare("SELECT * FROM cliente WHERE id_cliente=:id_cliente");
        $sentenciaSQL->bindParam(':id_cliente', $id_cliente);
        $sentenciaSQL->execute();
        $cliente = $sentenciaSQL->fetch(PDO::FETCH_LAZY);

        //ASIGNANDO A FORMULARIO
        $nombre_cliente = $cliente['nombre_cliente'];
        $telefono = $cliente['telefono'];
        $direccion = $cliente['direccion'];
        break;

    case "Borrar":
        $sentenciaSQL = $conexion->prepare("DELETE FROM cliente WHERE id_cliente=:id_cliente");
        $sentenciaSQL->bindParam(':id_cliente', $id_cliente);
        $sentenciaSQL->execute();
        $promocion = $sentenciaSQL->fetch(PDO::FETCH_LAZY);

        header("Location:clientes.php");
        break;
}

$sentenciaSQL = $conexion->prepare("SELECT * FROM cliente");
$sentenciaSQL->execute();
$listaclientes = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- CRUD -->
<div class="col-md-4">
    <div class="card">
        <div class="card-header text-white bg-primary mb-3 d-flex justify-content-center">
            Datos cliente
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_cliente" id="id_cliente" value="<?php echo $id_cliente; ?>">

                <!-- NOMBRE -->
                <div class="form-group">
                    <label for="nombre_cliente">Nombre</label>
                    <input type="text" required class="form-control" value="<?php echo $nombre_cliente; ?>" name="nombre_cliente" id="nombre_cliente" placeholder="Nombre y apellido">
                    <br>
                </div>

                <!-- FONO -->
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="number" required class="form-control" value="<?php echo $telefono; ?>" name="telefono" id="telefono" placeholder="Ej: 9 999 999 99">
                    <br>
                </div>

                <!-- DIRECCION -->
                <div class="form-group">
                    <label for="direccion">Dirección</label>
                    <input type="text" required class="form-control" value="<?php echo $direccion; ?>" name="direccion" id="direccion" placeholder="Ej: Av. Juan Fernandez 23">
                </div>
                <br>

                <!-- BOTON ACCION -->
                <div class="btn-group d-flex justify-content-center" role="group" aria-label="">
                    <button type="submit" name="accion" value="Agregar" class="btn btn-success">Agregar</button>
                    <button type="submit" name="accion" value="Modificar" class="btn btn-warning" <?php echo ($accion != "Seleccionar") ? "disabled" : ""; ?>>Modificar</button>
                    <button type="submit" name="accion" value="Cancelar" class="btn btn-info">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- TABLA DE PRODUCTOS-->
<div class="col-md-8">
    <table class="table table-bordered">
        <thead style="text-align: center;">
            <tr class="table-primary"> <!-- HEAD -->
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody> <!-- BODY -->
            <?php foreach ($listaclientes as $cliente) { ?>
                <tr>
                    <td><?php echo $cliente['nombre_cliente'] ?></td>
                    <td><?php echo $cliente['telefono'] ?></td>
                    <td><?php echo $cliente['direccion'] ?></td>

                    <td>
                        <div class="btn-group d-flex justify-content-center" role="group" aria-label="">
                            <form method="post">
                                <input type="hidden" name="id_cliente" id="id_cliente" value="<?php echo $cliente['id_cliente'] ?>">
                                <input type="submit" name="accion" value="Seleccionar" class="btn btn-success">
                                <input type="submit" name="accion" value="Borrar" class="btn btn-danger">
                            </form>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php include("template/piepagina.php"); ?>
