<?php
    include_once('./templates/header.php');

    // Si se pulsa el botón de añadir al carrito
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
        // Obtiene el carrito del usuario
        $query = "SELECT * FROM carrito WHERE user_email = '" . $user['Email'] . "' and estado = 'pendiente'";
        $cartResult = $databaseConnection->query($query);
        $cart = $cartResult->fetch_assoc();
        // Obtiene de ahi la ID del articulo
        $idCarrito = $cart['id'];
        $articuloId = $_POST['producto'];

        // Comrpueba si el producto ya existe.
        $query = "SELECT * FROM elementoscarrito where carritoId = '$idCarrito' AND articuloId = '$articuloId'";
        $result = $databaseConnection->query($query);
        $query = "";
        // Si ya existe
        if ($result->num_rows == 1) {
            // Se actualiza su cantidad
            $valueResult = $result->fetch_assoc();
            $cantidad = $valueResult['cantidad'];
            $nuevaCantidad = intval($cantidad) + 1;
            $query = "UPDATE elementoscarrito SET cantidad = '$nuevaCantidad' where carritoId = '$idCarrito' and articuloId = '$articuloId'";
        } else {
            // Si no, se agrega el nuevo valor.
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

    // Se crea la consulta de las novedades
    $consultaNovedades = "select a.id, a.nombre, a.stock, a.autor, a.editorial, a.precio, a.portada, g.nombre as nombreGenero
    from articulos a 
    join generos g on a.GeneroID = g.ID 
    ORDER BY a.id ASC 
    LIMIT 4";

    // Consulta para libros destacando
    $consultaDestacando = "select a.id, a.nombre, a.stock, a.autor, a.editorial, a.precio, a.portada, g.nombre as nombreGenero
    from articulos a
    join generos g on a.GeneroID = g.ID
    where a.stock > 0
    ORDER BY a.stock ASC
    limit 4";


    // Se obtienen sus resultados
    $result = $databaseConnection->query($consultaNovedades);
    $resultD = $databaseConnection->query($consultaDestacando);
?>
<main>
   <!-- Apartado de novedades -->
   <section class='novedades' id='novedad'>
        <h2>Ultimas novedades</h2>
        <!-- Apartado donde se muestran los libros de novedades. -->
        <div class='librosN'>
            <!-- TODO: Actualizar para usar Carrousel de JS. -->
            <span class='flechaIzquierda'><</span>
            <?php
                // Si no hay resultados no hay novedades y se meustra el aviso.
                if ($result->num_rows < 0) {
                    echo '<h2>No se han encontrado novedades</h2>';
                } else {
                    // Se recorre cada resultado
                    while ($fila = $result->fetch_assoc()) {
                    ?>
                        <!-- Contenedor del libro -->
                        <div class='libroN'>
                            <h3>
                                <?php echo $fila['nombre'];?>
                            </h3>

                             <?php
                                if ($fila['portada'] == '') {
                                    echo "<img src='public-files/imgs/libroPlaceholder.png' alt='Portada del libro'>";
                                } else {
                                    echo "<img src='public-files/books-imgs/". $fila['portada'] ."' alt='Portada del libro'>";
                                }
                             ?>
                            <h3>
                                <?php echo $fila['nombre'];?>
                            </h3>
                            <h4>
                                Género: <?php echo $fila['nombreGenero'];?>
                                <br>
                                Precio: <?php echo $fila['precio'];?>€
                            </h4>

                            <!-- Botones de acción -->
                            <button onclick="ver(<?php echo $fila['id']?>)">Ver</button>
                            <br>
                            <button <?php if($fila['stock']<= 0) echo 'disabled'; ?> <?php if(!isset($user) && $fila['stock'] > 0) echo "style='display:none;'";?>>
                                <?php
                                if ($fila['stock'] <= 0) { echo 'Actualmente agotado'; }
                                else {
                                    if(isset($user)) {
                                        echo "<form action='index.php' method='post'>";
                                        echo "<input type='text' value='" . $fila['id'] ."' hidden name='producto'>";
                                        echo "<input type='submit' value='Agregar al carrito' name='add' style='background: none; border: none;'></input>";
                                        echo "</form>";
                                    } else {
                                        echo "<button onclick='login()'>Inicia sesión para agregarlo al carrito</button>";
                                    }
                                 }
                                ?>
                            </button>
                        </div>
                    <?php
                    }
                }
                $result-> free();
            ?>
            <span class="flechaDerecha">></span>
        </div>
   </section>

   <!-- Apartado de articulos destacados -->
   <section class='destacando' id='destaca'>
        <h2>Destacando</h2>
        <!-- Apartado donde se muestran los libros de novedades. -->
        <div class='librosD'>
            <!-- TODO: Actualizar para usar Carrousel de JS. -->
            <span class='flechaIzquierda'><</span>
            <?php
                // Si no hay resultados no hay novedades y se meustra el aviso.
                if ($resultD->num_rows < 0 || $resultD->num_rows == 0) {
                    echo '<h2>No se han encontrado novedades</h2>';
                } else {
                    // Se recorre cada resultado
                    while ($fila = $resultD->fetch_assoc()) {
                    ?>
                        <!-- Contenedor del libro -->
                        <div class='libroD'>
                            <h3>
                                <?php echo $fila['nombre'];?>
                            </h3>

                            <?php
                                if ($fila['portada'] == '') {
                                    echo "<img src='public-files/imgs/libroPlaceholder.png' alt='Portada del libro'>";
                                } else {
                                    echo "<img src='public-files/books-imgs/". $fila['portada'] ."' alt='Portada del libro'>";
                                }
                             ?>
                            <h3>
                                <?php echo $fila['nombre'];?>
                            </h3>
                            <h4>
                                Género: <?php echo $fila['nombreGenero'];?>
                                <br>
                                Precio: <?php echo $fila['precio'];?>€
                            </h4>

                            <!-- Botones de acción -->
                            <button onclick="ver(<?php echo $fila['id']?>)">Ver</button>
                            <br>
                            <button <?php if($fila['stock']<= 0) echo 'disabled'; ?> <?php if(!isset($user) && $fila['stock'] > 0) echo "style='display:none;'"; ?>>
                                <?php
                                if ($fila['stock'] <= 0) { echo 'Actualmente agotado'; }
                                else { 
                                    if(isset($user)) {
                                        echo "<form action='index.php' method='post'>";
                                        echo "<input type='text' value='" . $fila['id'] ."' hidden name='producto'>";
                                        echo "<input type='submit' value='Agregar al carrito' name='add' style='background: none; border: none;'></input>";
                                        echo "</form>";
                                    } else {
                                        echo "<button onclick='login()'>Inicia sesión para agregarlo al carrito</button>";
                                    }
                                }
                                ?>
                            </button>
                        </div>
                    <?php
                    }
                }
                $resultD-> free();
            ?>
            <span class="flechaDerecha">></span>
        </div>
   </section>
   <br>
   <br>
</main> 
<?php
    include_once('./templates/footer.php');
?>

<script>
    /** Función que permite ver un producto. */
    function ver(id) {
        window.location.href = `product.php?producto=${id}`;
    }

    function login() {
        window.location.href = 'login.php';
    }

    // Animaciones de JQuery para cuando la página haya cargado.
    $(document).ready(function() {
        // Se anima las secciones de novdad y destacado.
        // Se hace que se muestre poco a poco el apartado de novedades.
        $("#novedad").css("opacity", "0");
        $("#destaca").css("display", "none");
        $("#novedad").animate({
            opacity: '0.2',
        });
        $("#novedad").animate({
            opacity: '0.4',
        });
        $("#novedad").animate({
            opacity: '0.6',
        });
        $("#novedad").animate({
            opacity: '1',
        });

        // Se hace con destacado.
        $("#destaca").fadeIn("slow");

        // Función que hace grande la foto al pasar el ratón por encima
        $(".libroN").hover(
            function() {
                $(this).children('img').css("width", "50%")
                $(this).children('img').css("height", "50%")
            },
            function() {
                $(this).children('img').css("width", "40%")
                $(this).children('img').css("height", "40%")
            }
        )
        $(".libroD").hover(
            function() {
                $(this).children('img').css("width", "50%")
                $(this).children('img').css("height", "50%")
            },
            function() {
                $(this).children('img').css("width", "40%")
                $(this).children('img').css("height", "40%")
            }
        )
    })
</script>