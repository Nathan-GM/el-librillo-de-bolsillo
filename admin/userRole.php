<?php
    include_once('./templates/header.php');
    // Se comprueba que exista un usuario y que sea admin.
    if(!isset($user)) {
        header("Location: ../login.php");
        exit;
    }
    if($user['Rol'] != 'admin') {
        header("Location: ../index.php");
        exit;
    }
    $error = "";
    $estilosError = false;

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["edit"])) {
        if(!isset($_POST['userToEdit'])) {
            $error = "No se ha indicado ningun usuario";
        } else {
            $email = $_POST['userToEdit'];
            $query = "UPDATE usuarios SET 
                Rol = 'admin'
                WHERE Email = '$email'
            ";
            if (!$update = $databaseConnection->query($query)) {
                if ($databaseConnection->errno == 1062) {
                        $error = "No se pudo modificar el articulo";
                        $estilosError = true;
                    }
                } 
            else {
                $error = "Datos actualizados correctamente";
            }
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["remove"])) {
        if(!isset($_POST['userToRemove'])) {
            $error = "No se ha indicado ningun usuario";
        } else {
            $email = $_POST['userToRemove'];
            $query = "UPDATE usuarios SET 
                Rol = 'usuario'
                WHERE Email = '$email'
            ";
            if (!$update = $databaseConnection->query($query)) {
                if ($databaseConnection->errno == 1062) {
                        $error = "No se pudo modificar el articulo";
                        $estilosError = true;
                    }
                } 
            else {
                $error = "Datos actualizados correctamente";
            }
        }
    }
?>

<main>
    <section class='contenido'>
        <h1>Editar roles de usuarios</h1>
        <h2 id='mostrarAvisos'><?php echo $error?></h2>
        <div class='cards'>
            <form action="userRole.php" method='post' class='registerCard' id='selectedUserToEdit'>
                <h1>Selecciona usuario que hacer administrador</h1>
                
                    <?php
                        $query = "SELECT Email, Nombre FROM usuarios where Rol != 'admin'";
                        try {
                            $result = $databaseConnection->query($query);
                            $numberUsers = $result->num_rows;
                            if ($numberUsers != 0) {
                                echo "<select name='userToEdit' id='userToEdit'>";
                                while ($fila = $result->fetch_assoc()) {
                                    echo "<option value=" . $fila['Email'] . ">" .$fila['Nombre'] . " - " . $fila['Email'] . "</option>";
                                }
                                echo "</select>";
                            } else {
                                echo "<h2>No hay usuarios para hacer admin.</h2>";
                            }
                        } catch(mysqli_sql_exception $e) {
                            $error = "Ha ocurrido un error: <br>";
                            $error =  $error . "Mensaje de error:" . $e->getMessage() ."<br>";
                            $error =  $error . "Numero de error:" . $e->getCode() ."<br>";
                            $estilosError = true;
                        } finally {
                            $result->free();
                        }
                    ?>
                <input type="submit" value="Hacer administrador" name="edit" <?php if($numberUsers == 0) echo 'disabled';?>>
            </form>
            
            <form action="userRole.php" method='post' class='registerCard' id='selectedUserToRemove'>
                <h1>Selecciona usuario que eliminar de administrador</h1>
                    <?php
                        $query = "SELECT Email, Nombre FROM usuarios where Rol LIKE 'admin' and Email NOT LIKE '" . $user['Email'] . "'";
                        try {
                            $result = $databaseConnection->query($query);
                            $numberAdmins = $result->num_rows;
                            if ($numberAdmins != 0) {
                                echo "<select name='userToRemove' id='userToRemove'>";
                                while ($fila = $result->fetch_assoc()) {
                                    echo "<option value=" . $fila['Email'] . ">" .$fila['Nombre'] . " - " . $fila['Email'] . "</option>";
                                }
                                echo "</select>";
                            } else {
                                echo "<h2>No hay administradores para eliminar</h2>";
                            }
                        } catch(mysqli_sql_exception $e) {
                            $error = "Ha ocurrido un error: <br>";
                            $error =  $error . "Mensaje de error:" . $e->getMessage() ."<br>";
                            $error =  $error . "Numero de error:" . $e->getCode() ."<br>";
                            $estilosError = true;
                        } finally {
                            $result->free();
                        }
                    ?>
                <input type="submit" value="Quitar de administrador" name="remove" <?php if($numberAdmins == 0) echo 'disabled';?>>
            </form>
        </div>
    </section>
</main>

<?php
    include_once('./templates/footer.php');
?>

<script>
    let cambiarEstilos = <?php if ($estilosError) echo "true"; else echo "false"?>

    if (cambiarEstilos) {
        document.getElementById("mostrarAvisos").style.color = 'red';
    }
</script>