<?php
    include_once('./templates/header.php');

    $error = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
        // Se comprueban que existan los campos
        if (
            !isset($_POST["email"]) ||
            !isset($_POST["password"]) ||
            !isset($_POST["confirmPassword"]) ||
            !isset($_POST["name"]) ||
            !isset($_POST["address"]) ||
            !isset($_POST["number"])
        ) {
            // Si no existe alguno se avisa de que faltan campos
            $error = "Alguno de los campos no es correcto.";
            // Se comprueba que las contraseñas coincidan.
        } else if ($_POST["password"] != $_POST["confirmPassword"]) {
            $error = "Las contraseñas no coinciden.";
        }
        else {
            // Se obtienen los datos del formulario
            $email = $_POST['email'];

            // Para guardar la contraseña cifrada se hace uso de password_hash
            // https://www.php.net/manual/en/function.password-hash.php
            $password = $_POST['password'];
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $name = $_POST['name'];
            $address = $_POST['address'];
            $phoneNumber = $_POST['number'];

            // Se comprueba que ningun valor sea nulo
            if ($email == null || $hashedPassword == null || $name == null || $address == null || $phoneNumber == null) {
                $error = "Alguno de los campos no es correcto.";
            } else {
                // Se crea la consulta para insertar los valores.
                $consulta = "INSERT INTO usuarios (email, contrasenya, nombre, direccion, rol, telefono) VALUES ('$email', '$hashedPassword', '$name', '$address' ,'usuario', '$phoneNumber')";

                try {
                    // Se ejecuta
                    if (!$result = $databaseConnection->query($consulta)) {
                        // Si da error 1062 dará error por la clave primaria
                        if ($databaseConnection->errno == 1062) {
                            $error = "Ha ocurrido un error con la clave primaria.";
                        }
                    } else {
                        // Si todo va bien, se moverá al login.
                        $error = "Se ha registrado correctamente.";
                        $createNewCartQuery = "INSERT INTO carrito (user_email, estado) VALUES ('" . $email . "', 'pendiente')";
                        $result = $databaseConnection->query($createNewCartQuery);
                        header('Location: login.php');
                    }
                } catch (mysqli_sql_exception $error) {
                    // Se captura la excepción de mysqli.
                    $mensajeError = "Ha ocurrido un error: <br>";
                    $mensajeError =  $mensajeError . "Mensaje de error:" . $error->getMessage() ."<br>";
                    $mensajeError =  $mensajeError . "Numero de error:" . $error->getCode() ."<br>";
                }
            }
        }
        echo "<p style='color: red;'>$error</p>";
    }

?>

<main>
    <div class='contenido'>
        <div class='registerCard'>
            <form action="register.php" method="post" id='form'>
                <div class='cards'>
                    <div class='userDataCard'>
                        <h2>Registrarse</h2>
                        <p>Correo: <input type='email' name='email' id='email'></p>
                        <p>Contraseña: <input type='password' name='password' id='password'></p>
                        <p>Confirmar contraseña: <input type='password' name='confirmPassword' id='confirmPassword'></p>
                    </div>
                    <hr>
                    <div class='personalDataCard'>
                        <h2>Datos personales</h2>
                        <p>Nombre y apellidos: <input type='text' name='name' id='name'></p>
                        <p>Dirección: <input type='text' name='address' id='address'></p>
                        <p>Teléfono: <input type='tel' name='number' id='number'></p>
                    </div>
                </div>
                <input type="submit" value="Crear cuenta" name='register' id='register'>
                <br>
            </form>
        <p id='error'><?php echo $error; ?></p>
        <button id='validar'>Crear cuenta</button></a>
        <button id='goToLogin'><a href="login.php">¿Ya tienes cuenta? Inicia sesión</button></a>
        </div>
    </div>
</main>

<?php
    // Se muestra el footer.
    include_once('./templates/footer.php');
?>
?>


<script>
    // Se oculta el submit de registro.
    document.getElementById("register").style.display = "none";

    // Se obtiene el parrafo que mostrará el error.
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

    // Al botón de validar se le da la función que comprobará que todos los campos sean validos.
    document.getElementById("validar").addEventListener("click", validar);

    function validar() {
        // Se obtiene el formulario
        let formulario = document.getElementById("form");

        // Y de ahi se obtienen todos sus campos.
        let correo = formulario.email.value;
        let contrasenya = formulario.password.value
        let confirmarContrasenya = formulario.confirmPassword.value
        let nombre = formulario.name.value;
        let direccion = formulario.address.value;
        let numeroTelefono = formulario.number.value;

        // Se comprueba que ninguno este vacio
        if (
            (correo == null || correo == '') ||
            (contrasenya == null || contrasenya == '') ||
            (confirmarContrasenya == null || confirmarContrasenya == '') ||
            (nombre == null || nombre == '') ||
            (direccion == null || direccion == '') ||
            (numeroTelefono == null || numeroTelefono == '')
        ) {
            error.innerHTML = "Formulario incompleto.";
            error.style.color = "red";
        } else {
            // Si el correo no incluye @ no se considerá valido.
            if (!correo.includes('@')) {
                error.innerHTML = "Correo invalido.";
                error.style.color = "red";
                // Si las contraseñas no coinciden no se permite continuar
            } else if (contrasenya != confirmarContrasenya) {
                error.innerHTML = "Las contraseñas no coinciden.";
                error.style.color = "red";
                // Si el numero de telefono no se considera valido se avisa al usuario
            } else if (!numeroDeTelefono.test(numeroTelefono)) {
                error.innerHTML = "El número de teléfono no es valido.";
                error.style.color = "red";
            } else {
                $("#register").click();
            }
        }
    }
</script>