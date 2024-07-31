<?php include("template/cabecera.php"); ?>

<?php
include("administrador/config/bd.php");


$sentenciaSQL= $conexion->prepare("SELECT * FROM promocion");
$sentenciaSQL->execute();
$listapromocion= $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);


?>
 <?php $url ="http://".$_SERVER['HTTP_HOST']."/sistemaweb" ?>

<a class="btn btn-success" href="<?php echo $url;?>/orden.php">Nueva Orden</a>
<?php foreach($listapromocion as $promocion){ ?>
<div class="col-md-3">
<br> <br>
<div class="card">
    <img class="card-img-top" src= "./img/<?php echo $promocion['Imagen']; ?>"alt="">
    <div class="card-body">
        <h4 class="card-title"><?php echo $promocion['NombrePromocion']?></h4>
        <p class="card-text">Precio: <?php echo $promocion['Precio']?></p>
        <p class="card-text">Contenido: </p>
    </div>
</div>
</div>
<?php }?>



<?php include("template/piepagina.php"); ?>