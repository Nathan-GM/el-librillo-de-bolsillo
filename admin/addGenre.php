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
        <div class='registerCard'>
            <!-- Formulario para crear género -->
            <form action="addGenre.php" method="post" class='registerCard' id='generoForm'>
                <h1 class='titulo'>Agregar un nuevo género para articulos</h1>
                <label for="genreName">Nombre del género</label>
                <input type="text" id="genreName" name="genreName">
                <br>
                <input type="submit" value="Crear género" name="create" id='crearGenero'>
                <br>
            </form>
            <p id='error'><?php echo $error; ?></p>
            <button id='validar'>Crear género</button>
            <p>
                ¿No sabes qué generos están actualmente disponible?<br>
                <!-- Enlace al listado de géneros. -->
                Pulsa <a href="listGenre.php">aquí</a> para ver el listado de géneros
            </p>
        </div>
    </section>
</main>

<?php
    include_once('./templates/footer.php');
?>

<script>
    // Se oculta el submit de crear genero.
    document.getElementById('crearGenero').style.display = 'none';

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
        let formulario = document.getElementById("generoForm");

        // Y de ahi se obtienen el nombre del genero.
        let nombreGenero = formulario.genreName.value;

        // Si esta nulo o vacio, no se considera valido
        if (nombreGenero == null || nombreGenero == '') {
            error.innerHTML = "Formulario incompleto.";
            error.style.color = "red";
        } else {
            // Si no, se pulsa el botón de submit.
            $("#crearGenero").click();
        }
     }
</script>