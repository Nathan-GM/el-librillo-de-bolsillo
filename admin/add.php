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

    $genres = [];
    $error = "";
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

    function gestionarFichero($file) {
        // Directorio donde se almacenan
        $fileDirectory = "../public-files/books-imgs/";

        // Se obtienen los nombres del fichero
        $tmpName = $file["tmp_name"];
        $name = $file['name'];
        // Si se ha subido correctamente
        if (is_uploaded_file($tmpName)) {
            // Se mueve el fichero a la ruta indicada.
            move_uploaded_file($tmpName, $fileDirectory . $name);
            return $name;
        }
        return "";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enviar'])) {
        if (
            !isset($_POST["title"]) ||
            !isset($_POST["description"]) ||
            !isset($_POST["stock"]) ||
            !isset($_POST["autor"]) ||
            !isset($_POST["editorial"]) ||
            !isset($_POST["price"]) ||
            !isset($_POST["genre"])
        ) {
            $error = "Faltan datos en el formulario.";
        } else {
            $fileName = '';
            $continue = true;
            // Se comprueba si es distinto a 4 ya que ese indica que no hay fichero
            // Fuente: https://www.php.net/manual/en/features.file-upload.errors.php
            if ($_FILES['portada']['error'] != 4) {
                $fileName = gestionarFichero($_FILES['portada']);
                if ($fileName == "") {
                    $error = "Error al subir el archivo";
                    print_r($_FILES);
                    $continue = false;
                } else {
                    echo $fileName;
                }
            }
            if ($continue) {
                try {
                    $titulo = $_POST['title'];
                    $descripcion = $_POST['description'];
                    $stock = $_POST['stock'];
                    $autor = $_POST['autor'];
                    $editorial = $_POST['editorial'];
                    $precio = $_POST['price'];
                    $genero = $_POST['genre'];

                    if ($titulo == null || $descripcion == null || $stock == 0 || $stock == null || $autor == null ||
                    $editorial == null || $precio == 0 || $precio == null || $genero == null) {
                        $error = "Alguno de los campos son erroneos.";
                    } else {
                        $query = "INSERT INTO articulos (generoId, Nombre, Descripcion, Stock, Autor, Editorial, Portada, Precio)
                        VALUES ('$genero', '$titulo', '$descripcion', '$stock', '$autor', '$editorial', '$fileName', '$precio');
                        ";
                        if (!$result = $databaseConnection->query($query)) {
                            // Si da error 1062 dará error por la clave primaria
                            if ($databaseConnection->errno == 1062) {
                                $error = "Ha ocurrido un error con la clave primaria.";
                            }
                        } else {
                            $error = "Se ha creado el articulo correctamente.";
                        }
                    }
                } catch (mysqli_sql_exception $e) {
                    $error = "Ha ocurrido el siguiente error: " . $e->getMessage();
                }
            }
        }


    }
?>
    <main>
        <section class='contenido'>
            <form action="add.php" method="post" class="registerCard" id="productoForm" enctype="multipart/form-data">
                <h1 class='titulo'>Agregar nuevos productos</h1>
                <label for="title">Titulo del producto</label>
                <input type="text" name="title" id="title">

                <br>

                <label for="description">Descripción del producto</label>
                <input type="text" name="description" id="description">

                <br>

                <label for="stock">Stock del producto disponible</label>
                <input type="number" name="stock" id="stock">

                <br>

                <label for="autor">Autor del producto</label>
                <input type="text" name="autor" id="autor">

                <br>

                <label for="editorial">Editorial a la que pertenece</label>
                <input type="text" name="editorial" id="editorial">

                <br>

                <label for="price">Precio del producto</label>
                <input type="number" name="price" id="price" step="0.01">

                <br>

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