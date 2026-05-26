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
    where a.deleted like 0
    ORDER BY a.id ASC 
    LIMIT 4";

    // Consulta para libros destacando
    $consultaDestacando = "select a.id, a.nombre, a.stock, a.autor, a.editorial, a.precio, a.portada, g.nombre as nombreGenero
    from articulos a
    join generos g on a.GeneroID = g.ID
    where a.stock > 0 && a.deleted like 0
    ORDER BY a.stock ASC
    limit 4";


    // Se obtienen sus resultados
    $result = $databaseConnection->query($consultaNovedades);
    $resultD = $databaseConnection->query($consultaDestacando);
?>

<!-- Ref Carousel: https://fancyapps.com/carousel/plugins/sync/ -->

<!-- CSS de Carousel -->
<link 
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/carousel/carousel.css"
>
<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/carousel/carousel.arrows.css"
/> 
<!-- JavaScript de Carousel -->
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/carousel/carousel.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/carousel/carousel.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/carousel/carousel.arrows.umd.js"></script>

<main>
   <!-- Apartado de novedades -->
   <section class='novedades' id='novedad'>
        <h2>Ultimas novedades</h2>
        <!-- Apartado donde se muestran los libros de novedades. -->
        <div class='librosN'>
            <div class='f-carousel' id='carouselNovedad'>
                 <?php
                     while ($fila = $result->fetch_assoc()) {
                        // Por cada producto se crea un div de carousel que contendrá
                        // El producto que se tenga ahora mismo en fila.
                        echo "<div class='f-carousel__slide'>";
                            echo "<h2>" . $fila['nombre'] . "</h2>";
                            if ($fila['portada'] == '') {
                                echo "<img src='public-files/imgs/libroPlaceholder.png' alt='Portada del libro'>";
                            } else {
                                echo "<img src='public-files/books-imgs/". $fila['portada'] ."' alt='Portada del libro'>";
                            }
                            echo "<h3>" . $fila['nombre'] . "</h3>";
                            echo "<h4>";
                                echo "Género: " . $fila['nombreGenero'];
                                echo "<br>";
                                echo "Precio: " . $fila['precio'] . "€";
                            echo "</h4>";
                            echo "<button onclick='ver(". $fila['id'] .")'>Ver</button>";
                            echo "<br>";
                            if ($fila['stock']<= 0) {
                                echo "<button disabled> Actualmente agotado</button>";
                            }
                            else if ($fila['stock'] > 0 && !isset($user)) {
                                echo "<button onclick='login()'>Inicia sesión para agregarlo al carrito</button>";
                            } else {
                                echo "<form action='index.php' method='post'>";
                                echo "<input type='text' value='" . $fila['id'] ."' hidden name='producto'>";
                                echo "<button>";
                                echo "<input type='submit' value='Agregar al carrito' name='add' style='background: none; border: none;'></input>";
                                echo "</form>";
                                echo "</button>";
                            }
                        echo "</div>";
                     }
                 ?>
            </div>
        </div>
   </section>

   <!-- Apartado de articulos destacados -->
   <section class='destacando' id='destaca'>
        <h2>Destacando</h2>
        <!-- Apartado donde se muestran los libros destacados. -->
        <div class='librosD'>
            <div class='f-carousel' id='carouselDestacado'>
                 <?php
                     while ($fila = $resultD->fetch_assoc()) {
                        // Se crea el div del carousel.
                        echo "<div class='f-carousel__slide'>";
                            // Dentro del div agregamos todos los datos del producto.
                            echo "<h2>" . $fila['nombre'] . "</h2>";
                            if ($fila['portada'] == '') {
                                echo "<img src='public-files/imgs/libroPlaceholder.png' alt='Portada del libro'>";
                            } else {
                                echo "<img src='public-files/books-imgs/". $fila['portada'] ."' alt='Portada del libro'>";
                            }
                            echo "<h3>" . $fila['nombre'] . "</h3>";
                            echo "<h4>";
                                echo "Género: " . $fila['nombreGenero'];
                                echo "<br>";
                                echo "Precio: " . $fila['precio'] . "€";
                            echo "</h4>";
                            echo "<button onclick='ver(". $fila['id'] .")'>Ver</button>";
                            echo "<br>";
                            if ($fila['stock']<= 0) {
                                echo "<button disabled> Actualmente agotado</button>";
                            }
                            else if ($fila['stock'] > 0 && !isset($user)) {
                                echo "<button onclick='login()'>Inicia sesión para agregarlo al carrito</button>";
                            } else {
                                echo "<form action='index.php' method='post'>";
                                echo "<input type='text' value='" . $fila['id'] ."' hidden name='producto'>";
                                echo "<button>";
                                echo "<input type='submit' value='Agregar al carrito' name='add' style='background: none; border: none;'></input>";
                                echo "</form>";
                                echo "</button>";
                            }
                        echo "</div>";
                     }
                 ?>
            </div>
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

    /** Función que manda a los usuarios al login. */
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
        $(".f-carousel__slide").children('img').hover(
            function() {
                $(this).css("width", "50%")
                $(this).css("height", "60%")
            },
            function() {
                $(this).css("width", "45%")
                $(this).css("height", "55%")
            }
        )
    })

    // Script Carousel de novedades. Se encarga de iniciar las flechas de navegación.
    Carousel(document.getElementById("carouselNovedad"), {}, {
        Arrows
    }).init();
    Carousel(document.getElementById("carouselDestacado"), {}, {
        Arrows
    }).init();
</script>

