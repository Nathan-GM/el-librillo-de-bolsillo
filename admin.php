<?php
    include_once('./templates/header.php');
    
    if(!isset($user)) {
        header("Location: login.php");
        exit;
    }
    if($user['Rol'] != 'admin') {
        header("Location: index.php");
        exit;
    }
?>
<main>
    <!-- Contenido principal -->
    <section class='contenido'>
        <h1>Bienvenido al apartado de administrador <?php echo $user['Nombre']?></h1>
        <p>Desde está sección, podrás agregar, editar, eliminar o listar los productos.</p>
        <!-- Sección de tarjetas -->
        <div class='adminCards'>
            <!-- Listado de productos -->
            <div class='generalCard'>
                <h2>Listado de articulos</h2>
                <p>¿Quieres ver los productos de la tienda? Desde este apartado puedes verlos, así como su stock</p>
                <button id='list'>Ver los productos.</button>
            </div>
            <!-- Agregar los productos -->
            <div class='generalCard'>
                <h2>Agregar productos</h2>
                <p>¿Alguna novedad? ¡Desde este apartado puedes agregarla como producto!</p>
                <button id='add'>Agregar producto.</button>
            </div>
            <div class='generalCard'>
                <h2>Agregar generos</h2>
                <p>¿Falta algún tipo de género? ¡Agregalo desde aquí!</p>
                <button id='addGenre'>Agregar género.</button>
            </div>
            <!-- Listado de productos -->
            <div class='generalCard'>
                <h2>Editar productos</h2>
                <p>¿Quieres modificar alguno de los productos? Desde este apartado puedes modificar sus datos</p>
                <button id='edit'>Editar productos.</button>
            </div>
            <!-- Listado de productos -->
            <div class='generalCard'>
                <h2>Eliminar producto</h2>
                <p>¿Algún producto ya no está disponible? Puedes eliminarlo desde esta sección</p>
                <button id='remove'>Eliminar productos.</button>
            </div>
            <div class='generalCard'>
                <h2>Cambiar usuario a administrador.</h2>
                <p>¿Necesitas agregar un usuario como admiminstrador? Actualiza el rol desde aqui.</p>
                <button id='role'>Cambiar roles.</button>
            </div>
        </div>
    </section>
</main>

<?php
    include_once('./templates/footer.php');
?>

<script>
    document.getElementById("list").addEventListener('click', goToList, false);
    document.getElementById("add").addEventListener('click', goToAddProduct, false);
    document.getElementById("addGenre").addEventListener('click', goToAddGenre, false);
    document.getElementById("edit").addEventListener('click', goToEditProduct, false);
    document.getElementById("remove").addEventListener('click', goToDeleteProduct, false);
    document.getElementById("role").addEventListener('click', goToEditUserRole, false);

    function goToList() {
        window.location.href = 'admin/list.php';
    }
    function goToAddProduct() {
        window.location.href = 'admin/add.php';
    }
    function goToAddGenre() {
        window.location.href = 'admin/addgenre.php';
    }
    function goToEditProduct() {
        window.location.href = 'admin/editProduct.php';
    }
    function goToDeleteProduct() {
        window.location.href = 'admin/removeProduct.php';
    }
    function goToEditUserRole() {
        window.location.href = 'admin/userRole.php';
    }
</script>