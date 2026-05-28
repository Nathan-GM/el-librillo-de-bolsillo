-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-05-2026 a las 17:34:01
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `proyecto`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `articulos`
--

CREATE TABLE `articulos` (
  `ID` int(11) NOT NULL,
  `GeneroID` int(11) DEFAULT NULL,
  `Nombre` varchar(255) NOT NULL,
  `Descripcion` varchar(255) DEFAULT NULL,
  `Stock` int(11) NOT NULL,
  `Autor` varchar(255) DEFAULT NULL,
  `Editorial` varchar(255) DEFAULT NULL,
  `Portada` varchar(255) DEFAULT NULL,
  `Precio` float NOT NULL,
  `deleted` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `estado` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `elementoscarrito`
--

CREATE TABLE `elementoscarrito` (
  `carritoId` int(11) NOT NULL,
  `articuloId` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `generos`
--

CREATE TABLE `generos` (
  `ID` int(11) NOT NULL,
  `Nombre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resenya`
--

CREATE TABLE `resenya` (
  `id` int(11) NOT NULL,
  `idArticulo` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `puntuacion` int(11) DEFAULT NULL,
  `mensaje` varchar(255) DEFAULT NULL,
  `fecha` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `Email` varchar(255) NOT NULL,
  `Contrasenya` varchar(255) NOT NULL,
  `Nombre` varchar(255) NOT NULL,
  `Telefono` varchar(12) DEFAULT NULL,
  `Rol` varchar(255) NOT NULL,
  `Direccion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`Email`, `Contrasenya`, `Nombre`, `Telefono`, `Rol`, `Direccion`) VALUES
('admin@gmail.com', '$2y$10$Wo5jq0ZuQiGv1QLHbPJs4uuqhbhhxQ6wcRolNO63R5Tab4aas34em', 'Administrador', '123456789', 'admin', 'suCasa'),
('usuario@gmail.com', '$2y$10$EKKHx/5FglLYWrmUQcPPFOh8spxoh6wRFvotMu4F45yZT2WeMF0Qe', 'Usuario', '123456789', 'usuario', 'algo');

INSERT INTO `carrito` (`id`, `user_email`, `estado`) VALUES
('1', 'admin@gmail.com', 'pendiente'),
('2', 'usuario@gmail.com', 'pendiente');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `articulos`
--
ALTER TABLE `articulos`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `GeneroID` (`GeneroID`);

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_email` (`user_email`);

--
-- Indices de la tabla `elementoscarrito`
--
ALTER TABLE `elementoscarrito`
  ADD PRIMARY KEY (`carritoId`,`articuloId`),
  ADD KEY `articuloId` (`articuloId`);

--
-- Indices de la tabla `generos`
--
ALTER TABLE `generos`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `resenya`
--
ALTER TABLE `resenya`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idArticulo` (`idArticulo`),
  ADD KEY `email` (`email`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`Email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `articulos`
--
ALTER TABLE `articulos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `generos`
--
ALTER TABLE `generos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `resenya`
--
ALTER TABLE `resenya`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `articulos`
--
ALTER TABLE `articulos`
  ADD CONSTRAINT `articulos_ibfk_1` FOREIGN KEY (`GeneroID`) REFERENCES `generos` (`ID`);

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`user_email`) REFERENCES `usuarios` (`Email`);

--
-- Filtros para la tabla `elementoscarrito`
--
ALTER TABLE `elementoscarrito`
  ADD CONSTRAINT `elementoscarrito_ibfk_1` FOREIGN KEY (`carritoId`) REFERENCES `carrito` (`id`),
  ADD CONSTRAINT `elementoscarrito_ibfk_2` FOREIGN KEY (`articuloId`) REFERENCES `articulos` (`ID`);

--
-- Filtros para la tabla `resenya`
--
ALTER TABLE `resenya`
  ADD CONSTRAINT `resenya_ibfk_1` FOREIGN KEY (`idArticulo`) REFERENCES `articulos` (`ID`),
  ADD CONSTRAINT `resenya_ibfk_2` FOREIGN KEY (`email`) REFERENCES `usuarios` (`Email`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
