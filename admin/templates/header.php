<?php
    // Se inicia la sesión
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset='UTF-8'>
        <title>El librillo de bolsillo</title>
        <!-- Estilos -->
        <link rel="stylesheet" href="../styles/css/styles.css">
        <link rel="stylesheet" href="../styles/css/tabletStyles.css">
        <link rel="stylesheet" href="../styles/css/pcStyles.css">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="../styles/css/fontawesome.css">
        <link rel="stylesheet" href="../styles/css/brands.css">
        <link rel="stylesheet" href="../styles/css/regular.css">
        <link rel="stylesheet" href="../styles/css/solid.css">

        <!-- Ficheros JS -->
         <script src="../js/jquery-4.0.0.min.js"></script>


        <!-- PHP - se inicia la sesión y se obtiene al usuario. -->
        <?php 
            // Datos BD docker
            $nombreServidor = "db";
            $usernameDB = "root";
            $passwordDB = "root";
            $database = "proyecto";
            $port = 3306;

            // Se crea la conexión a la base de datos
            $databaseConnection = new mysqli($nombreServidor, $usernameDB, $passwordDB, $database, $port); //DOCKER
            //$databaseConnection = new mysqli("localhost", "root", "", "proyecto"); // LOCAL
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
    <?php 
    if (isset($user)) {
        // TODO: Esto es para el desarrollo, borrarlo más adelante.
        echo "<b>DEV: Email:" .  $user['Email'] . " - Nombre Apellido: " . $user['Nombre'] . " - Rol " . $user['Rol'] . "</b>";
    }?>
        <header>
            <nav>
                <!-- Botón para mostrar el menu del movil. -->
                <input type="checkbox" id="hamburguesa">
                <label for="hamburguesa" class="fa-solid fa-ellipsis-vertical" id="icono"></label>
                <ul class="menuMovil">
                    <li><a href="../login.php">Mi cuenta</a></li>
                    <li><a href="../products.php">Novedades</a></li>
                    <li><a href="../genres.php">Ver géneros</a></li>
                    <li><a href="../resenyas.php">Reseñas</a></li>
                    <?php
                        if (isset($user) && $user['Rol'] == 'admin') {
                            echo '<p><a href="../admin.php">Administración</a></p>';
                        }
                    ?>
                </ul>

                <img src="../public-files/imgs/logo.png" alt="" style='width:5%' id="goIndex">
                <!-- Navegación de escritorio -->
                <ul class="menuNav">
                    <li><a href="../products.php">Novedades</a></li>
                    <li><a href="../genres.php">Ver géneros</a></li>
                    <li><a href="../resenyas.php">Reseñas</a></li>
                    <?php
                        if (isset($user) && $user['Rol'] == 'admin') {
                            echo '<p><a href="../admin.php">Administración</a></p>';
                        }
                    ?>
                </ul>

                <div class="tablet">
                    <span class="fa-solid fa-user"></span>
                </div>
                <button id='cart'>Carrito <span class="fa-solid fa-cart-shopping"></span></button>
                <div class="pc">
                    <a href="../login.php"><span class="fa-solid fa-user"></span></a>
                </div>
            </nav>
            
            <!-- Navegación en tablet. -->
            <ul class="tablet menuNavTablet">
                <li><a href="../products.php">Novedades</a></li>
                <li><a href="../genres.php">Ver géneros</a></li>
                <li><a href="../resenyas.php">Reseñas</a></li>
                <?php
                    if (isset($user) && $user['Rol'] == 'admin') {
                        echo '<p><a href="../admin.php">Administración</a></p>';
                    }
                ?>
            </ul>
        </header>        
    </body>

    <script>
        document.getElementById("goIndex").addEventListener('click', goToIndex, false);
        document.getElementById("cart").addEventListener('click', goToCartPage, false);

        // Función que lleva al usuario al index al pulsar la imagen.
        function goToIndex() {
            window.location.href = '../index.php';
        }

        // Función que lleva al usuario al carrito.
        function goToCartPage() {
            // Se comprueba si hay una sesión iniciada
            let haySesionIniciada = <?php 
                if (isset($user)) {
                     echo 'true'; 
                } else {
                    echo'false';   
                }?>;

            // Si hay una sesión inicada, se lleva al usuario a la página de carrito.php
            if (haySesionIniciada) {
                window.location.href = '../carrito.php';
            } else {
                // Si no, se le lleva al login.
                window.location.href = '../login.php';
            }
        }
    </script>