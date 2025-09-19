-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le :  ven. 19 sep. 2025 à 23:12
-- Version du serveur :  10.1.28-MariaDB
-- Version de PHP :  7.1.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `qr_presence_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `absences`
--

CREATE TABLE `absences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `presences`
--

CREATE TABLE `presences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `entry_time` time DEFAULT NULL,
  `exit_time` time DEFAULT NULL,
  `status` enum('present','absent') DEFAULT 'present'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `presences`
--

INSERT INTO `presences` (`id`, `user_id`, `date`, `created_at`, `entry_time`, `exit_time`, `status`) VALUES
(1, 6, '2025-09-10', '2025-09-10 09:48:48', '11:48:48', '11:49:39', 'present'),
(2, 8, '2025-09-10', '2025-09-10 09:50:44', '11:50:44', '11:51:13', 'present'),
(3, 6, '2025-09-11', '2025-09-11 11:55:08', '13:55:08', '14:01:10', 'present'),
(4, 6, '2025-09-18', '2025-09-18 20:24:26', '22:24:26', NULL, 'absent'),
(5, 6, '2025-09-19', '2025-09-19 09:23:28', '11:23:28', '21:28:51', 'present'),
(6, 8, '2025-09-19', '2025-09-19 19:23:03', '21:23:03', '21:23:15', 'present'),
(7, 11, '2025-09-19', '2025-09-19 19:37:54', '21:37:54', '22:31:14', 'present');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `qr_data` varchar(255) NOT NULL DEFAULT '',
  `identity_card` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `est_actif` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `password`, `photo`, `qr_code`, `qr_data`, `identity_card`, `role`, `created_at`, `est_actif`) VALUES
(6, 'Admin', 'System', 'admin@example.com', '$2y$10$B3sHZtxlO7FJGRD30iSkfejrn1MzceVuZqdpEYQqTEbeFg93BcUvq', 'uploads/6_1757463787.png', NULL, '', NULL, 'admin', '2025-09-09 16:21:00', 1),
(8, 'karume', 'Kojocampany', 'chalij68@gmail.com', '$2y$10$rHnqQ4WFiCNkNGK7uldYYukfvcIXUNUTiOtbf1ML7N2VLPheFT9aG', 'uploads/1757435926_image de menuprincipale.png', 'user_8.png', 'PRESENCE_SYSTEM:USER:8:karume:Kojocampany:1757435926', 'card_8.png', 'user', '2025-09-09 16:38:46', 1),
(9, 'BRARUDI', 'industrie', 'brarudi@gmail.com', '$2y$10$511A5olvkzRz1LYp2arZB.hiuqkU3V9lMwQ3mwEEHrZW/TTvzVbk6', 'uploads/1757591546_R.png', 'user_9.png', 'PRESENCE_SYSTEM:USER:9:BRARUDI:industrie:1757591546', 'card_9.png', 'user', '2025-09-11 11:52:26', 1),
(10, 'KOJO', 'AIA', 'kojo@gmail.com', '$2y$10$ufk1y.o4NTsByHytCqYpiez5HS4x.qXR3NZ8t7L.hqb.FwXqmzx8W', 'uploads/1758309964_Screenshot_1.ico', NULL, 'PRESENCE:KOJO:AIA:1758309964', NULL, 'user', '2025-09-19 19:26:04', 1),
(11, 'chris', 'boys', 'chris@gmail.com', '$2y$10$kydrZ0U88NsrbE1S5a5oI.dsEnpcoNYCo18SCLXeVtHn4m2w3Ubr.', 'uploads/1758310288_kojo IA.ico', NULL, 'PRESENCE:chris:boys:1758310227', NULL, 'user', '2025-09-19 19:30:27', 1),
(13, 'test', 'encore', 'test@gmail.com', '$2y$10$5CMKg8Fnds/AP9Qx2gqcfuNPBp3PpRi2LTaKhIlodTXYpNduV/QTu', 'uploads/1758311441_logoIA.webp', NULL, 'PRESENCE:test:encore:1758311441', NULL, 'user', '2025-09-19 19:50:41', 0),
(15, 'solution', 'réussie', 'solution@gmail.com', '$2y$10$POsQgPtHSkXOlkby94k6KO.JHCjaknHl/J1TOHTwtVx2UYs8d0nRa', 'uploads/1758311693_kojo IA.png', NULL, 'PRESENCE:solution:réussie:1758311693', NULL, 'user', '2025-09-19 19:54:53', 0);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `absences`
--
ALTER TABLE `absences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `presences`
--
ALTER TABLE `presences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `absences`
--
ALTER TABLE `absences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `presences`
--
ALTER TABLE `presences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `absences`
--
ALTER TABLE `absences`
  ADD CONSTRAINT `absences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `presences`
--
ALTER TABLE `presences`
  ADD CONSTRAINT `presences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
