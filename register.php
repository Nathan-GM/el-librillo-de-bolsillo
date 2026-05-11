<?php
    include_once('./templates/header.php');
?>

<main>
    <div class='contenido'>
        <form action="register.php" method="post" class='registerCard' id='form'>
            <div class='cards'>
                <!-- TODO: AGREGAR VALIDADORES. -->
                <div class='userDataCard'>
                    <h2>Registrarse</h2>
                    <p>Correo: <input type='email' name='email' id='email'></p>
                    <p>Contraseña: <input type='password' name='password' id='password'></p> <!-- TODO: Agregar regex de contraseña. -->
                    <p>Confirmar contraseña: <input type='password' name='confirmPassword' id='confirmPassword'></p>
                </div>
                <hr>
                <div class='personalDataCard'>
                    <h2>Datos personales</h2>
                    <p>Nombre y apellidos: <input type='text' name='name' id='name'></p>
                    <p>Dirección: <input type='text' name='address' id='address'></p>
                    <p>Teléfono: <input type='text' name='number' id='number'></p> <!-- TODO: Regex para solo permitir números. -->
                </div>
            </div>
            <input type="submit" value="Crear cuenta" name='register'>
            <br>
            <button id='goToLogin'><a href="login.php">¿Ya tienes cuenta? Inicia sesión</button></a>
        </form>
    </div>
</main>


<?php
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
                $consulta = "INSERT INTO usuarios (email, contrasenya, nombre, direccion, rol, telefono) VALUES ('$email', '$hashedPassword', '$name', '$address' ,'usuario', '$phone')";

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

    // Se muestra el footer.
    include_once('./templates/footer.php');
?>