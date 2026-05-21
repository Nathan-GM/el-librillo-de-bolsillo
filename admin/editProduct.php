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
    $articulo = null;

    function gestionarFicheros($oldFile, $newFile, $id) {
        // Directorio donde se almacenan
        $fileDirectory = "../public-files/books-imgs/";
        $actual = $fileDirectory . $oldFile;
        $shouldReplace = true;
        if ($actual == $fileDirectory) {
            $shouldReplace = false;
        }
        $tmpName = $newFile['tmp_name'];
        $separado = explode(".", $newFile['name']);
        $name = $id . "." . end($separado);
        if (is_uploaded_file($tmpName)) {
            if ($shouldReplace && file_exists($actual)) {
                // Unlink elimina ese fichero.
                //https://www.php.net/manual/en/function.unlink.php
                unlink($actual);
            }
            move_uploaded_file($tmpName, $fileDirectory . $name);
            return $name;
        }
        return "";

    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
        if (
            !isset($_POST["title"]) ||
            !isset($_POST["description"]) ||
            !isset($_POST["stock"]) ||
            !isset($_POST["autor"]) ||
            !isset($_POST["editorial"]) ||
            !isset($_POST["price"]) ||
            !isset($_POST["genre"])
        ) {
            $error = "Faltan datos en el formulario";
        } else {
            $portadaActual = $_SESSION['portada'];
            $nuevaPortada = "";
            $continue = true;
            if ($_FILES['portada']['error'] != 4) {
                $nuevaPortada = gestionarFicheros($portadaActual, $_FILES['portada'], $_SESSION['articuloId']);
                if ($nuevaPortada == "") {
                    $error = "Error al subir el fichero";
                    $continue = false;
                }
            } else {
                $nuevaPortada = $portadaActual;
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
                        if ($databaseConnection->errno == 1062) {
                            $error = "No se pudo modificar el articulo";
                        }
                    } else {
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
        <form action="editProduct.php" method="post" class='registerCard' id="selectArticle">
            <h1>Editar producto</h1>
            <select name="articulo" id="articulo">
            <?php
                $query = "SELECT ID, Nombre FROM articulos";
                try {
                    $result = $databaseConnection->query($query);
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
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit'])) {
                if (!isset($_POST['articulo'])) {
                    $error = "No se ha indicado ningun articulo";
                } else {
                    $id = $_POST['articulo'];
                    $query = "SELECT * FROM articulos where ID = '$id'";
                    if (!$result = $databaseConnection->query($query)) {
                        $error = "Ocurrió un problema al buscar el articulo";
                    } else {
                        $articulo = $result->fetch_assoc();
                        $_SESSION['articuloId'] = $id;
                        $_SESSION['portada'] = $articulo['Portada'];
                    }
                }
            }
        ?>

        <form action="editProduct.php" method="post" class="registerCard" id="editForm" enctype="multipart/form-data">
            <?php
                if ($articulo != null) {
                    
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

                    echo "<label for='genre'>Genero al que pertenece</label>";
                    echo "<select name='genre' id='genre'";
                    for ($i=0; $i < count($genres) ; $i++) { 
                        if ($genres[$i]['id'] == $articulo['GeneroId']) {
                            echo "<option value=" . $genres[$i]["id"] . " selected>";
                        } else {
                            echo "<option value=" . $genres[$i]["id"] . ">";
                        }
                        echo $genres[$i]["nombre"];
                        echo "</option>";
                    }
                    echo "</select> <br>";

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
    let existeArticulo = <?php if($articulo!= null) echo 'true'; else echo 'false';?>
    
    if (!existeArticulo) {
        document.getElementById("editForm").style.display = "none";
        document.getElementById("selectArticle").style.display = "flex";
    } else {
        document.getElementById("editForm").style.display = "flex";
        document.getElementById("selectArticle").style.display = "none";
    }

    document.getElementById("error").style.color = "red";
    
</script>