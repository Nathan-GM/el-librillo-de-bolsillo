<?php
    include_once('./templates/header.php');
    if (isset($user)) {
        header("Location: zonaUsuario.php");
    }
    $error = '';

?>
<main>
    <div class='contenido'>
        <form action="login.php" method="post" class='loginCard'>
            <h2>Iniciar sesión</h2>
            <p>Correo: <input type='email' name='email' id='email'></p>
            <p>Contraseña: <input type='password' name='password' id='password'></p>
            <input type="submit" value="Iniciar sesión" name='login'>
            <p>¿No tienes cuenta? <a href="register.php">Registrate</a></p>
        </form>
    </div>
</main>
<?php
    include_once('./templates/footer.php');
    // Se comprueba si se ha hecho la petición post.
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
        // Se comrpueba que los campos no estén vacios, si lo están, se devuelve error
        if (!isset($_POST['email']) || !isset($_POST['password'])) {
            $error = 'Correo o contraseña no introducidos.';            
            echo "<p style='color:red'> <?php echo $error; ?> </p>";
            return;
        }

        // Se obtienen el correo y contraseña
        $email = $_POST['email'];
        $pass = $_POST['password'];

        // Se crea la consulta
        $query = "SELECT * from usuarios where email like '$email' AND contrasenya like '$pass'";
        try {
            // Se busca el resultado. En caso de haber resultado envia al usuario al inicio de sesión.
            $result = $databaseConnection->query($query);
            if ($result->num_rows == 1) {
                $_SESSION['user'] = $email;
                header("Location: index.php");
            }
            // Si no, mostrará un aviso de correo incorrecto.
            else {
                $error = 'Correo o contraseña incorrecto';
                echo "<p style='color:red'> <?php echo $error; ?> </p>";
            }
        } catch(mysqli_sql_exception $error) {
            $error = 'Ha ocurrido un error al iniciar sesión';
            echo "<p style='color:red'> <?php echo $error; ?> </p>";
        }
    }
?>