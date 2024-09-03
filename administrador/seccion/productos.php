<?php include("../template/cabecera.php"); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<?php

// RECEPCIONANDO INFORMACION Y ARCHIVOS //
//Si hay algo en $ va a asignar y enviarf
$txtID = (isset($_POST['txtID'])) ? $_POST['txtID'] : "";
$txtNombre = (isset($_POST['txtNombre'])) ? $_POST['txtNombre'] : "";
$txtPrecio = (isset($_POST['txtPrecio'])) ? $_POST['txtPrecio'] : "";
$txtImagen = (isset($_FILES['txtImagen']['name'])) ? $_FILES['txtImagen']['name'] : "";
$accion = (isset($_POST['accion'])) ? $_POST['accion'] : "";
$seleccion = (isset($_POST['seleccion'])) ? $_POST['seleccion'] : "";
$editar = (isset($_POST['editar'])) ? $_POST['editar'] : "";


include("../config/bd.php");

switch ($accion) {

    case "Agregar";

        try {
            $sentenciaSQL = $conexion->prepare("INSERT INTO promocion (id_promocion, nombre_promocion, imagen, precio) 
            VALUES (NULL, :nombre_promocion, :imagen, :precio)");

            /////SECCION IMAGEN/////
            $fecha = new DateTime();
            $nombreArchivo = ($txtImagen != "") ? $fecha->getTimestamp() . "_" . $_FILES["txtImagen"]["name"] : "imagen.jpg";
            $tmpimagen = $_FILES["txtImagen"]["tmp_name"];

            /////MOVIENDO IMAGEN A CARPETA IMG/////
            if ($tmpimagen != "") {
                move_uploaded_file($tmpimagen, "../../img/" . $nombreArchivo);
            }
            $sentenciaSQL->bindParam(':nombre_promocion', $txtNombre);
            $sentenciaSQL->bindParam(':imagen', $nombreArchivo);
            $sentenciaSQL->bindParam(':precio', $txtPrecio);
            $sentenciaSQL->execute();
            header("Location: productos.php");
            exit();

        } catch (PDOException $e) {
            echo "Error al insertar datos: " . $e->getMessage();
        }
        break;

    case "Modificar":
        try {
            $sentenciaSQL = $conexion->prepare("UPDATE promocion SET nombre_promocion=:nombre_promocion, precio=:precio WHERE id_promocion=:id_promocion");
            $sentenciaSQL->bindParam(':nombre_promocion', $txtNombre);
            $sentenciaSQL->bindParam(':precio', $txtPrecio);
            $sentenciaSQL->bindParam(':id_promocion', $txtID);
            $sentenciaSQL->execute();

            if ($txtImagen != "") {
                $fecha = new DateTime();
                $nombreArchivo = $fecha->getTimestamp() . "_" . $_FILES["txtImagen"]["name"];
                $tmpimagen = $_FILES["txtImagen"]["tmp_name"];
                move_uploaded_file($tmpimagen, "../../img/" . $nombreArchivo);

                // Actualizar imagen solo si la imagen es diferente de la predeterminada 
                if ($nombreArchivo != "imagen.jpg") {
                    // Eliminar la imagen anterior si existe
                    $sentenciaSQL = $conexion->prepare("SELECT imagen FROM promocion WHERE id_promocion=:id_promocion");
                    $sentenciaSQL->bindParam(':id_promocion', $txtID);
                    $sentenciaSQL->execute();
                    $promocion = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);

                    if (isset($promocion["imagen"]) && $promocion["imagen"] != "imagen.jpg" && file_exists("../../img/" . $promocion["imagen"])) {
                        unlink("../../img/" . $promocion["imagen"]);
                    }

                    // Actualizar la imagen en la base de datos
                    $sentenciaSQL = $conexion->prepare("UPDATE promocion SET imagen=:imagen WHERE id_promocion=:id_promocion");
                    $sentenciaSQL->bindParam(':imagen', $nombreArchivo);
                    $sentenciaSQL->bindParam(':id_promocion', $txtID);
                    $sentenciaSQL->execute();
                }
            }
            // Redirección después de la modificación exitosa
            header("Location: productos.php");

        } catch (PDOException $e) {
            echo "Error al modificar el producto: " . $e->getMessage();
        }
        break;

    case "Cancelar";
        header("Location:productos.php");
        break;

    case "Seleccionar";
        $sentenciaSQL = $conexion->prepare("SELECT * FROM promocion WHERE id_promocion=:id_promocion");
        $sentenciaSQL->bindParam(':id_promocion', $txtID);
        $sentenciaSQL->execute();
        $promocion = $sentenciaSQL->fetch(PDO::FETCH_LAZY);

        $txtNombre = $promocion['nombre_promocion'];
        $txtImagen = $promocion['imagen'];
        $txtPrecio = $promocion['precio'];
        break;

    case "Detalles";
        header("Location:contenido.php");
        break;


    case "Borrar":
        echo "<script>
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: '¿Estás seguro de que deseas eliminar este registro?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminarlo',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Ejecutar la eliminación del registro
                        window.location.href = 'borrar.php?id=" . $txtID . "&tipo=promocion';
                    } else {
                        // Si el usuario cancela, no hacer nada
                    }
                });
             </script>";
        break;

}

//SELECCIONA LA TABLA PROMOCIONES
$sentenciaSQL = $conexion->prepare("SELECT * FROM promocion");
$sentenciaSQL->execute();
$listapromocion = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="col-md-4">
    <div class="card">
        <div class="card-header text-white bg-primary mb-3 d-flex justify-content-center">
            DETALLES 
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <!-- NOMBRE -->
                <div class="form-group">
                    <label for="txtNombre">Nombre</label>
                    <input type="text" required class="form-control" value="<?php echo $txtNombre; ?>" name="txtNombre"
                        id="txtNombre" placeholder="Nombre">
                </div>
                <br>
                <!-- PRECIO -->
                <div class="form-group">
                    <label for="txtPrecio">Precio</label>
                    <input type="int" required class="form-control" value="<?php echo $txtPrecio; ?>" name="txtPrecio"
                        id="txtPrecio" placeholder="Precio">
                </div>
<br>
                <!-- IMAGEN -->
                <div class="form-group">
                    <label for="txtImagen">Imagen</label>
                    <br>
                    <!-- MOSTRANDO IMAGEN -->
                    <?php if ($txtImagen != "") { ?>
                        <img class="img-tumbnail rounded" src="../../img/<?php echo $txtImagen ?>" width="50" alt="">
                    <?php } ?>
                    <input type="file" class="form-control" name="txtImagen" id="txtImagen" placeholder="Imagen">
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

<div class="col-md-8">
    <div class="card">
        <table class="table table-bordered">
            <thead style="text-align: center;">
                <tr class="table-primary">
                    <th style="font-size: 18px;">NOMBRE</th>
                    <th style="font-size: 18px;">PRECIO</th>
                    <th style="font-size: 18px;">IMAGEN</th>
                    <th style="font-size: 18px;">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listapromocion as $promocion) { ?>
                    <tr>
                        <td><li> <?php echo strtoupper($promocion['nombre_promocion']) ?> </li></td>
                       
                        <td>$<?php echo strtoupper($promocion['precio']) ?></td>
                        <td>
                            <div class="btn-group d-flex justify-content-center" role="group" aria-label="">
                                <img class="img-tumbnail rounded" src="../../img/<?php echo $promocion['imagen'] ?>"
                                    width="70" alt="">
                            </div>
                        </td>
                        <td>
                            <div class="btn-group d-flex justify-content-center" role="group" aria-label="">
                                <form method="post">
                                    <input type="hidden" name="txtID" id="txtID"
                                        value="<?php echo $promocion['id_promocion'] ?>">
                                    <input type="submit" name="accion" value="Seleccionar" class="btn btn-primary">
                                    <a href="contenido.php?id_promocion=<?php echo $promocion['id_promocion']; ?>"
                                        class="btn btn-primary">Detalles</a>
                                    <input type="submit" name="accion" value="Borrar" class="btn btn-danger">
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include("../template/piepagina.php"); ?>