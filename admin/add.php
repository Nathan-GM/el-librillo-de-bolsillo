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
                    $query = "INSERT INTO articulos (generoId, Nombre, Descripcion, Stock, Autor, Editorial, Precio, deleted)
                    VALUES ('$genero', '$titulo', '$descripcion', '$stock', '$autor', '$editorial', '$precio', 0);
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
            <div class="registerCard">
                <form action="add.php" method="post" id="productoForm" class="registerCard" enctype="multipart/form-data">
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
                    <input type="number" name="stock" id="stock" min='0'>

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
                    <input type="file" name="portada" id="portada" accept=".jpg, .png, .jpeg">

                    <br>

                    <input type="submit" value= 'Crear producto' name="enviar" id="crearProducto">
                </form>

                <p id='error'><?php echo $error; ?></p>
                <button id='validar'>Crear producto</button>
            </div>
        </section>
    </main>
<?php
    include_once('./templates/footer.php');
?>

<script>
    // Se oculta el submit de crear producto.
    document.getElementById('crearProducto').style.display = 'none';

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
        let formulario = document.getElementById("productoForm");

        // Y de ahi se obtienen todos sus campos.
        let titulo = formulario.title.value;
        let descripcion = formulario.description.value;
        let stock = formulario.stock.value;
        let autor = formulario.autor.value;
        let editorial = formulario.editorial.value;
        let precio = formulario.price.value;
        let genero = formulario.genre.value;
        let portada = formulario.portada.value;

        // Se comprueba que ninguno este vacio
        if (
            (titulo == null || titulo == '') ||
            (descripcion == null || descripcion == '') ||
            (stock == null || stock == '') ||
            (autor == null || autor == '') ||
            (editorial == null || editorial == '') ||
            (precio == null || precio == '') ||
            (genero == null) 
        ) {
            error.innerHTML = "Formulario incompleto.";
            error.style.color = "red";
        } else {
            // Si el precio es menor a 0 no es valido
            if (precio < 0.00) {
                error.innerHTML = "Precio invalido.";
                error.style.color = "red";
            }
            // Lo mismo ocurre con el stock
            else if (stock < 0) {
                error.innerHTML = "Stock invalido.";
                error.style.color = "red";
            } else {
                // Si la portada no está vacia
                if (portada != null && portada != '') {
                    // Se obtiene su extension.
                    let lastIndexOfPunto = portada.lastIndexOf('.') + 1;
                    let extension = portada.substr(lastIndexOfPunto, portada.length).toLowerCase();
                    // Y se comprueba que pertenezca a las permitidas
                    if (extension == 'jpg' || extension == 'jpeg' || extension == 'png') {
                        // Si todo es correcto, se hace click
                        $("#crearProducto").click();
                    } else {
                        console.log(extension);
                        error.innerHTML = "Formato de portada invalido.";
                        error.style.color = "red";
                    }
                } else {
                    // Si no hay portada, simplemente se hace click al submit.
                    $("#crearProducto").click();
                }
            }
        }
    }
</script>