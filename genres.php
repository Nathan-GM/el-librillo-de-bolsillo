<?php
    include_once('./templates/header.php');
    $genres = [];
    $query = "SELECT * FROM generos";
    $gResult = $databaseConnection->query($query);

    $position = 0;
    while ($fila = $gResult->fetch_assoc()) {
        $tmp = [];
        $tmp['id'] = $fila['ID'];
        $tmp['nombre'] = $fila['Nombre'];
        $genres[$position] = $tmp;
        $position = $position + 1;
    }
?>
    <main>
        <section class='contenido'>
            <h1>Listado de géneros disponibles</h1>
            <p>
                Aquí encontrarás un listado de todos los géneros disponibles. <br>
                Pulsar en ellos te llevará a los productos de dicho género.
            </p>
            <?php
                if (count($genres) == 0) {
                    echo "<h2>Parece que ha ocurrido un problema, intentalo más tarde</h2>";
                } else{
                    echo "<div class='cards'>";
                    foreach ($genres as $key => $value) {
                        echo "<div class='genreCard' onclick='test(". $genres[$key]['id'] .")'>";
                        echo "<h1>" . $genres[$key]['nombre'] . "</h1>";
                        echo "</div>";
                    }
                    echo "</div>";
                }
            ?>
        </section>
    </main>
<?php
    include_once("./templates/footer.php");
?>

<script>
    function test(id){
        window.location.href = "products.php?genero=" + id;
    }
</script>