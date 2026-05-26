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

    /**
     * Función que borra el fichero del articulo
     * @param $file: nombre del fichero a borrar.
     */
    function borrarFichero($file){
        // Localización de ficheros
        $fileDirectory = "../public-files/books-imgs/";
        // Se crea la ruta al producto exacto
        $ruta = $fileDirectory . $file;
        // si el fichero existe, se usa unlink para borrarlo.
        if (is_file($ruta) && file_exists($ruta)) {
            unlink($ruta);
        }
    }
?>

<main>
    <section class="contenido">
        <!-- Form para borrar productos. -->
        <form action="removeProduct.php" method="post" class='registerCard' id="delete">
            <h1>Borrar productos</h1>
            <select name="articulo" id="articulo">
                <?php
                    // Se obtienen la ID y nombre de todos los productos para mostrarlas en un select.
                    $query = "SELECT ID, Nombre FROM articulos where deleted like 0";
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
            <input type="submit" value="Eliminar" name="delete">
        </form>

        <?php
        // Si se ha pulsado borrar
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
            // Se comprueba que exista un articulo.
            if (!isset($_POST['articulo'])) {
                $error = "No se ha indicado ningun articulo";
            } else {
                try {
                    // Se obtiene la portada del articulo a borrar.
                    $query = "SELECT Portada FROM articulos where ID = " . $_POST['articulo'] . "";
                    $result = $databaseConnection->query($query);
                    $values = $result->fetch_assoc();
                    $portada = $values['Portada'];

                    // Se elimina el articulo marcandolo como eliminado
                    $query = "UPDATE articulos SET deleted = '1' WHERE ID like '" . $_POST['articulo'] . "'";
                    $databaseConnection->query($query);

                    // Se elimina el fichero
                    borrarFichero($portada);
                } catch(mysqli_sql_exception $e) {
                    $error = "Ha ocurrido un error: <br>";
                    $error =  $error . "Mensaje de error:" . $e->getMessage() ."<br>";
                    $error =  $error . "Numero de error:" . $e->getCode() ."<br>";
                }
            }
        }
        echo "<h1>$error</h1>"
        ?>
    </section>
</main>

<?php
    include_once('./templates/footer.php');
?>