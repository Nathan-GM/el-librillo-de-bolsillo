<?php
    include_once('./templates/header.php');
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
                            <button <?php if($fila['stock']<= 0) echo 'disabled'; ?>>
                                <?php
                                if ($fila['stock'] <= 0) { echo 'Actualmente agotado'; }
                                else { echo 'Agregar al carrito.'; }
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
                            <button <?php if($fila['stock']<= 0) echo 'disabled'; ?>>
                                <?php
                                if ($fila['stock'] <= 0) { echo 'Actualmente agotado'; }
                                else { echo 'Agregar al carrito.'; }
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
                $(this).children('img').css("width", "65%")
                $(this).children('img').css("height", "65%")
            },
            function() {
                $(this).children('img').css("width", "50%")
                $(this).children('img').css("height", "50%")
            }
        )
        $(".libroD").hover(
            function() {
                $(this).children('img').css("width", "65%")
                $(this).children('img').css("height", "65%")
            },
            function() {
                $(this).children('img').css("width", "50%")
                $(this).children('img').css("height", "50%")
            }
        )
    })
</script>