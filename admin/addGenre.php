<?php
    include_once('./templates/header.php');
    // Se comprueba que exista un usuario y que sea admin.
    if(!isset($user)) {
        header("Location: login.php");
        exit;
    }
    if($user['Rol'] != 'admin') {
        header("Location: index.php");
        exit;
    }

    $error = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create'])) {
        if (!isset($_POST["genreName"])) {
            $error = "Nombre no indicado.";
        }

        $genre = $_POST["genreName"];
        $query = "SELECT * FROM generos WHERE Nombre LIKE '$genre'";
        try {
            $result = $databaseConnection->query($query);
            $exists = $result->num_rows;
            if ($exists != 0) {
                $error = "Ya existe el género $genre";
            } else {
                $result->free();
                $query = "INSERT INTO generos (Nombre) VALUES ('$genre')";
                if (!$result = $databaseConnection->query($query)) {
                    if ($databaseConnection->errno == 1062) {
                        $error = "Ha ocurrido un error con la clave primaria";
                    }
                } else {
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
        <form action="addGenre.php" method="post" class='registerCard'>
            <h1 class='titulo'>Agregar un nuevo género para articulos</h1>
            <label for="genreName">Nombre del género</label>
            <input type="text" id="genreName" name="genreName">
            <br>
            <input type="submit" value="Crear género" name="create">
            <br>
            <p>
            ¿No sabes qué generos están actualmente disponible?<br>
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