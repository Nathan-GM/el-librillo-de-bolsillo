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

    // Se crea la variable de error
    $error = "";

    // Si se pulsa el botón de crear
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create'])) {
        // Se comprobará que exista el nombre del género.
        if (!isset($_POST["genreName"])) {
            // Si no existe, se avisa al usuario.
            $error = "Nombre no indicado.";
        }

        // Se almacena en una variable el nombre del género nuevo y se crea la consulta
        // que comprobará si existe
        $genre = $_POST["genreName"];
        $query = "SELECT * FROM generos WHERE Nombre LIKE '$genre'";
        try {
            // Se comprueba que no exista
            $result = $databaseConnection->query($query);
            $exists = $result->num_rows;
            if ($exists != 0) {
                // Si existe dará error.
                $error = "Ya existe el género $genre";
            } else {
                // Si no, creará un nuevo género con el nombre indicado.
                $result->free();
                $query = "INSERT INTO generos (Nombre) VALUES ('$genre')";
                if (!$result = $databaseConnection->query($query)) {
                    // Error clave primaria
                    if ($databaseConnection->errno == 1062) {
                        $error = "Ha ocurrido un error con la clave primaria";
                    }
                } else {
                    // Aviso de que se creo correctamente.
                    $error = "Genero creado correctamente.";
                }
            }
        } catch(mysqli_sql_exception $e) {
            $error = "Ha ocurrido el siguiente error: " . $e->getMessage();
        }
    }
?>

<main>
    <section class="contenido">
        <!-- Formulario para crear género -->
        <form action="addGenre.php" method="post" class='registerCard'>
            <h1 class='titulo'>Agregar un nuevo género para articulos</h1>
            <label for="genreName">Nombre del género</label>
            <input type="text" id="genreName" name="genreName">
            <br>
            <input type="submit" value="Crear género" name="create">
            <br>
            <p>
            ¿No sabes qué generos están actualmente disponible?<br>
            <!-- Enlace al listado de géneros. -->
            Pulsa <a href="listGenre.php">aquí</a> para ver el listado de géneros
            </p>
        </form>
        <?php
            echo "<h1>$error</h1>";
        ?>
    </section>
</main>

<?php
    include_once('./templates/footer.php');
?>