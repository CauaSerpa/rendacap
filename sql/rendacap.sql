-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3312
-- Tempo de geração: 18-Ago-2024 às 10:12
-- Versão do servidor: 10.4.22-MariaDB
-- versão do PHP: 8.1.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `rendacap`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_permissions`
--

CREATE TABLE `tb_permissions` (
  `id` int(11) NOT NULL,
  `permission_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `tb_permissions`
--

INSERT INTO `tb_permissions` (`id`, `permission_name`) VALUES
(2, 'edit_content'),
(1, 'manage_users'),
(3, 'use_content');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_roles`
--

CREATE TABLE `tb_roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `tb_roles`
--

INSERT INTO `tb_roles` (`id`, `role_name`) VALUES
(1, 'Admin'),
(2, 'Editor'),
(3, 'User');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_role_permissions`
--

CREATE TABLE `tb_role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `tb_role_permissions`
--

INSERT INTO `tb_role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 2),
(2, 3),
(3, 3);

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_users`
--

CREATE TABLE `tb_users` (
  `id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `username` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `active_token` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `recup_password` varchar(255) DEFAULT NULL,
  `recup_password_expiry` datetime DEFAULT NULL,
  `whatsapp` varchar(255) NOT NULL,
  `birth_date` date NOT NULL,
  `cpf` varchar(255) NOT NULL,
  `rg` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `token_used` varchar(255) DEFAULT NULL,
  `2fa` tinyint(1) NOT NULL DEFAULT 1,
  `date_create` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_user_roles`
--

CREATE TABLE `tb_user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `tb_user_roles`
--

INSERT INTO `tb_user_roles` (`user_id`, `role_id`) VALUES
(11, 3),
(13, 3),
(15, 3),
(16, 3),
(17, 3);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `tb_permissions`
--
ALTER TABLE `tb_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Índices para tabela `tb_roles`
--
ALTER TABLE `tb_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Índices para tabela `tb_role_permissions`
--
ALTER TABLE `tb_role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Índices para tabela `tb_users`
--
ALTER TABLE `tb_users`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `tb_user_roles`
--
ALTER TABLE `tb_user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `tb_permissions`
--
ALTER TABLE `tb_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `tb_roles`
--
ALTER TABLE `tb_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `tb_users`
--
ALTER TABLE `tb_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `tb_role_permissions`
--
ALTER TABLE `tb_role_permissions`
  ADD CONSTRAINT `tb_role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `tb_roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tb_role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `tb_permissions` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `tb_user_roles`
--
ALTER TABLE `tb_user_roles`
  ADD CONSTRAINT `tb_user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tb_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tb_user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `tb_roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
