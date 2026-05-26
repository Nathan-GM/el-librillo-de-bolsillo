<?php
    include_once('./templates/header.php');
    if (isset($user)) {
        header("Location: zonaUsuario.php");
    }
    $error = '';

        // Se comprueba si se ha hecho la petición post.
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
            // Se comrpueba que los campos no estén vacios, si lo están, se devuelve error
            if (!isset($_POST['email']) || !isset($_POST['password'])) {
                $error = 'Correo o contraseña no introducidos.';            
                return;
            }
    
            // Se obtienen el correo y contraseña
            $email = $_POST['email'];
            $pass = $_POST['password'];
    
    
            // Se crea la consulta
            $query = "SELECT * from usuarios where email like '$email'";
            try {
                // Se busca el resultado. En caso de haber resultado envia al usuario al inicio de sesión.
                $result = $databaseConnection->query($query);
                if ($result->num_rows == 1) {
                    $data = $result->fetch_assoc();
                    if (password_verify($pass, $data['Contrasenya'])) {
                        $_SESSION['user'] = $email;
                        header("Location: zonaUsuario.php");
                    } else {
                        $error = 'Correo o contraseña incorrecto';
                    }
                }
                // Si no, mostrará un aviso de correo incorrecto.
                else {
                    $error = 'Correo o contraseña incorrecto';
                }
            } catch(mysqli_sql_exception $error) {
                $error = 'Ha ocurrido un error al iniciar sesión';
            }
        }

?>
<main>
    <section class='contenido'>
        <div class='loginCard'>
            <form action="login.php" method="post" class='loginCard' id='formulario'>
                <h2>Iniciar sesión</h2>
                <p>Correo: <input type='email' name='email' id='email'></p>
                <p>Contraseña: <input type='password' name='password' id='password'></p>
                <input type="submit" value="Iniciar sesión" name='login' id='login'>
            </form>
            <button id='validar'>Iniciar sesion</button>
            <p id='error'><?php echo $error; ?></p>
            <p>¿No tienes cuenta? <a href="register.php">Registrate</a></p>
        </div>
    </section>
</main>
<?php

    include_once('./templates/footer.php');
?>

<script>
    // Se oculta el botón de submit y se usa el botón para hacer validadores.
    document.getElementById("login").style.display = "none";
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

    // Al botón anterior se le da la función de validar
    document.getElementById("validar").addEventListener("click", validar);

    /**
     * Función que valida que el formulario sea correcto.
     */
    function validar() {
        // Se obtienen los elementos del formulario.
        let formulario = document.getElementById("formulario");
        let correo = formulario.email.value;
        let contrasenya = formulario.password.value
        
        // Si alguno de sus campos está vacio se avisa al usuario.
        if (
            (correo == null || correo == '') ||
            (contrasenya == null || contrasenya == '')
        ) {
            error.innerHTML = "Formulario incompleto.";
            error.style.color = "red";
        } else {
            // Si el correo no incluye @ no se considerá valido.
            if (!correo.includes("@")) {
                error.innerHTML = "Correo invalido.";
                error.style.color = "red";
            } else {
                // TODO: regex contraseña.

                // Finalmente, con JQUery se hace click al submit del formulario.
                // REF: https://stackoverflow.com/questions/8319688/click-a-specific-submit-button-with-jquery
                $("#login").click();
            }
        }
    }

</script>