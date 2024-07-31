<?php include("template/cabecera.php"); ?>



<?php

//PRE 

$accion = isset($_POST['accion']) ? $_POST['accion'] : "";
$seleccion = isset($_POST['seleccion']) ? $_POST['seleccion'] : "";
$editar = isset($_POST['editar']) ? $_POST['editar'] : "";

include("administrador/config/bd.php");

// Obtener todas las promociones
$sentenciaSQL = $conexion->prepare("SELECT * FROM promocion");
$sentenciaSQL->execute();
$listapromocion = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);

$url = "http://" . $_SERVER['HTTP_HOST'] . "/sistemaweb";
?>

<style>
    .card {
        border: 1px solid #ccc;
        border-radius: 5px;
        overflow: hidden;
        width: 300px;
        margin: 9px;
    }

    .card-img-top {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .card-body {
        padding: 10px;
    }

    .card-title {
        font-size: 1.25em;
        margin-bottom: 10px;
    }

    .card-text {
        margin-bottom: 10px;
    }
</style>

<?php foreach ($listapromocion as $promocion) { 
    // Obtener rolls para la promoción actual
    $sentenciaSQL1 = $conexion->prepare("SELECT DISTINCT roll.id_roll, roll.nombre_roll,
        (SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.cobertura AND insumo.tipo_insumo = 1) AS cobertura,
        (SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.proteina AND insumo.tipo_insumo = 2) AS proteina,
        (SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.vegetal_1 AND insumo.tipo_insumo = 3) AS vegetal_1,
        (SELECT nombre_insumo FROM insumo WHERE id_insumo = roll.vegetal_2 AND insumo.tipo_insumo = 3) AS vegetal_2
        FROM roll WHERE roll.id_promocion = :id_promocion");
    $sentenciaSQL1->bindParam(':id_promocion', $promocion['id_promocion'], PDO::PARAM_INT);
    $sentenciaSQL1->execute();
    $listaroll = $sentenciaSQL1->fetchAll(PDO::FETCH_ASSOC);

    // Obtener snacks para la promoción actual
    $sentencia_snack = $conexion->prepare("SELECT DISTINCT snack.id_snack, snack.nombre_snack, snack.cantidad 
        FROM snack WHERE snack.id_promocion = :id_promocion");
    $sentencia_snack->bindParam(':id_promocion', $promocion['id_promocion'], PDO::PARAM_INT);
    $sentencia_snack->execute();
    $listasnack = $sentencia_snack->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="card border-primary mb-3" style="max-width: 20rem;">
        <div class="card-header">
            <img class="card-img-top" src="./img/<?php echo $promocion['imagen']; ?>" alt="">
        </div>
        <div class="card-body">
            <h4 class="card-title"><?php echo strtoupper($promocion['nombre_promocion']); ?></h4>
            <p class="card-text">Precio: <?php echo $promocion['precio']; ?></p>
            <p class="card-text">
                Contenido:
                <?php if (!empty($listaroll)) { ?>
                    <ul>
                        <?php foreach ($listaroll as $roll) { ?>
                            <li>
                                ROLL: <?php echo strtoupper($roll['nombre_roll']); ?> -
                                Cobertura: <?php echo $roll['cobertura']; ?> -
                                Proteina: <?php echo $roll['proteina']; ?> -
                                Vegetal 1: <?php echo $roll['vegetal_1']; ?> -
                                Vegetal 2: <?php echo $roll['vegetal_2']; ?>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
                <?php if (!empty($listasnack)) { ?>
                    <ul>
                        <?php foreach ($listasnack as $snack) { ?>
                            <li>
                                SNACK: <?php echo strtoupper($snack['nombre_snack']); ?> -
                                Cantidad: <?php echo $snack['cantidad']; ?>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </p>
        </div>
    </div>
<?php } ?>

<?php include("template/piepagina.php"); ?>