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

    function borrarFichero($file){
        $fileDirectory = "../public-files/books-imgs/";
        $ruta = $fileDirectory . $file;
        if (file_exists($ruta)) {
            unlink($ruta);
        }
    }
?>

<main>
    <section class="contenido">
        <form action="removeProduct.php" method="post" class='registerCard' id="delete">
            <h1>Borrar productos</h1>
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
            <input type="submit" value="Eliminar" name="delete">
        </form>

        <?php
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
            if (!isset($_POST['articulo'])) {
                $error = "No se ha indicado ningun articulo";
            } else {
                try {
                    // Se obtiene la portada del articulo a borrar.
                    $query = "SELECT Portada FROM articulos where ID = " . $_POST['articulo'] . "";
                    $result = $databaseConnection->query($query);
                    $values = $result->fetch_assoc();
                    $portada = $values['Portada'];

                    // Se elimina el articulo
                    $query = "DELETE FROM articulos WHERE ID like '" . $_POST['articulo'] . "'";
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