<?php
    include_once("./templates/header.php");
    if (!isset($user)) {
        header("Location: login.php");
    }

    $cartsPerPage = 5;
    function construirTabla($actualPage, $query, $cartsPerPage, $databaseConnection) {
        $posicionInicial = ($actualPage-1) * $cartsPerPage;
        $consulta = $query . " limit " . $posicionInicial . "," . $cartsPerPage;
        $estiloTabla = "'border: 1px solid black; padding:10px; text-align:center;'";

        try {
            $tableResult = $databaseConnection->query($consulta);
            echo "<caption>Página ". $actualPage . "</caption>";
            $headers = true;
            
            while ($fila = $tableResult->fetch_assoc()) {
                if ($headers) {
                    echo "<tr>";
                    foreach($fila as $indice=>$valor) {
                        if ($indice != 'user_email') {
                        echo "<th style=" . $estiloTabla . ">";
                            echo "$indice";
                        echo "</th>";
                        }
                    }
                        echo "<th style=" . $estiloTabla . ">";
                            echo "Acciones";
                        echo "</th>";
                    echo "</tr>";
                }
                $headers = false;

                echo "<tr>";
                echo "<td style=" . $estiloTabla . ">" . $fila['id'] . "</td>";
                echo "<td style=" . $estiloTabla . ">" . $fila['estado'] . "</td>";
                $buttonDisabled = $fila['estado'] != 'completado' ? 'disabled' : '';
                echo "<td style=" . $estiloTabla . "><button " . $buttonDisabled  . " onclick='getOnPDF(". $fila['id'] .")'>Ver pedido en PDF</button>";


                echo "</tr>";
            }
        } catch(mysqli_sql_exception $e) {
            echo 'Error: la ejecucion de tu petición a fallado. <br>';
            echo 'Que se estaba haciendo: ' . $consultaInicial . "<br>";
            echo 'Número de error: ' . $e->getCode() . "<br>";
            echo 'Error que ha ocurrido: ' . $e->getMessage();
            exit;
        }
    }

    $query = "SELECT * FROM carrito WHERE user_email LIKE '" . $user['Email'] . "'";
    $result = $databaseConnection->query($query);
    $totalCarritos = $result->num_rows;

    $result -> free();
    $tablePage = 1;
    if (isset($_GET['pagina'])) {
        $tablePage = $_GET['pagina'];
    }

?>

    <main>
        <section class='contenido'>
            <h1>Carritos de <?php echo $user['Nombre'] ?>:</h1>
            <table>
                <?php
                    if ($totalCarritos == 0) {
                        echo '<h2>No has hecho ninguna compra, ¡realiza alguna!</h2>';
                    } else {
                        construirTabla($tablePage, $query, $cartsPerPage, $databaseConnection);
                    }
                ?>
                <caption style="caption-side: bottom;"> 
                <!-- Usado para poner las paginas debajo de la tabla
                  https://stackoverflow.com/questions/7529657/how-would-it-be-possible-to-add-a-caption-to-the-bottom-of-a-table -->
                <?php
                    $totalPaginas = ceil($totalCarritos / $cartsPerPage);
                    $estilo = "'text-align:center;'";
                    for($contador = 1; $contador <=$totalPaginas; $contador++) {
                        echo '<a  style= '. $estilo . ' href="carritos.php?pagina='.$contador.'">'.$contador.'</a> ';
                    }
                ?>
            </caption>
            </table>
        </section>
    </main>

    <script>
        function getOnPDF(id) {
            // window.location.href = 'cartPDF.php?carrito=' + id;
            window.open("cartPDF.php?carrito=" + id + "");
        }
    </script>

<?php
    include_once("./templates/footer.php");
?>