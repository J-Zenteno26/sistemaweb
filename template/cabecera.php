<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <link rel="stylesheet" href="\sistemaweb\css\node_modules\bootswatch\dist\flatly\bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

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

        body {
            position: relative;
            background-image: url("img/fondo2.jpg");
            background-repeat: no-repeat;
            background-size: cover;
        }

        .container-center {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .fondo_container {
            background-color: rgba(255, 255, 255, 0);
            border-radius: 8px;
            width: 1800px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        p,
        h1 {
            color: #000000;
        }

        .logout-icon {
            color: white;
            margin-left: auto;
            cursor: pointer;
        }

        .modal-background-darken {
            background-color: rgba(0, 0, 0, 0.5);
        }
    </style>
</head>

<body>
    <?php $url = "http://" . $_SERVER['HTTP_HOST'] . "/sistemaweb"; ?>

    <nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
        <div class="nav navbar-nav">
            <a class="nav-item nav-link active" href="#">Sistema de recepcion </a>
            <a class="nav-item nav-link" href="stock.php">Stock</a>
            <a class="nav-item nav-link" href="inicio.php">Inicio</a>
            <a class="nav-item nav-link" href="menu.php">Menu</a>
            <a class="nav-item nav-link" href="pedidos.php">Pedidos</a>
            <a class="nav-item nav-link" href="clientes.php">Clientes</a>
        </div>
        <div class="logout-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <div class="navbar-nav ms-auto">
            <a class="nav-item nav-link" href="<?php echo $url; ?>index.php">Cerrar sesión </a>
        </div>
    </nav>
    <br>
    <div class="container-center">
        <div class="fondo_container">
            <div class="row">