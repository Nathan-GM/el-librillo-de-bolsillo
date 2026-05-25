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

                    <a href="editUser.php"><button>Editar perfil.</button></a>
                </div>
                <div class='generalCard'>
                    <h2>Cerrar sesión</h2>
                    <p>¿Deseas cerrar sesión? Pulsa el botón para cerrarla.</p>
                    <p>Si vuelves a acceder se te pedirá tus credenciales nuevamente</p>
                    <a href="logout.php"><button>Cerrar sesión</button></a>
                </div>
            </div>

            <h1>
                Temperaturas  
            </h1>

            <div class='generalCard'>
                <div id='data'>
                    <h2 id='ciudad'></h2>
                    <h4 id='fecha'></h4>
                    <p id='tiempo'></p>
                    <p id='min'></p>
                    <p id='max'></p>
                </div>
                <p id='errorTemp'></p>
                <button id='getTemp'>Obtener temperaturas</button>
            </div>


        </section>
    </main>
<?php
    include_once ('./templates/footer.php');
?>

<script>
    // Se oculta el apartado de datos.
    document.getElementById("data").style.display = "none"; 
    // Se obtiene el elemento con id ErrorTemp para gestionar los errores de la temperatura.
    let errorTemp = document.getElementById("errorTemp")
    errorTemp.style.display = "none";

    // Se le da una función click al botón getTemp.
    document.getElementById("getTemp").addEventListener("click", temp)

    // Elementos de la petición a la página.
    let query = "";

    /**
     * Función que intentará obtener la temperatura y mostrarla por pantalla.
     */
    function temp() {
        // Se oculta el error
        errorTemp.style.display = "none";
        // Se usa getCurrentPosition para obtener la ubicación, en caso de error se llama a la funcion error.
        // Referencia: https://developer.mozilla.org/en-US/docs/Web/API/Geolocation/getCurrentPosition
        navigator.geolocation.getCurrentPosition(Ubicacion, error)
    };

    /**
     * Función que obtiene la ubicación del usuario.
     */
    function Ubicacion(position) {
        // Obtiene del objeto position la latitud y la longitud.
        let latitud = position.coords.latitude;
        let longitud = position.coords.longitude;
        // los agrega en la query para openweathermap.org
        query = "?lat="+ latitud + "&lon=" + longitud;

        // Se obtienen los elementos donde se indicarán los datos.
        let city = document.getElementById("ciudad");
        let date = document.getElementById("fecha");
        let tiempo = document.getElementById("tiempo");
        let tMin = document.getElementById("min");
        let tMax = document.getElementById("max");

        // Se hace la consulta a la página con la API y en unidades metricas.
        fetch('http://api.openweathermap.org/data/2.5/weather' + query + '&appid=f242c4eb775e0b1f53705a5df1bc0118&units=metric')
        // Despues convierte los resultados en JSON.
        .then(resultado => resultado.json())
        // Con esos resultados:
        .then(datos => {
            // Si el código es distinto a 200 representará un error, avisandose como tal.
            if (datos.cod != 200) {
                errorTemp.innerHTML = "Ha ocurrido un error al obtener la temperatura."
                errorTemp.style.color = "red";
                errorTemp.style.display = "inline";
            } else{
                // Si no, se obtiene la fecha de la solicitud.
                // Dado que es un timestamp en unix se multiplica por 1000 para obtener
                // el valor correcto
                // Ref: https://stackoverflow.com/questions/847185/convert-a-unix-timestamp-to-time-in-javascript
                let fechaDeDatos = new Date(datos.dt * 1000);

                // Se obtiene día mes y año
                let dia = fechaDeDatos.getDate();
                let mes = fechaDeDatos.getMonth() + 1;
                let anyo = fechaDeDatos.getFullYear();
                let fecha = `${dia}/${mes}/${anyo}`;

                // Se asignan al HTML los datos obtenidos.
                city.innerHTML = "Ubicación obtenida en la ciudad: " + datos.name;
                date.innerHTML = "Datos tomados el: " + fecha
                // Se llama a la función obtenerTiempo para saber que tiempo hay al hacer la petición.
                // Tras ello lo agrega al html.
                let tmpTiempo = obtenerTiempo(datos.weather[0].description)
                tiempo.innerHTML = tmpTiempo;

                // Se asignan las tempearturas máximas y minimas.
                tMin.innerHTML = "Temperatura Minima: " + datos.main.temp_min + "ºC";
                tMax.innerHTML = "Temperatura Máxima: " + datos.main.temp_max + "ºC";

                // Finalmente, muestra los datos con FadeIn y oculta el botón con FadeOut.
                $(document).ready(function() {
                    $("#data").fadeIn("slow");
                    $("#getTemp").fadeOut("slow");
                })

            }
        })
    }

    /**
     * Función que se ejecuta en caso de error al obtener los datos.
     */
    function error(err) {
        // Si el mensaje es que el usuario rechazo la localización
        if (err.message == "User denied Geolocation") {
            // Se muestra un mensaje personalizado.
            errorTemp.innerHTML = "No se ha permitido la ubicación.";
        } else {
            // Si no, se muestra el error.
            errorTemp.innerHTML = `Error: ${err.message}`;
        }
        // Se le da los estilos necesarios.
        errorTemp.style.display = 'inline';
        errorTemp.style.color = "red";
    }

    /**
     * Función que según el valor obtenido en el JSON devuelve el tiempo
     * que hace ahora mismo.
     */
    function obtenerTiempo(valor) {
        // Según los valores que puede devolver
        // Se devuelve la opción traducida.
        // REF: https://openweathermap.org/api/weather-conditions
        switch(valor) {
            case "clear sky":
                return "Despejado";
            case "few clouds":
                return "Pocas nubes";
            case "scattered clouds":
                return "Nubes dispersas";
            case "broken clouds":
                return "Intervalos nubosos";
            case "shower rain":
                return "Pequeños chubascos"
            case "rain":
                return "Lluvia";
            case "thunderstorm":
                return "Tormenta";
            case "snow":
                return "Nevando";
            case "mist":
                return "Niebla";
            default:
                return valor;
        }
    }
</script>