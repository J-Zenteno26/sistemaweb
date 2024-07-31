
<?php
$host="localhost";
$bd="sistema";
$usuario="root";
$contrasena="";

try {
    $conexion= new PDO("mysql:host=$host; dbname=$bd", $usuario, $contrasena);
   // if($conexion){
     //   echo "Conectando.. a sistema ...";}

} catch (Exception $ex) {
  echo $ex ->getMessage();
}

?>