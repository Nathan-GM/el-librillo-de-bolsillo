<?php
    include_once ('./templates/header.php');
    if (!isset($user)) {
        header("Location: login.php");
    }

    $error = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
        // Se comprueban que existan los campos
        if (
            !isset($_POST["name"]) ||
            !isset($_POST["address"]) ||
            !isset($_POST["number"])
        ) {
            // Si no existe alguno se avisa de que faltan campos
            $error = "Alguno de los campos no es correcto.";
        } else {

            // Se crean los valores necesarios para contraseña.
            $oldPassword = '';
            $newPassword = '';
            $confirmPassword = '';
            $cambioContrasenya = 'no';

            // Si el checkbox está marcado
            if (isset($_POST['editP']) && $_POST['editP'] == 'Si') {
                // Se comprueban que los campos de contraseña existan.
                if (
                    !isset($_POST["old"]) ||
                    !isset($_POST["newPass"]) ||
                    !isset($_POST["confirmPassword"])
                ) {
                    $error = "Alguno de los campos no es correcto.";
                } else {
                    // Se obtienen sus valores.
                    $oldPassword = $_POST['old'];
                    $newPassword = $_POST['newPass'];
                    $confirmPassword = $_POST['confirmPassword'];
                    $cambioContrasenya = 'si';

                    // Si son nulos o las contraseñas no coinciden se lanzará error.
                    if ($oldPassword == null || $newPassword == null || $confirmPassword == null) {
                        $error = "Formulario incompleto";
                    } else if (!password_verify($oldPassword, $user['Contrasenya'])) {
                        $error = "La contraseña actual es incorrecta";
                    } else if ($newPassword != $confirmPassword) {
                        $error = "Las nuevas contraseñas no coinciden";
                    }
                }
            }

            // Se obtienen los demás datos.
            $name = $_POST['name'];
            $address = $_POST['address'];
            $phoneNumber = $_POST['number'];
            $hashedPassword = '';

            if (
                $name == null ||
                $address == null ||
                $phoneNumber == null
            ) {
                $error = "Formulario incompleto";
            }  else {
                if($error == "") {
                    $consulta = "";
                    if ($cambioContrasenya == "si") {
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $consulta = "UPDATE usuarios SET
                            Nombre = '$name',
                            Direccion = '$address',
                            Telefono = '$phoneNumber',
                            Contrasenya = '$hashedPassword'
                            WHERE Email = '" . $user['Email'] . "'";
                        ;
                    } else {
                        $consulta = "UPDATE usuarios SET
                            Nombre = '$name',
                            Direccion = '$address',
                            Telefono = '$phoneNumber'
                            WHERE Email = '" . $user['Email'] . "'"
                        ;
                    }

                    if (!$update = $databaseConnection->query($consulta)) {
                        if ($databaseConnection->errno == 1062) {
                                $error = "No se pudo modificar el articulo";
                            }
                        } 
                    else {
                        $error = "Datos actualizados correctamente";
                    }
                }
            }   
        }
    }
?>
    <main>
        <section class="contenido">
            <h1>
                Editando perfil de <?php echo $user['Nombre'];?>
            </h1>
            <form action="editUser.php" method="post" class='registerCard' id="form">
                <div class='cards'>
                    <div class='userDataCard'>
                        <h2>Datos básicos</h2>
                        <p>Correo: <?php echo $user['Email']?></p>
                        <p>Editar contraseña: <input type="checkbox" name="editP" id="editP" value='Si'></p>
                        <div id='passForm'>
                            <p>Contraseña Actual: <input type='password' name='old' id='old'></p> <!-- TODO: Agregar regex de contraseña. -->
                            <p>Nueva contraseña: <input type='password' name='newPass' id='newPass'></p>
                            <p>Confirmar nueva contraseña: <input type='password' name='confirmPassword' id='confirmPassword'></p>
                        </div>
                    </div>
                    <hr>
                    <div class='personalDataCard'>
                        <h2>Datos personales</h2>
                        <p>Nombre y apellidos: <input type='text' name='name' id='name' value='<?php echo $user['Nombre']?>'></p>
                        <p>Dirección: <input type='text' name='address' id='address' value='<?php echo $user['Direccion']?>'></p>
                        <p>Teléfono: <input type='text' name='number' id='number' value='<?php echo $user['Telefono']?>'></p> <!-- TODO: Regex para solo permitir números. -->
                    </div>
                </div>
                <input type="submit" value="Actualizar cuenta" name='update'>
                <br>

                <button>Volver atras.</button>
            </form>
            <?php echo $error?>
        </section>
    </main>

<?php
    include_once('./templates/footer.php');
?>

<script>
    // Se oculta el apartado de contraseña.
    document.getElementById("passForm").style.display = "none";
    
    // Se le da un eventListener de change al checkbox.
    document.getElementById("editP").addEventListener('change', (event) => {
        // Si el checkbox está marcado, se mostrará el formulario.
        if (event.currentTarget.checked) {
            document.getElementById("passForm").style.display = "inline";
        } else {
            // Si no, se ocultará.
            document.getElementById("passForm").style.display = "none";
        }
    })
</script>