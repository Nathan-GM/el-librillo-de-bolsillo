<?php
    include_once("./templates/header.php");
    // Si no hay usuario se manda a iniciar sesión
    if (!isset($user)) {
        header("Location: login.php");
    }

    function actualizarStock($id, $databaseConnection) {
        $query ="SELECT carritoId, cantidad, a.Nombre, a.Precio, articuloId 
        FROM elementoscarrito
        INNER JOIN articulos a ON a.id = articuloId
        WHERE carritoId like '" . $id ."'";

        $result = $databaseConnection->query($query);
        while($fila = $result->fetch_assoc()) {
            $idArticulo = $fila['articuloId'];
            $cantidad = $fila['cantidad'];

            $q = "SELECT stock FROM articulos where id = '$idArticulo'";
            $stockResult = $databaseConnection->query($q);
            $stockInicial = $stockResult->fetch_assoc();

            $updatedStock = intval($stockInicial['stock']) - intval($cantidad);

            $q = "UPDATE articulos SET stock = '$updatedStock' where id = '$idArticulo'";
            $databaseConnection->query($q);
        }
    }

    $error = "";

    // Se obtiene el actual carrito pendiente del usuario
    $mainCartQuery = "SELECT * FROM carrito WHERE user_email like '" . $user['Email'] . "' AND estado LIKE 'pendiente'";
    $cartResults = $databaseConnection->query($mainCartQuery);
    $userCart = $cartResults->fetch_assoc();
    $cartResults->free();

    // Variables para los productos.
    $itemCarts = '';
    $itemNumber = 0;
    $totalPrice = 0;

    // Si esta asignado la variable userCart, es decir, se recogio un carrito
    if (isset($userCart)) {
        // Se seleccionan los articulos que pertenezcan a dicha ID de carrito en la tabla elementoscarrito.
        $itemCarts = "SELECT carritoId, cantidad, a.Nombre, a.Precio 
        FROM elementoscarrito
        INNER JOIN articulos a ON a.id = articuloId
        WHERE carritoId like '" . $userCart['id'] ."'";

        $cartResults = $databaseConnection->query($itemCarts);
        
        // Por cada articulo se obtiene su precio, su cantidad y se suma al total de precio
        while ($fila = $cartResults->fetch_assoc()) {
                $itemPrice = floatVal($fila['Precio']);
                $quantity = intval($fila['cantidad']);

                $productPrice = $itemPrice * $quantity;
                $totalPrice = $totalPrice + $productPrice;

                $itemNumber = $itemNumber + $quantity;
        }

        // Si no hay productos se manda a index.
        if ($itemNumber == 0) {
            header('Location: index.php');
        }
    }

    // Función para cuando se llame al formulario.
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['payment'])) {
        // Se comprueba que existan los campos necesarios
        if (
            !isset($_POST['cardNumber']) ||
            !isset($_POST['expiration']) ||
            !isset($_POST['numeroPrivado'])
        ) {
            $error = "Formulario incompleto.";
        } else {
            // Se obtienen todos los campos.
            $cardNumber = $_POST['cardNumber'];
            // Se separa la fecha de expiración en mes y año.
            $tmpExpiration = $_POST['expiration'];
            $separado = explode("/", $tmpExpiration);
            $mes = $separado[0];
            $anyo = $separado[1];
            $numeroPrivado = $_POST['numeroPrivado'];

            // Se comprueba que este valido
            if(!isset($mes) || !isset($anyo)) {
                $error = "Expiración mal puesta";
            } else {
                // Aquí se haría la gestión de pago con Stripe.

                // Finalizaría la gestión de pago de Stripe

                // Consultas para actualizar el carrito y marcarlo como completado y crear un nuevo carrito para el usuario.
                $completeCartQuery = "UPDATE carrito SET estado = 'completado' WHERE ID = '" . $userCart['id'] . "'";
                $createNewCartQuery = "INSERT INTO carrito (user_email, estado) VALUES ('" . $user['Email'] . "', 'pendiente')";
                
                try {
                    // Se completa el carrito
                    $result = $databaseConnection->query($completeCartQuery);
                    // Se actualiza el stock
                    actualizarStock($userCart['id'], $databaseConnection);
                    // Se genera un nuevo carrito para el usuario y se manda a la página principal.
                    $result = $databaseConnection->query($createNewCartQuery);
                    header("Location: carritos.php");
                } catch (mysqli_sql_exception $e) {
                    $error = 'Ha ocurrido un error al completar el pago. <br>';
                    $error = $error . "Mensaje de Error: " . $e ->getMessage() . "<br>";
                    $error = $error . "Número de error: " . $e->getCode() . "<br>";
                }
            }
        }
    }
?>

<main>
    <section class='contenido'>
        <div class='loginCard'>
            <form action="paymentForm.php" method="post" id='formulario'>
                <h1>Formulario de pago</h1>
                <p>Productos a comprar: <?php echo $itemNumber;?> - Total a pagar: <?php echo $totalPrice?>€</p>
                <p>Número de la tarjeta: <input type="number" name="cardNumber" id="cardNumber"></p>
                <p>Fecha expiración: <input type="text" name="expiration" id="expiration"></p>
                <p>Número privado<input type="number" name="numeroPrivado" id="numeroPrivado"></p>
                <input type="submit" value="Finalizar compra de <?php echo $totalPrice?>€" name='payment' id='enviar'>
            </form>
            <button id='validar'>Finalizar compra de <?php echo $totalPrice?>€</button>
            <p id='error'><?php echo $error; ?></p>
        </div>
    </section>
</main>

<?php
    include_once("./templates/footer.php");
?>

<script>
    // Se oculta el botón de submit y se usa el botón para hacer validadores.
    document.getElementById("enviar").style.display = "none";
    var error = document.getElementById("error");

    // Se comprueba si desde PHP se ha recibido un error.
    var existeError = <?php
        if ($error != "") {
            echo "true";
        } else {
            echo "false";
        }
    ?>  
    // Si el valor es cierto, su color pasa a error.
    if (existeError) {
        error.style.color = "red";
    }

    // Al botón de validar se le da la función de validar
    document.getElementById("validar").addEventListener("click", validar);

    function validar() {
        error.innerHTML = '';
        // se obtienen los valores del formulario.
        let formulario = document.getElementById("formulario");
        let numTarjeta = formulario.cardNumber.value;
        let expiracion = formulario.expiration.value;
        let numeroPrivado = formulario.numeroPrivado.value;

        // Se comprueba que no sea nulo
        if (
            (numTarjeta == null || numTarjeta == '') ||
            (expiracion == null || expiracion == '') ||
            (numeroPrivado == null || numeroPrivado == '')
        ) {
            error.innerHTML = "Formulario incompleto.";
            error.style.color = "red";
        } else {
            // Despues que coincidan con los regex.
            if (!numeroTarjeta.test(numTarjeta)) {
                error.innerHTML = "Numero de tarjeta erroneo.";
                error.style.color = "red";
            } else if (!fechaExpiracion.test(expiracion)) {
                error.innerHTML = "Expiración erronea.";
                error.style.color = "red";
            } else if (!numPrivadoTarjeta.test(numeroPrivado)) {
                error.innerHTML = "Numero privado erroneo.";
                error.style.color = "red";
            } else {
                // Si cumple todo, se envia e inicia el submit.
                $("#enviar").click();
            }
        }
    }
</script>