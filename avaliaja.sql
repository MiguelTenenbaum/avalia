-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2026 at 08:38 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `avaliaja`
--
CREATE DATABASE IF NOT EXISTS `avaliaja` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `avaliaja`;

-- --------------------------------------------------------

--
-- Table structure for table `avaliacoes`
--

CREATE TABLE `avaliacoes` (
  `id_avaliacao` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_jogo` int(11) NOT NULL,
  `nota_geral` tinyint(4) NOT NULL,
  `comentario_geral` text DEFAULT NULL,
  `nota_performance` tinyint(4) DEFAULT NULL,
  `plataforma` varchar(100) DEFAULT NULL,
  `especificacoes` text DEFAULT NULL,
  `possui_bug` enum('sim','nao') DEFAULT 'nao',
  `descricao_bug` text DEFAULT NULL,
  `visivel` tinyint(1) NOT NULL DEFAULT 1,
  `data_avaliacao` datetime NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `jogos`
--

CREATE TABLE `jogos` (
  `id_jogo` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `genero` varchar(100) NOT NULL,
  `data_lancamento` date DEFAULT NULL,
  `desenvolvedora` varchar(150) DEFAULT NULL,
  `distribuidora` varchar(150) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `capa_url` varchar(255) DEFAULT NULL,
  `data_cadastro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jogos`
--

INSERT INTO `jogos` (`id_jogo`, `titulo`, `genero`, `data_lancamento`, `desenvolvedora`, `distribuidora`, `descricao`, `capa_url`, `data_cadastro`) VALUES
(1, 'Hollow Knight', 'Metroidvania', '2017-02-24', 'Team Cherry', 'Team Cherry', 'Jogo de ação e aventura em um mundo subterrâneo.', 'https://shared.cloudflare.steamstatic.com/store_item_assets/steam/apps/367520/library_600x900.jpg', '2026-05-25 14:39:13'),
(2, 'Stardew Valley', 'Simulação', '2016-02-26', 'ConcernedApe', 'ConcernedApe', 'Jogo de fazenda, exploração e relacionamento com personagens.', 'https://shared.cloudflare.steamstatic.com/store_item_assets/steam/apps/413150/library_600x900.jpg', '2026-05-25 14:39:13'),
(3, 'Celeste', 'Plataforma', '2018-01-25', 'Maddy Makes Games', 'Maddy Makes Games', 'Jogo de plataforma focado em desafio e narrativa.', 'https://shared.cloudflare.steamstatic.com/store_item_assets/steam/apps/504230/library_600x900.jpg', '2026-05-25 14:39:13');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('usuario','admin') NOT NULL DEFAULT 'usuario',
  `data_cadastro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nome`, `email`, `senha`, `tipo`, `data_cadastro`) VALUES
(1, 'Administrador', 'admin@admin.com', '$2y$10$GvDfKHhjMOQ6ZoJLncTQLujZJvikkDo5bR0tycEHE35..xI3nY362', 'admin', '2026-05-25 14:16:18'),
(4, 'Miguel', 'miguel.tenenbaum@acad.ufsm.br', '$2y$10$sFgGo9lN4RQqUvOCepRn6emMbt6f2qF1er6kZeNrfqolMPy6s9Tne', 'admin', '2026-05-25 15:29:25'),
(11, 'teste3', 'teste2@gmail.com', '$2y$10$SWPxDOShAABXHcGCvGC3I.4v9yJQnD/M1e3PPpVvSjFYrUQrxGSJm', 'usuario', '2026-05-25 17:07:58'),
(18, 'teste', 'teste@teste.com', '$2y$10$pEfrXYf/2RIP2OLpyYs0N.Iqm4bRWI6CFsTYFld7khJFIB7iWe8Am', 'usuario', '2026-06-18 16:57:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD PRIMARY KEY (`id_avaliacao`),
  ADD UNIQUE KEY `uq_usuario_jogo` (`id_usuario`,`id_jogo`),
  ADD KEY `fk_avaliacoes_jogo` (`id_jogo`);

--
-- Indexes for table `jogos`
--
ALTER TABLE `jogos`
  ADD PRIMARY KEY (`id_jogo`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `avaliacoes`
--
ALTER TABLE `avaliacoes`
  MODIFY `id_avaliacao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jogos`
--
ALTER TABLE `jogos`
  MODIFY `id_jogo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD CONSTRAINT `fk_avaliacoes_jogo` FOREIGN KEY (`id_jogo`) REFERENCES `jogos` (`id_jogo`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_avaliacoes_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
