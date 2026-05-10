<!DOCTYPE html>
<html>
    <head>
        <meta charset='UTF-8'>
        <title>El librillo de bolsillo</title>
        <!-- Estilos -->
        <link rel="stylesheet" href="./styles/css/styles.css">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="./styles/css/fontawesome.css">
        <link rel="stylesheet" href="./styles/css/brands.css">
        <link rel="stylesheet" href="./styles/css/regular.css">
        <link rel="stylesheet" href="./styles/css/solid.css">

        <!-- Ficheros JS -->
         <script src="./js/jquery-4.0.0.min.js"></script>


        <!-- PHP - se inicia la sesión y se obtiene al usuario. -->
        <?php 
            // Se crea la conexión a la base de datos
            $databaseConnection = new mysqli("localhost", "root", "", "proyecto");
            // Se inicia la sesión
            session_start();
            // si hay un usuario asignado 
            if (isset($_SESSION['user'])) {
                // se obtienen sus datos de la base de datos.
                $query = "select * from usuarios where email = '" . $_SESSION['user'] . "'";

                $result = $databaseConnection->query($query);
                $user = $result->fetch_assoc();
            }
        ?>

    </head>
    <body>
        <header>
            <nav>
                <!-- Botón para mostrar el menu del movil. -->
                <input type="checkbox" id="hamburguesa">
                <label for="hamburguesa" class="fa-solid fa-ellipsis-vertical" id="icono"></label>
                <ul class="menuMovil">
                    <li><a href="">Mi cuenta</a></li>
                    <li><a href="">Novedades</a></li>
                    <li><a href="">Ver géneros</a></li>
                    <li><a href="">Reseñas</a></li>
                    <li><a href="">Blog</a></li>
                    <?php
                        if (isset($user) && $user['rol'] == 'admin') {
                            echo '<p><a href="admin.php">Administración</a></p>';
                        }
                    ?>
                </ul>

                <img src="public-files/imgs/logo.png" alt="" style='width:5%'>
                <!-- Navegación de escritorio -->
                <ul class="menuNav">
                    <li><a href="">Novedades</a></li>
                    <li><a href="">Ver géneros</a></li>
                    <li><a href="">Reseñas</a></li>
                    <li><a href="">Blog</a></li>
                    <?php
                        if (isset($user) && $user['rol'] == 'admin') {
                            echo '<p><a href="admin.php">Administración</a></p>';
                        }
                    ?>
                </ul>

                <div class="tablet">
                    <span class="fa-solid fa-user"></span>
                </div>
                <span class="fa-solid fa-magnifying-glass"></span>
                <button>Carrito</button>
                <span class="fa-solid fa-cart-shopping"></span>
                <div class="pc">
                    <a href=""><span class="fa-solid fa-user"></span></a>
                </div>
            </nav>
            
            <!-- Navegación en tablet. -->
            <ul class="tablet menuNavTablet">
                <li>Novedades</li>
                <li>Ver géneros</li>
                <li>Reseñas</li>
                <li>Blog</li>
                <?php
                    if (isset($user) && $user['rol'] == 'admin') {
                        echo '<p><a href="admin.php">Administración</a></p>';
                    }
                ?>
            </ul>
        </header>        
    </body>
