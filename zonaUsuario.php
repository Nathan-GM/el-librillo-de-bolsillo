<?php
    include_once ('./templates/header.php');
    if (!isset($user)) {
        header("Location: login.php");
    }
?>
    <main>
        <section class='contenido'> <!-- Contenido principal -->
            <h1>
                Bienvenido <?php echo $user['Nombre']?>  
            </h1>
            <!-- Ajustar estilos -->
            <div class='cards'>
                <div class='generalCard'>
                    <h2>Carrito</h2>
                    <p>¿Quieres ver cuáles han sido tus últimas compras? ¡Revisalas!</p>
                    <p>Si no has hecho antes ningúna compra, aquí no se mostrará nada, ¡ojea la tienda y haz tu primera compra!</p>
                    <a href="carritos.php"><button>Ver carritos anteriores</button></a>
                </div>
                <div class='generalCard'>
                    <h2>Editar perfil</h2>
                    <p>¿Algún dato introducido era incorrecto?</p>
                    <p>¡Edita tu perfil y confirma que todo sea correcto para tus compras!</p>

                    <a href="logout.php"><button disabled>Editar perfil.</button></a>
                </div>
                <div class='generalCard'>
                    <h2>Cerrar sesión</h2>
                    <p>¿Deseas cerrar sesión? Pulsa el botón para cerrarla.</p>
                    <p>Si vuelves a acceder se te pedirá tus credenciales nuevamente</p>
                    <a href="logout.php"><button>Cerrar sesión</button></a>
                </div>
            </div>


        </section>
    </main>
<?php
    include_once ('./templates/footer.php');
?>