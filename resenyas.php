<?php
    include_once('./templates/header.php');

    // Cantidad de reseñas que se mostraran en la tabla y la página actual
    $resenyasPorPagina = 5;
    $page = 1;
    // Si esta indicado en el GET, se obtiene la página 
    if (isset($_GET['pagina'])) {
        $page = $_GET['pagina'];
    };

    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove'])) {
        $idResenya = $_POST['resenyaId'];
        $q = "DELETE FROM resenya WHERE id = '$idResenya'";
        $databaseConnection->query($q);
    }

    /**
     * Función que crea la tabla con todas las reseñas.
     * 
     * @param $con Conexión a la base de datos que permite hacer las consultas.
     * @param $RPP Cantidad de reseñas que hay por página (Reseñas Por Página => RPP)
     * @param $page Página actual.
     * @param $total: Total de reseñas disponibles.
     * @param $user: usuario. Si no se le pasa es nulo.
     */
    function crearTablaResenya($con, $RPP, $page, $total, $user = null) {
        // Se obtiene la posición actual
        $posicion = ($page-1) * $RPP;

        // Se crea la SQL con lo necesario para mostrar correctamente la reseña.
        $query = "SELECT a.Nombre, u.Nombre as Usuario, r.puntuacion, r.mensaje, r.fecha, r.id
        from resenya r
        inner JOIN articulos a on r.idArticulo = a.ID
        inner JOIN usuarios u on u.Email = r.email
        where a.deleted = 0
        "
        ;

        // Si se ha indicado un filtro de articulo se agrega
        if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['filtrar'])) {
            if ($_POST['opcion'] != 0) {
                $query = $query . " WHERE r.idArticulo LIKE '" . $_POST['opcion'] . "'";
            }
        }

        // Se le aplica el limite de páginas
        $query = $query . " LIMIT $posicion, $RPP";

        // Se crea el estilo de la tabla.
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
                        if ($indice != 'id') {
                            echo "<th style=$estilo>";
                            echo "$indice";
                            echo "</th>";
                        }
                    }
                    if (isset($user) && $user['Rol'] == 'admin') {
                        echo "<th style=$estilo>";
                        echo "Administrar";
                        echo "</th>";
                    }
                    echo "</tr>";
                }
                $displayHeaders = false;
                // Se muestran todos los datos obtenidos de la SQL.
                echo "<tr>";
                    echo "<td style=$estilo>" . $fila['Nombre'] . "</td>";
                    echo "<td style=$estilo>" . $fila['Usuario'] . "</td>";
                    echo "<td style=$estilo>" . $fila['puntuacion'] . "</td>";
                    echo "<td style=$estilo>" . $fila['mensaje'] . "</td>";
                    echo "<td style=$estilo>" . $fila['fecha'] . "</td>";
                    if (isset($user) && $user['Rol'] == 'admin') {
                        echo "<td style=$estilo>";
                        echo "<form action='resenyas.php' method='post'>";
                            echo "<input type='text' value='" . $fila['id'] ."' hidden name='resenyaId'>";
                            echo "<input type='submit' value='Eliminar' name='remove'></input>";
                        echo "</form>";
                        echo "</td>";
                    }
                echo "</tr>";
            }
            // Se muestra como pie de tabla el total de páginas que tiene la tabla.
            $totalPages = ceil($total / $RPP);
            echo "<caption style='caption-side:bottom;'>";
            for ($contador = 1; $contador <= $totalPages; $contador++) { 
                echo "<a style='text-align:center;' href='resenyas.php?page=$contador'>$contador</a>";
            }
            echo "</caption>";

        } catch(mysqli_sql_exception $e) {
            // Se captura cualquier tipo de error y se muestra por pantalla.
            echo 'Error: la ejecucion de tu petición a fallado. <br>';
            echo 'Que se estaba haciendo: ' . $query . "<br>";
            echo 'Número de error: ' . $e->getCode() . "<br>";
            echo 'Error que ha ocurrido: ' . $e->getMessage();
        }
    }

    $totalResenya = 0;
    try {
        // Se obtiene el total de reseñas que hay en la BD
        $inicialQuery = "SELECT * FROM resenya inner join articulos a on idArticulo = a.id where a.deleted = 0 ";
        $result = $databaseConnection->query($inicialQuery);
        $totalResenya = $result->num_rows;
        $result->free();
    } catch(mysqli_sql_exception $e) {
        // Si ocurre error al ejecutarlo se captura
        echo 'Error: la ejecucion de tu petición a fallado. <br>';
        echo 'Que se estaba haciendo: ' . $inicialQuery . "<br>";
        echo 'Número de error: ' . $e->getCode() . "<br>";
        echo 'Error que ha ocurrido: ' . $e->getMessage();
    }
?>

<main>
    <section class='contenido'>
        <h1>Reseñas disponibles</h1>
        <table>
            <?php
                // Si existen reseñas se crea la tabla.
                if ($totalResenya != 0) {
                    if (isset($user)) {
                        crearTablaResenya($databaseConnection, $resenyasPorPagina, $page, $totalResenya, $user);
                    } else {
                        crearTablaResenya($databaseConnection, $resenyasPorPagina, $page, $totalResenya);
                    }
                } else {
                    // en caso de no encontrar ninguna se muestra.
                    echo "<h2>No se tienen reseñas actualmente.</h2>";
                }
            ?>
        </table>
    </section>
</main>

<?php
    include_once('./templates/footer.php');
?>