<?php
    include_once('./templates/header.php');
    // Se comprueba que exista un usuario y que sea admin.
    if(!isset($user)) {
        header("Location: login.php");
        exit;
    }
    if($user['Rol'] != 'admin') {
        header("Location: index.php");
        exit;
    }

    // Cantidad de articulos que se mostraran en la tabla y la página actual
    $itemsPerPage = 5;
    $page = 1;
    // Si esta indicado en el GET, se obtiene la página 
    if (isset($_GET['pagina'])) {
        $page = $_GET['pagina'];
    };

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

    /**
     * Función que crea la tabla con los productos
     * 
     * @param $con: Conexión a la BD
     * @param $itemsPerPage: Cantidad de elementos que se mostraran en la tabla
     * @param $page: página actual
     * @param $total: Total de articulos.
     */
    function crearTablaProductos($con, $itemsPerPage, $page, $total) {
        // Se obtiene la posición actual
        $posicion = ($page-1) * $itemsPerPage;
        // Se crea la SQL que obtiene los datos necesarios del articulo y el nombre del género.
        $query = "SELECT a.Nombre, a.Descripcion, a.Autor, a.Editorial, a.Stock, a.Precio, g.Nombre as Genero 
        FROM Articulos a
        INNER JOIN generos g on g.id = a.GeneroID
        ";

        if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['filtrar'])) {
            $query = $query . " WHERE GeneroID LIKE '" . $_POST['opcion'] . "'";
        }

        $query = $query . " LIMIT $posicion, $itemsPerPage";

        $estilo = "'border: 1px solid black; padding:10px; text-align:center;'";

        try {
            // Se ejecuta la consulta y se crea la cabecera
            $resultado = $con->query($query);
            echo "<caption>Página $page </caption>";
            $displayHeaders = true;
            // Se recorren los resultados
            while ($fila = $resultado->fetch_assoc()) {
                // Si es la primera vez, se crearan las cabeceras
                if ($displayHeaders == true) {
                    echo "<tr>";
                    foreach($fila as $indice=>$valor) {
                        echo "<th style=$estilo>";
                        echo "$indice";
                        echo "</th>";
                    }
                    echo "</tr>";
                }
                $displayHeaders = false;
                // Se muestran todos los datos obtenidos de la SQL.
                echo "<tr>";
                echo "<td style=$estilo>" . $fila['Nombre'] . "</td>";
                echo "<td style=$estilo>" . $fila['Descripcion'] . "</td>";
                echo "<td style=$estilo>" . $fila['Autor'] . "</td>";
                echo "<td style=$estilo>" . $fila['Editorial'] . "</td>";
                echo "<td style=$estilo>" . $fila['Stock'] . "</td>";
                echo "<td style=$estilo>" . $fila['Precio'] . "€</td>";
                echo "<td style=$estilo>" . $fila['Genero'] . "</td>";
                echo "</tr>";
            }
            // Se muestra como pie de tabla el total de páginas que tiene la tabla.
            $totalPages = ceil($total / $itemsPerPage);
            echo "<caption style='caption-side:bottom;'>";
            for ($contador = 1; $contador <= $totalPages; $contador++) { 
                echo "<a style='text-align:center;' href='list.php?page=$contador'>$contador</a>";
            }
            echo "<br>";
            echo "<button onclick='listOnPDF()'>Exportar a PDF todos los productos</button>";
            echo "</caption>";

        } catch(mysqli_sql_exception $e) {
            // Se captura cualquier tipo de error y se muestra por pantalla.
            echo 'Error: la ejecucion de tu petición a fallado. <br>';
            echo 'Que se estaba haciendo: ' . $consultaInicial . "<br>";
            echo 'Número de error: ' . $e->getCode() . "<br>";
            echo 'Error que ha ocurrido: ' . $e->getMessage();
        }
    }

    $totalProductos = 0;
    try {
        // Se obtiene el total de productos que hay en la BD
        $inicialQuery = "SELECT * FROM ARTICULOS";

        $result = $databaseConnection->query($inicialQuery);
        $totalProductos = $result->num_rows;
        $result->free();
    } catch(mysqli_sql_exception $e) {
        // Si ocurre error al ejecutarlo se captura
        echo 'Error: la ejecucion de tu petición a fallado. <br>';
        echo 'Que se estaba haciendo: ' . $consultaInicial . "<br>";
        echo 'Número de error: ' . $e->getCode() . "<br>";
        echo 'Error que ha ocurrido: ' . $e->getMessage();
    }
?>
<main>
    <section class='contenido'>
        <h1 class='titulo'>Listado de productos de la tienda</h1>
        <table class='table'>
            <?php
                // Si hay productos se crea la tabla llamando a la función
                if ($totalProductos != 0) {
                    crearTablaProductos($databaseConnection, $itemsPerPage, $page, $totalProductos);
                } else {
                    // Si no, se muestra un error de que no hay productos.
                    echo "<h2>No se tienen productos actualmente.</h2>";
                }
            ?>
        </table>

        <h2>Filtrar por género</h2>
        <form action="list.php" method="POST">
        <select name="opcion">
            <?php
                for ($i=0; $i < count($genres) ; $i++) { 
                    echo "<option value=" . $genres[$i]["id"] . ">";
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
    include_once('./templates/footer.php');
?>

<script>
    function listOnPDF() {
        window.open("productPDF.php");
    }
</script>