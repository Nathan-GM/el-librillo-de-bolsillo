<?php
    include_once("./templates/header.php");
    if (!isset($user)) {
        header("Location: login.php");
    }

    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove'])) {
        $nuevaCantidad = intval($_POST['cantidad']) - 1;
        $idCarrito = $_POST['carritoId'];
        $articuloId = $_POST['articuloId'];
        $q = "UPDATE elementoscarrito SET cantidad = '$nuevaCantidad' where carritoId = '$idCarrito' and articuloId = '$articuloId'";
        $databaseConnection->query($q);
    }

    function crearTabla($query, $databaseConnection) {
        
        try {
            $items = $databaseConnection->query($query);
            $estiloTabla = "'border: 1px solid black; padding:10px; text-align:center;'";
            echo "<tr>";
                echo "<th style=$estiloTabla>Nombre</th>";
                echo "<th style=$estiloTabla>Cantidad</th>";
                echo "<th title='Precio por Unidad' style=$estiloTabla>PpU</th>";
                echo "<th style=$estiloTabla>Precio total</th>";
                echo "<th style=$estiloTabla>Quitar 1</th>";
            echo "</tr>";

            while ($fila = $items->fetch_assoc()) {
                foreach($fila as $indice=>$valor)
                $totalProduct = floatval($fila['Precio']) * floatval($fila['cantidad']);
                echo "<tr>";
                    echo "<td style=$estiloTabla>" . $fila['Nombre'] ."</td>";
                    echo "<td style=$estiloTabla>" . $fila['cantidad'] ."</td>";
                    echo "<td style=$estiloTabla>" . $fila['Precio'] ."€</td>";
                    echo "<td style=$estiloTabla>" . $totalProduct ."€</td>";
                    echo "<td style=$estiloTabla>";
                    echo "<form action='carrito.php' method='post'>";
                        echo "<input type='text' value='" . $fila['id'] ."' hidden name='articuloId'>";
                        echo "<input type='text' value='" . $fila['cantidad'] ."' hidden name='cantidad'>";
                        echo "<input type='text' value='" . $fila['carritoId'] ."' hidden name='carritoId'>";
                        echo "<input type='submit' value='Quitar uno' name='remove'></input>";
                    echo "</form>";
                    echo "</td>";
                echo "</tr>";
            }
        } catch(mysqli_sql_exception $e) {
            echo $e;
        }
    }

    $cartQuery = "SELECT * FROM carrito WHERE user_email like '" . $user['Email'] . "' AND estado like 'pendiente'";
    $cartResult = $databaseConnection->query($cartQuery);
    $cart = $cartResult->fetch_assoc();

    $cartResult->free();
    $itemsNumber = 0;
    $itemCarts = '';

    if (isset($cart)) {
        $itemCarts = "SELECT carritoId, cantidad, a.Nombre, a.Precio, a.id 
        FROM elementosCarrito
        INNER JOIN articulos a ON a.id = articuloId
        WHERE carritoId like '" . $cart['id'] ."'";
        $cartResult = $databaseConnection->query($itemCarts);
        $itemsNumber = $cartResult->num_rows;
        $cartResult->free();
    }
?>
    <main>
        <section class='contenido'>
            <h1>Carrito actual de <?php echo $user['Nombre'];?> </h1>
            <table>
                <?php
                    if ($itemsNumber == 0) {
                        echo '<h2>No has agregado ningún producto, ¡busca alguno!</h2>';
                    } else {
                        crearTabla($itemCarts, $databaseConnection);
                    }
                ?>
            </table>
            <!-- TODO: llevar al pago de formulario. -->
            <button id='goToPayment' <?php if ($itemsNumber == 0) echo 'disabled' ?>>Procesar pago</button>
        </section>
    </main>

<?php
    include_once("./templates/footer.php");
?>

<script>
    // Se obtiene el botón goToPayment y se le asigna la función del mismo nombre
    document.getElementById("goToPayment").addEventListener("click", goToPayment, false);

    /**
     * Función que lleva al usuario a la página del formulario de pago.
     */
    function goToPayment() {
        window.location.href = 'paymentForm.php';
    }
</script>