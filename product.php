<?php
    include_once('./templates/header.php');

    $error = "";
    $product;
    $resenyas = [];
    $nombres = [];
    $promedio;
    $haHechoResenya = false;

    // Registro de Reseñas
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['res'])) {
        if (
            !isset($_POST['score']) ||
            !isset($_POST['com'])
        ){
            $error = "Faltan datos para registrar la reseña";
        } else {
            // Se obtienen todos los valores
            $puntuacion = $_POST['score'];
            $mensaje = $_POST['com'];
            $idProducto = $_POST['producto'];
            $email = $user['Email'];
            $date = date("Y-m-d");

            // Se crea la consulta.
            $query = "INSERT INTO resenya
            (idArticulo, email, puntuacion, mensaje, fecha) VALUES
            ('$idProducto', '$email', '$puntuacion', '$mensaje', '$date')";
            try {
                // Se ejecuta
                if (!$result = $databaseConnection->query($query)) {
                    // Si da error 1062 dará error por la clave primaria
                    if ($databaseConnection->errno == 1062) {
                        $error = "Ha ocurrido un error con la clave primaria.";
                    }
                } else {
                    // Si todo va bien, se mostrará el aviso
                    $error = "Se ha registrado correctamente.";
                }
            } catch (mysqli_sql_exception $error) {
                // Se captura la excepción de mysqli.
                $mensajeError = "Ha ocurrido un error: <br>";
                $mensajeError =  $mensajeError . "Mensaje de error:" . $error->getMessage() ."<br>";
                $mensajeError =  $mensajeError . "Numero de error:" . $error->getCode() ."<br>";
            }
        }
    }

    // Agregar al carrito
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
        $query = "SELECT * FROM carrito WHERE user_email = '" . $user['Email'] . "' and estado = 'pendiente'";
        $cartResult = $databaseConnection->query($query);
        $cart = $cartResult->fetch_assoc();
        $idCarrito = $cart['id'];
        $articuloId = $_POST['producto'];

        $query = "SELECT * FROM elementoscarrito where carritoId = '$idCarrito' AND articuloId = '$articuloId'";
        $result = $databaseConnection->query($query);
        $query = "";
        if ($result->num_rows == 1) {
            $valueResult = $result->fetch_assoc();
            $cantidad = $valueResult['cantidad'];
            $nuevaCantidad = intval($cantidad) + 1;
            $query = "UPDATE elementoscarrito SET cantidad = '$nuevaCantidad' where carritoId = '$idCarrito' and articuloId = '$articuloId'";
        } else {
            $query = "INSERT INTO elementoscarrito (carritoId, articuloId, cantidad)
            VALUES ('$idCarrito', '$articuloId', '1')";
        }
        $result->free();

        try {
            // Se ejecuta
            if (!$result = $databaseConnection->query($query)) {
                // Si da error 1062 dará error por la clave primaria
                if ($databaseConnection->errno == 1062) {
                    $error = "Ha ocurrido un error con la clave primaria.";
                }
            } else {
                // Si todo va bien, se mostrará el aviso
                $error = "Se ha registrado correctamente.";
            }
        } catch (mysqli_sql_exception $errorMSQL) {
            // Se captura la excepción de mysqli.
            $error = "Ha ocurrido un error: <br>";
            $error =  $error . "Mensaje de error:" . $errorMSQL->getMessage() ."<br>";
            $error =  $error . "Numero de error:" . $errorMSQL->getCode() ."<br>";
        }
    }

    // Si no se encuentra el producto dará error.
    if (!isset($_GET['producto']) && !isset($_POST['producto'])) {
        $error = "No se ha encontrado el producto. Disculpa las molestias.";
    } else {        
        $producto = '';
        // Se obtiene el producto indicado
        if (isset($_GET['producto'])) {
            $producto = $_GET['producto'];
        }
        else if ($producto == '' && isset($_POST['producto'])) {
            $producto = $_POST['producto'];
        }
        $query = "SELECT a.Nombre, a.Descripcion, a.Autor, a.Editorial, a.Stock, a.Precio, a.portada, a.id, g.Nombre as Genero 
        FROM Articulos a
        INNER JOIN generos g on g.id = a.GeneroID
        where a.id = '" . $producto . "' and deleted = 0";
        $result = $databaseConnection->query($query);

        // Si la fila no es igual a 1, no se ha encontrado el producto.
        if ($result->num_rows != 1) {
            $error = "No se ha encontrado el producto. Disculpa las molestias. - $producto";
        } else {
            // Si no, se almacena su valor y se obtienen las reseñas
            $product = $result->fetch_assoc();
            $query = "SELECT * FROM resenya where idArticulo = '" . $producto . "' LIMIT 2";
            $resultR = $databaseConnection->query($query);
            // Se almacenan las reseñas en el array de reseñas.
            $position = 0;
            $emails = [];
            while ($fila = $resultR->fetch_assoc()) { 
                $tmp = [];
                $tmp['id'] = $fila['id'];
                $tmp['idArticulo'] = $fila['idArticulo'];
                $tmp['email'] = $fila['email'];
                $tmp['puntuacion'] = $fila['puntuacion'];
                $tmp['mensaje'] = $fila['mensaje'];
                $tmp['fecha'] = $fila['fecha'];
                $resenyas[$position] = $tmp;
                $position++;

                // Se obtiene el email para sacar el nombre del usuario
                $emails[$position] = $fila['email'];
                if (isset($user) && $fila['email'] == $user['Email']) {
                    $haHechoResenya = true;
                }
            }

            $query = "SELECT AVG(puntuacion) as promedio from resenya where idArticulo = '" . $producto . "'";
            $resultP = $databaseConnection->query($query);
            $promedio = $resultP->fetch_assoc();

            if (count($emails) > 0) {
                for ($i=1; $i <= count($emails); $i++) { 
                    $query = "SELECT nombre from usuarios where email = '" . $emails[$i] . "'";
                    $resultN = $databaseConnection->query($query);
                    $nombres[$i] = $resultN->fetch_assoc();
                }
            }
        }
    }
?>

<main>
    <section class='contenido'>
        <?php
            if(!isset($product)) {
                echo "<h1>$error</h1>";
            } else {?>
        <!-- Portada - Izquierda -->
        <div class='productHeader'>
            <div>
                <?php
                    if ($product['portada'] == '') {
                        echo "<img src='public-files/imgs/libroPlaceholder.png' alt='Portada del libro'>";
                    } else {
                        echo "<img src='public-files/books-imgs/". $product['portada'] ."' alt='Portada del libro'>";
                    }
                ?>
            </div>

            <!-- Titulo + Precio -->
            <div>
                <?php
                echo "<h1>" . $product['Nombre'] . " - " . $product['Precio'] . "€</h1>";
                echo "<h2>" . $product['Genero'] . "</h2>";
                if ($product['Stock'] <= 0) {
                    echo "<button disabled>Actualmente agotado</button>";
                } else {
                    if(isset($user)) {
                        echo "<form action='product.php' method='post'>";
                        echo "<input type='text' value='" . $product['id'] ."' hidden name='producto'>";
                        echo "<input type='submit' value='Agregar al carrito' name='add'></input>";
                        echo "</form>";
                    } else {
                        echo "<button id='login'>Inicia sesión para agregarlo al carrito</button>";
                    }
                }
                ?>
            </div>
        </div>
        <!-- Descripción y reseñas -->
        <div>
            <h2>¿Qué es este articulo? / ¿De que tratá este producto?</h2>
            <p>
                <?php
                    echo $product['Descripcion']    
                ?>
            </p>
            <hr>
            <h2>Reseñas</h2>
            <p style='font-weight:bold'>
                <?php
                    if (isset($promedio['promedio']) && $promedio['promedio'] != '') {
                        echo "Actualmente tiene una puntuación de: " .  round($promedio['promedio'], 2) . " / 5";
                    }
                ?>
            </p>
            <?php
                if (count($resenyas) > 0) {
                    for ($i=0; $i < count($resenyas); $i++) { 
                        // Se formatea la fecha para mejor comprensión.
                        $fecha = $resenyas[$i]['fecha'];
                        $separada = explode("-", $fecha);
                        $newFecha = $separada[2] . "/" . $separada[1] . "/" . $separada[0];

                        echo "<p style='font-weight:bold;'>Reseña de " . $nombres[$i+1]['nombre'] . " hecha " . $newFecha . "</p>";
                        echo "<p>Valoración: " . $resenyas[$i]['puntuacion'] . " / 5</p>";
                        echo "<p>" . $resenyas[$i]['mensaje'] . "</p>";
                        echo "<hr>";
                    }
                } else {
                    echo "<h4>Actualmente no tiene reseñas, se el primero en dar una</h4>";
                }
            ?>
            <h2>Dejar tu propia reseña</h2>
            <?php
                if (isset($user) && !$haHechoResenya) {?>

                <form action="product.php" method="post" class='registerCard'>
                    <label for="score">Puntuación del producto:</label>
                    <select name="score" id="score">
                        <option value="1">1 - No puedo recomendarlo</option>
                        <option value="2">2 - Tiene cosas mal</option>
                        <option value="3">3 - No esta mal</option>
                        <option value="4">4 - Esta bastante bien</option>
                        <option value="5">5 - Obra maestra</option>
                    </select>
                    <br>
                    <label for="com">¡Deja aquí tu comentario!</label>
                    <textarea name="com" id="com"></textarea>
                    <br>
                    <input type="text" name='producto' hidden value='<?php echo $_GET['producto']?>'>
                    <input type="submit" value="Enviar reseña" name="res">
                </form>
            <?php
                } else {
                    if ($haHechoResenya) {
                        echo "<h4>¡Gracias por tu reseña!</h4>";
                    } else {
                        echo "<h4>Necesitas tener una cuenta para poder dar una reseña</h4>";
                    }
                }
            ?>
        </div>
        <?php
        }
    ?>
    </section>
</main>

<?php
    include_once('./templates/footer.php');
?>

<script>
    document.getElementById("login").addEventListener('click', login, false);

    function login() {
        window.location.href = 'login.php';
    }
</script>