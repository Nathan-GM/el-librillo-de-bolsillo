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
            <div class='registerCard'>
                <form action="editUser.php" method="post" id="form">
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
                    <input type="submit" value="Actualizar cuenta" name='update' id='update'>
                    <br>

                </form>
                <p id='mostrarRequisitos'>*Mostrar requisitos de contraseña</p>
                <div id='requisitos'>
                    <p>La contraseña ha de tener:
                        <ol>
                            <li>Una mayuscula</li>
                            <li>Una minuscula</li>
                            <li>Un numero</li>
                            <li>Un caracter especial</li>
                            <li>Minimo 6 caracteres</li>
                        </ol>
                    </p>
                </div>
                <p id='error'><?php echo $error; ?></p>
                <button id='validar'>Actualizar cuenta</button></a>
                <button onclick='goBack()'>Volver atras.</button>
            </div>
        </section>
    </main>

<?php
    include_once('./templates/footer.php');
?>

<script>
    // Booleano que comprueba si se están mostrando o no los requisitos.
    let verRequisitos = false;
    // Booleano que indicara si el validador ha de tener en cuenta o no la contraseña.
    let cambiarContrasenya = false;


    // Se oculta el apartado de contraseña, los requisitos y el submit.
    document.getElementById("passForm").style.display = "none";
    document.getElementById("mostrarRequisitos").style.display = "none";
    document.getElementById("requisitos").style.display = "none";
    document.getElementById("update").style.display = "none";
    
    
    // Se dan estilos al elemento de mostrar requisitos para cuando se deba mostrar
    document.getElementById("mostrarRequisitos").style.cursor = "pointer";
    document.getElementById("mostrarRequisitos").style.fontWeight = "bold";
    document.getElementById("mostrarRequisitos").style.textAlign = "center";
    
    
    // Se le da un eventListener de change al checkbox.
    document.getElementById("editP").addEventListener('change', (event) => {
        // Si el checkbox está marcado
        if (event.currentTarget.checked) {
            // Se muestra las contraseñas
            document.getElementById("passForm").style.display = "inline";
            // Se muestran los requisitos si estaban activos
            document.getElementById("mostrarRequisitos").style.display = "inline";
            if (verRequisitos) {
                document.getElementById("requisitos").style.display = "inline";
            }
            // Y se indica que si se mostrará la contraseña.
            cambiarContrasenya = true;
        } else {
            // Si no, se ocultará.
            document.getElementById("passForm").style.display = "none";
            document.getElementById("mostrarRequisitos").style.display = "none";
            if (verRequisitos) {
                document.getElementById("requisitos").style.display = "none";
            }
            cambiarContrasenya = false;
        }
    })

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

    // Se le da una función click
    document.getElementById("mostrarRequisitos").addEventListener("click", requisitos);

    /**
     * Función que mostrará u ocultara los requisitos de contraseña.
     */
    function requisitos() {
        // Si ver requisitos esta activo
        if (verRequisitos) {
            // Se ocultan y se actualiza el texto de mostrar requisitos.
            document.getElementById("requisitos").style.display = "none";
            document.getElementById("mostrarRequisitos").innerHTML = '*Mostrar requisitos de contraseña';
            // Finalmente, requisitos pasa a falso
            verRequisitos = false;
        } else {
            // Si no, se muestran los requisitos y se actualiza el texto
            document.getElementById("requisitos").style.display = "inline";
            document.getElementById("mostrarRequisitos").innerHTML = '*Ocultar requisitos de contraseña';
            // Finalmente, ver requisitos pasa a cierto.
            verRequisitos = true;
        }
    }

    // Al botón de validar se le da la función que comprobará que todos los campos sean validos.
    document.getElementById("validar").addEventListener("click", validar);

    function validar() {
        // Se obtiene el formulario
        let formulario = document.getElementById("form");

        // Y de ahi se obtienen todos sus campos.
        let nombre = formulario.name.value;
        let direccion = formulario.address.value;
        let numeroTelefono = formulario.number.value;

        // Se crean los campos de contraseña
        let actual = ''
        let contrasenya = ''
        let confirmarContrasenya = ''

        // Y si cambiarContrasenya esta activo, se asignan los valores del formulario.
        if (cambiarContrasenya) {
            actual = formulario.old.value
            contrasenya = formulario.newPass.value
            confirmarContrasenya = formulario.confirmPassword.value
        }

        // Si algun campo esta vacio, se avisará al usuario.
        if (
            (nombre == null || nombre == '') ||
            (direccion == null || direccion == '') ||
            (numeroTelefono == null || numeroTelefono == '')
        ) {
            error.innerHTML = "Formulario incompleto.";
            error.style.color = "red";
        } else {
            // Si el teléfono no es valido, se avisa al usuario.
            if (!numeroDeTelefono.test(numeroTelefono)) {
                error.innerHTML = "El número de teléfono no es valido.";
                error.style.color = "red";
            } 
            // Si cambiar contrasenya esta activo
            else if (cambiarContrasenya) {
                // Se comprueba si sus campos son nulos.
                if ((contrasenya == null || contrasenya == '') || (confirmarContrasenya == null || confirmarContrasenya == '')) {
                    error.innerHTML = "Formulario incompleto.";
                    error.style.color = "red";
                } 
                // se comprueba que las contraseñas coincidan
                else if (contrasenya != confirmarContrasenya) {
                    error.innerHTML = "Las contraseñas no coinciden.";
                    error.style.color = "red";
                }
                // Se comprueba que las contraseñas son validas
                else if (!passwordRegEx.test(contrasenya) || !passwordRegEx.test(actual)) {
                    error.innerHTML = "La contraseña no es valida.";
                    error.style.color = "red";
                } 
                // Si todo es valido, se hará click en submit para pasarlo a servidor.
                else {
                    $("#update").click();
                }
            } else {
                $("#update").click();
            }
        }
    }

    /**
     * función que manda al usuario a la zona de usuario.
     */
    function goBack() {
        window.location.href = 'zonaUsuario.php'
    }
</script>