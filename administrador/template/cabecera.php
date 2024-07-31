<?php
//REDIRIGIR AL LOGIN//
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location:../../index.php");
} else {
  //if($_SESSION['usuario']=="ok")
  // $NombreUsuario= $_SESSION['NombreUsuario'];
}
?>

<!doctype html>
<html lang="en">

<head>
  <title>Administrador</title>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="\sistemaweb\css\node_modules\bootswatch\dist\flatly\bootstrap.min.css">
  <!-- Tus estilos personalizados -->
  <style>
    .input-group {
      width: 115px;
      margin: 0 auto;
    }

    .input-group .porciones {
      width: 56px;
      height: 30px;
      font-size: 14px;
      margin: 0 auto;
    }

    .input-group .btn1 {
      cursor: pointer;
      width: 30px;
      height: 30px;
      font-size: 14px;
    }

    body {
      background-image: url("../../img/fondo2.jpg");
      background-repeat: no-repeat;
      background-size: cover;
    }

    .container-center {
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .fondo_container {
      background-color: rgba(255, 255, 255, 0); /* Fondo blanco semitransparente */
      border-radius: 8px;
      width: 1466px;
      padding: 20px; /* Añadir un poco de padding */
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Añadir una ligera sombra */
    }

    p, h1 {
      color: #000000;
    }

    .logout-icon {
      color: white;
      margin-left: auto;
      cursor: pointer;
    }
  </style>
</head>

<body>

  <?php $url = "http://" . $_SERVER['HTTP_HOST'] . "/sistemaweb"; ?>

  <!-- BARRA DE NAVEGACIÓN ADMINISTRADOR -->
  <nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
    <div class="nav navbar-nav">
      <a class="nav-item nav-link active" href="#">Administrador del sistema </a>
      <a class="nav-item nav-link" href="<?php echo $url; ?>/administrador/seccion/inicio.php">Inicio</a>
      <a class="nav-item nav-link" href="<?php echo $url; ?>/administrador/seccion/productos.php">Promociones</a>
      <a class="nav-item nav-link" href="<?php echo $url; ?>/administrador/seccion/stock.php">Stock</a>
      <a class="nav-item nav-link" href="<?php echo $url; ?>">Ver sitio web</a>
    </div>
    <div class="logout-icon">
      <i class="fas fa-sign-out-alt"></i>
    </div>
    <div class="navbar-nav ms-auto">
      <a class="nav-item nav-link" href="<?php echo $url; ?>/administrador/seccion/cerrar.php">Cerrar sesión </a>
    </div>
  </nav>

<br>
  <div class="container-center">
    <div class="fondo_container">
      <div class="row">