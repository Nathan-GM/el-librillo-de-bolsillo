<?php
    include_once('./templates/header.php');
    $query = "
    select a.id, a.nombre, a.stock, a.autor, a.editorial, a.precio, a.portada, g.nombre as nombreGenero
    from articulos a 
    join generos g on a.GeneroID = g.ID";

    if (isset($_GET['genero'])) {
        $query = $query . " WHERE a.GeneroID = '" . $_GET['genero'] . "'";
    }

    if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['filtrar'])) {
        if ($_POST['opcion'] != 0) {
            $query = $query . " WHERE GeneroID LIKE '" . $_POST['opcion'] . "'";
        }
    }

    $query = $query . " ORDER BY a.id ASC";

    $result = $databaseConnection->query($query);

    $genres = [];
    $q = "SELECT * FROM generos";
    $gResult = $databaseConnection->query($q);
    $position = 0;
    while ($fila = $gResult->fetch_assoc()) {
        $tmp = [];
        $tmp['id'] = $fila['ID'];
        $tmp['nombre'] = $fila['Nombre'];
        $genres[$position] = $tmp;
        $position = $position + 1;
    }
?>

<main>
    <section class='contenido'>
        <?php
            // Si no hay resultados se muestra un mensaje de aviso.
            if ($result->num_rows < 0) {
                if (isset($_GET['genero']) || isset($_POST['opcion'])) {
                    echo "<h2>No hay productos del género indicado.";
                } else {
                    echo "<h2>Ocurrio un problema al cargar los articulos. Intentalo más tarde.";
                }
            } else {
                $i = 0;
                $total = 0;
                while ($fila = $result->fetch_assoc()) {
                    if ($i == 0) {
                        echo "<div style='display:flex; flex-direction:row;'>";
                    }
                    echo "<div class='libroN'>";
                        echo "<h3>" . $fila['nombre'] . "</h3>";
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
                        echo "<button onclick='ver(" . $fila['id'] . ")'>Ver</button>";
                        echo "<br>";
                        if ($fila['stock'] <= 0) {
                            echo "<button disabled>Actualmente agotado</button>";
                        } else {
                            echo "<button>Agregar al carrito</button>";
                        }
                    echo "</div>";
                    $i++;
                    $total++;
                    if ($i == 3 || $total == $result->num_rows) {
                        echo "</div>";
                        $i = 0;
                    }
                }
            }
        ?>
        
        <h2>Filtrar por género</h2>
        <form action="products.php" method="post">
            <select name="opcion">
                <option value="0">Todos</option>
                <?php
                    // se muestran todos los géneros
                    for ($i=0; $i < count($genres) ; $i++) {
                        // Si se ha filtrado y la opción elegida coincide con el acutal se marca como seleccionado.
                        if (isset($_POST['opcion']) && $_POST['opcion'] == $genres[$i]['id'])  {
                            echo "<option value=" . $genres[$i]["id"] . " selected>";
                        } 
                        // Se hace lo mismo en caso de que se obtenga por GET
                        else if (isset($_GET['genero']) && $_GET['genero'] == $genres[$i]['id']) {
                            echo "<option value=" . $genres[$i]["id"] . " selected>";
                        }
                        // Si no, simplemente agrega la opción.
                        else {
                            echo "<option value=" . $genres[$i]["id"] . ">";
                        }
                        echo $genres[$i]["nombre"];
                        echo "</option>";
                    }
                ?>
            </select>
            <br>
            <input type="submit" name="filtrar" value="Filtrar">
        </form>
    </section>
</main>

<?php
    include_once("./templates/footer.php");
?>

<script>
    function ver(id) {
        window.location.href = `product.php?producto=${id}`;
    }
</script>