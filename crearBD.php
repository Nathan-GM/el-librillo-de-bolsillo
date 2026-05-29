<?php
// Fichero para crear la base de datos usando docker.
    $nombreServidor = "db";
    $username = "root";
    $password = "root";
    $database = "proyecto";
    $port = 3306;

    $databaseConnection = new mysqli($nombreServidor, $username, $password, $database, $port);

    // Se crea la base de datos
    $sql = "CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8 COLLATE utf8_spanish_ci";
    $databaseConnection->query($sql);

    $databaseConnection->select_db($database);

    // Se crean las tablas

    // Generos
    $sql = "CREATE TABLE generos (
        ID int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
        Nombre varchar(255) NOT NULL
        ) ENGINE=InnoDB;";

        $databaseConnection->query($sql);

    // Usuarios
    $sql = "CREATE TABLE usuarios (
        Email varchar(255) NOT NULL PRIMARY KEY,
        Contrasenya varchar(255) NOT NULL,
        Nombre varchar(255) NOT NULL,
        Telefono varchar(12) DEFAULT NULL,
        Rol varchar(255) NOT NULL,
        Direccion varchar(255) DEFAULT NULL
        ) ENGINE=InnoDB;";
        $databaseConnection->query($sql);
        

    // Articulos
    $sql = "CREATE TABLE articulos (
    ID int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    GeneroID int(11) DEFAULT NULL,
    Nombre varchar(255) NOT NULL,
    Descripcion varchar(255) DEFAULT NULL,
    Stock int(11) NOT NULL,
    Autor varchar(255) DEFAULT NULL,
    Editorial varchar(255) DEFAULT NULL,
    Portada varchar(255) DEFAULT NULL,
    Precio float NOT NULL,
    deleted tinyint(1) DEFAULT NULL,

    FOREIGN KEY (GeneroID) REFERENCES generos(ID)
    ) ENGINE=InnoDB;";

    $databaseConnection->query($sql);

    // Carrito

    $sql ="CREATE TABLE carrito (
    id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_email varchar(255) NOT NULL,
    estado varchar(255) DEFAULT NULL,

    FOREIGN KEY (user_email) REFERENCES usuarios(Email)
    ) ENGINE=InnoDB;";

    $databaseConnection->query($sql);
    
    // Elementos carrito

    $sql ="CREATE TABLE elementoscarrito (
    carritoId int(11) NOT NULL,
    articuloId int(11) NOT NULL,
    cantidad int(11) NOT NULL,

    PRIMARY KEY (carritoId, articuloId),

    FOREIGN KEY (carritoId) REFERENCES carrito(id),
    FOREIGN KEY (articuloId) REFERENCES articulos(ID)

    ) ENGINE=InnoDB;";

    $databaseConnection->query($sql);
    
    // reseñas
    $sql = "CREATE TABLE resenya (
    id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    idArticulo int(11) NOT NULL,
    email varchar(255) NOT NULL,
    puntuacion int(11) DEFAULT NULL,
    mensaje varchar(255) DEFAULT NULL,
    fecha date DEFAULT NULL,

    FOREIGN KEY (idArticulo) REFERENCES articulos(ID),
    FOREIGN KEY (email) REFERENCES usuarios(Email)

    ) ENGINE=InnoDB;";

    $databaseConnection->query($sql);
    
    // INSERTAR
    
    // usuarios
    
    $contrasenya1 = password_hash("12345", PASSWORD_DEFAULT);
    $contrasenya2 = password_hash("Test1234!#", PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (Email, Contrasenya, Nombre, Telefono, Rol, Direccion) VALUES
    ('admin@gmail.com', '$contrasenya1', 'Administrador', '123456789', 'admin', 'suCasa'),
    ('usuario@gmail.com', '$contrasenya2', 'Usuario', '123456789', 'usuario', 'algo');";
    $databaseConnection->query($sql);
    
    
    // Carritos
    $sql = "INSERT INTO carrito (id, user_email, estado) VALUES
    ('1', 'admin@gmail.com', 'pendiente'),
    ('2', 'usuario@gmail.com', 'pendiente');";
    $databaseConnection->query($sql);


?>