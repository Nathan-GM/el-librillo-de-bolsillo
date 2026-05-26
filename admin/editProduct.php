<?php
    include_once('./templates/header.php');
    // Se comprueba que exista un usuario y que sea admin.
    if(!isset($user)) {
        header("Location: login.php");
        exit;
    }
    // Si el usuario no es admin se le manda a la página de inicio.
    if($user['Rol'] != 'admin') {
        header("Location: index.php");
        exit;
    }

    // Se crean las variables de error y articulo
    $error = "";
    $articulo = null;

    /**
     * Función encargada de gestionar los ficheros.
     * @param $oldFile: Fichero que tiene actualmente el producto.
     * @param $newFile: Nuevo fichero que se quiere agregar
     * @param $id: ID del producto que estamos actualizando.
     */
    function gestionarFicheros($oldFile, $newFile, $id) {
        // Directorio donde se almacenan
        $fileDirectory = "../public-files/books-imgs/";
        // Se obtiene el directorio actual
        $actual = $fileDirectory . $oldFile;

        // Si coinciden entonces no se debe remplazar.
        $shouldReplace = true;
        if ($actual == $fileDirectory) {
            $shouldReplace = false;
        }
        // Se obtienen los nuevos datos del fichero.
        $tmpName = $newFile['tmp_name'];
        $separado = explode(".", $newFile['name']);
        $name = $id . "." . end($separado);

        // Si se ha subido correctamente el fichero
        if (is_uploaded_file($tmpName)) {
            // Y se debe remplazar porque el fichero existe
            if ($shouldReplace && file_exists($actual)) {
                // Unlink elimina ese fichero.
                //https://www.php.net/manual/en/function.unlink.php
                unlink($actual);
            }
            // Finalmente, se mueve el fichero subido al directorio.
            move_uploaded_file($tmpName, $fileDirectory . $name);
            // Se devuelve el nombre para almacenar en la base de datos.
            return $name;
        }
        return "";

    }

    /**
     * Si se ha recibido update se comenzará la función de actualizar 
     */
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
        // Se comprueba que todos los valores necesarios existan.
        if (
            !isset($_POST["title"]) ||
            !isset($_POST["description"]) ||
            !isset($_POST["stock"]) ||
            !isset($_POST["autor"]) ||
            !isset($_POST["editorial"]) ||
            !isset($_POST["price"]) ||
            !isset($_POST["genre"])
        ) {
            // En caso de no, se indica el error.
            $error = "Faltan datos en el formulario";
        } else {
            // Se obtiene la portada actual, almacenada en la session.
            $portadaActual = $_SESSION['portada'];
            $nuevaPortada = "";
            $continue = true;
            // Se comprueba si es distinto a 4 ya que ese indica que no hay fichero
            // Fuente: https://www.php.net/manual/en/features.file-upload.errors.php
            if ($_FILES['portada']['error'] != 4) {
                // Se obtiene el nombre de la nueva portada llamando a la función
                // gestionarFichero.
                $nuevaPortada = gestionarFicheros($portadaActual, $_FILES['portada'], $_SESSION['articuloId']);
                if ($nuevaPortada == "") {
                    // Si la nueva portada devuelve "" se mostrará un error.
                    $error = "Error al subir el fichero";
                    $continue = false;
                }
            } else {
                // Si no, la nueva portada se considerá la actual.
                $nuevaPortada = $portadaActual;
            }
            // Si se puede continar
            if ($continue) {
                try {
                    // Se obtienen todos los valores.
                    $titulo = $_POST['title'];
                    $descripcion = $_POST['description'];
                    $stock = $_POST['stock'];
                    $autor = $_POST['autor'];
                    $editorial = $_POST['editorial'];
                    $precio = $_POST['price'];
                    $genero = $_POST['genre'];

                    // Y se crea la query para actualizar
                    // el producto.
                    $query = "UPDATE articulos SET 
                    generoId = '$genero', 
                    Nombre = '$titulo', 
                    Descripcion = '$descripcion',
                    Stock = '$stock',
                    Autor = '$autor',
                    Editorial = '$editorial',
                    Portada = '$nuevaPortada',
                    Precio = '$precio'
                    where ID ='". $_SESSION['articuloId'] . "'
                    ";

                    if (!$update = $databaseConnection->query($query)) {
                        // Si da error 1062 será por la clave primaria.
                        if ($databaseConnection->errno == 1062) {
                            $error = "No se pudo modificar el articulo";
                        }
                    } else {
                        // Si no, se mostrará que actualizo y borrara los elementos de session
                        $error = "Datos actualizados correctamente";
                        $articulo = null;
                        unset($_SESSION['articuloId']);
                        unset($_SESSION['portada']);
                    }

                } catch (mysqli_sql_exception $e) {
                    $error = "Ha ocurrido el siguiente error: " . $e->getMessage();
                }
            }
        }
    }
?>

<main>
    <section class="contenido">
        <!-- Formulario para obtener el articulo -->
        <form action="editProduct.php" method="post" class='registerCard' id="selectArticle">
            <h1>Editar producto</h1>
            <h2><?php echo $error?></h2>
            <select name="articulo" id="articulo">
            <?php
                // Se obtiene la ID y nombre de todos los articulos
                $query = "SELECT ID, Nombre FROM articulos where deleted = 0";
                try {
                    $result = $databaseConnection->query($query);
                    // Se crea el option para cada uno de ellos.
                    while ($fila = $result->fetch_assoc()) {
                        echo "<option value=" . $fila['ID'] . ">" .$fila['Nombre'] . "</option>";
                    }
                } catch(mysqli_sql_exception $e) {
                    $error = "Ha ocurrido un error: <br>";
                    $error =  $error . "Mensaje de error:" . $error->getMessage() ."<br>";
                    $error =  $error . "Numero de error:" . $error->getCode() ."<br>";
                } finally {
                    $result->free();
                }
            ?>
            </select>
            <input type="submit" value="Editar" name="edit">
        </form>

        <?php
            // Si se pulsa el botón de editar un articulo
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit'])) {
                // Comprobará que haya un articulo marcado
                if (!isset($_POST['articulo'])) {
                    $error = "No se ha indicado ningun articulo";
                } else {
                    // Si lo hay, almacena su ID en una variable y crea la consulta
                    $id = $_POST['articulo'];
                    $query = "SELECT * FROM articulos where ID = '$id'";
                    if (!$result = $databaseConnection->query($query)) {
                        // Si la consulta da error, se mostrará por pantalla.
                        $error = "Ocurrió un problema al buscar el articulo";
                    } else {
                        // Si no, se obtendrá el producto y creará una session con la ID y la portada.
                        $articulo = $result->fetch_assoc();
                        $_SESSION['articuloId'] = $id;
                        $_SESSION['portada'] = $articulo['Portada'];
                    }
                }
            }
        ?>

        <!-- Formulario de edición de articulo -->
        <form action="editProduct.php" method="post" class="registerCard" id="editForm" enctype="multipart/form-data">
            <?php
                if ($articulo != null) {
                    // Se obtienen todos los géneros para poder modificar el género
                    // del articulo mediante un select.
                    $genres = [];
                    try {
                        $query = "SELECT * FROM generos";
                        $genreResult = $databaseConnection->query($query);
                        $count = 0;
                        while ($fila = $genreResult->fetch_assoc()) {
                            $tmp = [];
                            $tmp['id'] = $fila["ID"];
                            $tmp['nombre'] = $fila["Nombre"];
                            $genres[$count] = $tmp;
                            $count = $count + 1;
                        }
                    } catch (mysqli_sql_exception $e) {
                        $error = "Ha ocurrido el siguiente error: " . $e->getMessage();
                    }

                    echo "<h1>Editando el articulo " . $articulo['Nombre'] . "</h1>";

                    // Titulo del articulo
                    echo "
                    <label for='title'>Titulo del producto</label>
                    <input type='text' name='title' id='title' value='" . $articulo['Nombre'] ."'> <br>";

                    // Descripcion del articulo
                    echo "
                    <label for='description'>Descripción del producto</label>
                    <input type='text' name='description' id='description' value='" . $articulo['Descripcion'] ."'> <br>";

                    // Stock del articulo
                    echo "
                    <label for='stock'>Stock del producto disponible</label>
                    <input type='number' name='stock' id='stock' value='" . $articulo['Stock'] ."'> <br>";

                    // Autor del articulo
                    echo "
                    <label for='autor'>Autor del producto</label>
                    <input type='text' name='autor' id='autor' value='" . $articulo['Autor'] ."'> <br>";

                    // Editorial del articulo
                    echo "
                    <label for='editorial'>Editorial a la que pertenece</label>
                    <input type='text' name='editorial' id='editorial' value='" . $articulo['Editorial'] ."'> <br>";

                    // Precio del articulo
                    echo "
                    <label for='price'>Precio del producto</label>
                    <input type='number' name='price' id='price' step='0.01' value='" . $articulo['Precio'] ."'> <br>";

                    // Género del articulo.
                    echo "<label for='genre'>Genero al que pertenece</label>";
                    echo "<select name='genre' id='genre'>";
                    for ($i=0; $i < count($genres) ; $i++) { 
                        echo "<option value=" . $genres[$i]["id"] . ">";
                        echo $genres[$i]["nombre"];
                        echo "</option>";
                    }
                    echo "</select> <br>";

                    // Portada del articulo
                    echo "
                    <label for='portada'>Portada del producto</label>
                    <input type='file' name='portada' id='portada' accept='image/png, image/jpeg'><br>";

                    echo '<input type="submit" value="Actualizar" name="update">';
                }

                echo "<h1 id='error'>$error</h1>";
            ?>
        </form>
    </section>
</main>

<?php
    include_once('./templates/footer.php');
?>

<script>
    // Variable que indica si existe o no un articulo.
    let existeArticulo = <?php if($articulo!= null) echo 'true'; else echo 'false';?>
    
    // Si no existe, se oculta el de edicion y se muestra el de seleccionar.
    if (!existeArticulo) {
        document.getElementById("editForm").style.display = "none";
        document.getElementById("selectArticle").style.display = "flex";
    } else {
        // Si no, se muestra el de editar y se oculta el de seleccionar.
        document.getElementById("editForm").style.display = "flex";
        document.getElementById("selectArticle").style.display = "none";
    }

    // Se le da color rojo al texto de error.
    document.getElementById("error").style.color = "red";
    
</script>