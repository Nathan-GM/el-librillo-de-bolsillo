<?php
    // Se abre la sesión
    session_start();
    // Se destruye
    session_destroy();
    // Y se envia a index.php
    header("Location: index.php");
?>