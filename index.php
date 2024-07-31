<?php      
    include("administrador/config/bd.php");
    session_start();
    
    if ($_POST) {
        if (isset($_POST['nombre_usuario']) && isset($_POST['contrasenia'])) {
            $nombreUsuario = $_POST['nombre_usuario'];
            $contrasenia = $_POST['contrasenia'];
    
            $sentenciaSQL= $conexion->prepare("SELECT * FROM usuario WHERE nombre_usuario=:nombre_usuario AND contrasenia=:contrasenia");
            $sentenciaSQL->bindParam(':nombre_usuario', $nombreUsuario);
            $sentenciaSQL->bindParam(':contrasenia', $contrasenia);
            $sentenciaSQL->execute();
            $usuario = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
    
            if ($usuario) {
                $_SESSION['usuario'] = $usuario;
                $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
                
                // Redirige según el tipo de usuario
                if ($usuario['tipo_usuario'] == 1) {
                    header('Location: administrador/seccion/inicio.php');
                    exit();
                } elseif ($usuario['tipo_usuario'] == 0) {
                    header('Location: inicio.php');
                    exit();
                }
            } else {
                $mensaje = "ERROR: EL USUARIO O CONTRASEÑA SON INCORRECTOS";
            }
        }
    }
    
    
?>
    <!doctype html>
    <html lang="en">
      <head>
        <title>Login</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <link rel="stylesheet" href="/sistemaweb/css/bootstrap/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="/sistemaweb/css/bootswatch/dist/flatly/bootstrap.min.css">
        <style>
        body{
                background-image: url("img/fondo2.jpg");
                background-repeat: no-repeat;
                background-size: cover;
            }
            
        </style>
      </head>

      <body>
          <!-- LOGIN -->
          <div class="container">
              <div class="row">
              <div class="col-md-4">                 
              </div>
                  <div class="col-md-4">
                  <br><br><br>
                      <div class="card">
                          <div class="card-header text-white bg-primary mb-3 d-flex justify-content-center">
                              Login
                          </div>
                          <div class="card-body">
                            <?php if(isset($mensaje)){?>
                             
                              <div class="alert alert-danger" role="alert">
                                  <?php echo $mensaje;?>
                              </div>
                            <?php }?>  
                              <form  action="#" method = "POST">
                              <div class = "form-group">
                              <label >Usuario</label>
                              <input type="text" class="form-control" name="nombre_usuario" placeholder="Ingresa tu usuario">
                              </div>

                              <div class="form-group">
                              <label >Contraseña</label>
                              <input type="password" class="form-control" name ="contrasenia" placeholder="Ingresa tu contraseña">
                              </div>
                              <div class="btn-group d-flex justify-content-center" role="group" aria-label="">
                              <button type="submit" value="Iniciar sesion" class="btn btn-primary">Ingresar</button>
                              </div>
                              </form>                              
                          </div>
                          
                      </div>
                  </div>
                  
              </div>
          </div>
        
      </body>
    </html>

    