<?php
    include_once('./templates/header.php');
    // Se comprueba que exista un usuario y que sea admin.
    if(!isset($user)) {
        header("Location: ../login.php");
        exit;
    }
    // Si el usuario no es admin se le manda a la página de inicio.
    if($user['Rol'] != 'admin') {
        header("Location: ../index.php");
        exit;
    }

    // Cantidad de generos que se mostraran en la tabla y la página actual
    $itemsPerPage = 10;
    $page = 1;
    // Si esta indicado en el GET, se obtiene la página 
    if (isset($_GET['pagina'])) {
        $page = $_GET['pagina'];
    }

    /**
     * Función que crea la tabla con los generos
     * 
     * @param $con: Conexión a la BD
     * @param $itemsPerPage: Cantidad de elementos que se mostraran en la tabla
     * @param $page: página actual
     * @param $total: Total de generos que existen.
     */
    function crearTablaGeneros($con, $itemsPerPage, $page, $total) {
        // Se obtiene la posición actual
        $posicion = ($page-1) * $itemsPerPage;
        // Se crea la SQL que obtiene los datos del género, limitada según la página
        $query = "SELECT * FROM generos
        LIMIT $posicion, $itemsPerPage
        ";

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
                echo "<td style=$estilo>" . $fila['ID'] . "</td>";
                echo "<td style=$estilo>" . $fila['Nombre'] . "</td>";
                echo "</tr>";
            }
            // Se muestra como pie de tabla el total de páginas que tiene la tabla.
            $totalPages = ceil($total / $itemsPerPage);
            echo "<caption style='caption-side:bottom;'>";
            for ($contador = 1; $contador <= $totalPages; $contador++) { 
                echo "<a style='text-align:center;' href='list.php?page=$contador'>$contador</a>";
            }
            echo "</caption>";

        } catch(mysqli_sql_exception $e) {
            // Se captura cualquier tipo de error y se muestra por pantalla.
            echo 'Error: la ejecucion de tu petición a fallado. <br>';
            echo 'Que se estaba haciendo: ' . $consultaInicial . "<br>";
            echo 'Número de error: ' . $e->getCode() . "<br>";
            echo 'Error que ha ocurrido: ' . $e->getMessage();
        }
    }

    $totalGenres = 0;
    try {
        // Se obtiene el total de productos que hay en la BD
        $inicialQuery = "SELECT * FROM Generos";
        $result = $databaseConnection->query($inicialQuery);
        $totalGenres = $result->num_rows;
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
        <h1 class='titulo'>Listado de generos disponibles</h1>
        <table class='table'>
            <?php
                // Si hay productos se crea la tabla llamando a la función
                if ($totalGenres != 0) {
                    crearTablaGeneros($databaseConnection, $itemsPerPage, $page, $totalGenres);
                } else {
                    // Si no, se muestra un error de que no hay productos.
                    echo "<h2>No se tienen generos actualmente.</h2>";
                }
            ?>
        </table>
    </section>
</main>
<?php
    include_once('./templates/footer.php');
?>