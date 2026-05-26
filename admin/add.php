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

    // Se crean las variables de error y generos
    $genres = [];
    $error = "";
    try {
        // Se obtienen todos los géneros para poder mostrarlos
        // en un select para elegir el género del producto.
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

    /**
     * Función que mueve el fichero de la carpeta temporal al directorio
     * donde se almacenan las imagenes.
     */
    function gestionarFichero($file, $id) {
        // Directorio donde se almacenan
        $fileDirectory = "../public-files/books-imgs/";

        // Se obtienen los nombres del fichero
        $tmpName = $file["tmp_name"];
        $separatedName = explode('.', $file['name']);
        $name = $id . "." . end($separatedName);
        // Si se ha subido correctamente
        if (is_uploaded_file($tmpName)) {
            // Se mueve el fichero a la ruta indicada.
            move_uploaded_file($tmpName, $fileDirectory . $name);
            return $name;
        }
        return "";
    }

    /*Si se pulsa enviar */
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enviar'])) {
        // Se comprobará que existan todos los campos
        if (
            !isset($_POST["title"]) ||
            !isset($_POST["description"]) ||
            !isset($_POST["stock"]) ||
            !isset($_POST["autor"]) ||
            !isset($_POST["editorial"]) ||
            !isset($_POST["price"]) ||
            !isset($_POST["genre"])
        ) {
            // Si falta alguno se avisa
            $error = "Faltan datos en el formulario.";
        } else {
            $fileName = '';
            try {
                // Se obtienen todos los datos.
                $titulo = $_POST['title'];
                $descripcion = $_POST['description'];
                $stock = $_POST['stock'];
                $autor = $_POST['autor'];
                $editorial = $_POST['editorial'];
                $precio = $_POST['price'];
                $genero = $_POST['genre'];

                // Si alguno es nulo, se avisa al usuario.
                if ($titulo == null || $descripcion == null || $stock == 0 || $stock == null || $autor == null ||
                $editorial == null || $precio == 0 || $precio == null || $genero == null) {
                    $error = "Alguno de los campos son erroneos.";
                } else {
                    // Si no, se crea el articulo en la BD.
                    $query = "INSERT INTO articulos (generoId, Nombre, Descripcion, Stock, Autor, Editorial, Precio)
                    VALUES ('$genero', '$titulo', '$descripcion', '$stock', '$autor', '$editorial', '$precio');
                    ";
                    if (!$result = $databaseConnection->query($query)) {
                        // Si da error 1062 dará error por la clave primaria
                        if ($databaseConnection->errno == 1062) {
                            $error = "Ha ocurrido un error con la clave primaria.";
                        }
                    } else {
                        // Se comprueba si es distinto a 4 ya que ese indica que no hay fichero
                        // Fuente: https://www.php.net/manual/en/features.file-upload.errors.php
                        $continue = true;
                        // Se obtiene la ID del ultimo insert.
                        $id = $databaseConnection->insert_id;
                        if ($_FILES['portada']['error'] != 4) {
                            // Se asigna en fileName el nombre del fichero en la BD.
                            $fileName = gestionarFichero($_FILES['portada'], $id);
                            // Si esta vacio, se indicará que hubo un error.
                            if ($fileName == "") {
                                $error = "Error al subir el archivo. Deberá editar el articulo para agregar la portada.";
                                $continue = false;
                            } else {
                                // Si no, se actualizará el articulo para incluir la portada.
                                $updateQuery = "UPDATE articulos SET portada = '$fileName' where ID = '$id'";
                                $databaseConnection->query($updateQuery);
                            }
                        }
                    }
                }
            } catch (mysqli_sql_exception $e) {
                $error = "Ha ocurrido el siguiente error: " . $e->getMessage();
            }
        }


    }
?>
    <main>
        <section class='contenido'>
            <form action="add.php" method="post" class="registerCard" id="productoForm" enctype="multipart/form-data">
                <h1 class='titulo'>Agregar nuevos productos</h1>
                <!-- Titulo del articulo -->
                <label for="title">Titulo del producto</label>
                <input type="text" name="title" id="title">

                <br>

                <!-- Descripcion del articulo -->
                <label for="description">Descripción del producto</label>
                <input type="text" name="description" id="description">

                <br>

                <!-- Stock del articulo -->
                <label for="stock">Stock del producto disponible</label>
                <input type="number" name="stock" id="stock">

                <br>

                <!-- Autor del articulo -->
                <label for="autor">Autor del producto</label>
                <input type="text" name="autor" id="autor">

                <br>

                <!-- Editorial del articulo -->
                <label for="editorial">Editorial a la que pertenece</label>
                <input type="text" name="editorial" id="editorial">

                <br>

                <!-- Precio del articulo -->
                <label for="price">Precio del producto</label>
                <input type="number" name="price" id="price" step="0.01">

                <br>

                <!-- Género del articulo. -->
                <label for="genre">Genero al que pertenece</label>
                <select name="genre" id="genre">
                    <?php
                        for ($i=0; $i < count($genres) ; $i++) { 
                            echo "<option value=" . $genres[$i]["id"] . ">";
                            echo $genres[$i]["nombre"];
                            echo "</option>";
                        }
                    ?>
                </select>

                <br>
                
                <!-- Portada del articulo -->
                <label for="portada">Portada del producto</label>
                <input type="file" name="portada" id="portada" accept="image/png, image/jpeg">

                <br>

                <input type="submit" name="enviar" id="crear">


            </form>
            <?php
                echo "<h1>$error</h1>";
            ?>
        </section>
    </main>
<?php
    include_once('./templates/footer.php');
?>