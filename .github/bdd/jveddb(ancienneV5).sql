-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : mysql
-- Généré le : ven. 16 fév. 2024 à 11:44
-- Version du serveur : 8.3.0
-- Version de PHP : 8.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `jveddb`
--
CREATE DATABASE IF NOT EXISTS `jveddb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `jveddb`;

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

CREATE TABLE `categorie` (
  `id` int NOT NULL,
  `nom` varchar(255) NOT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deletedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id`, `nom`, `createdAt`, `updatedAt`, `deletedAt`) VALUES
(1, 'Jeux vidéos', '2024-02-11 16:57:28', '2024-02-14 15:47:00', NULL),
(2, 'Console', '2024-02-14 15:01:30', '2024-02-14 15:38:17', NULL),
(3, 'BlaBla', '2024-02-14 15:39:58', '2024-02-14 15:39:58', NULL),
(4, 'Actus', '2024-02-14 15:49:53', '2024-02-14 15:49:58', '2024-02-14 15:49:58');

-- --------------------------------------------------------

--
-- Structure de la table `content`
--

CREATE TABLE `content` (
  `id` int NOT NULL,
  `commentaire` text NOT NULL,
  `auteur` int NOT NULL,
  `idTopic` int NOT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deletedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `content`
--

INSERT INTO `content` (`id`, `commentaire`, `auteur`, `idTopic`, `createdAt`, `updatedAt`, `deletedAt`) VALUES
(1, 'Salut, c’est quoi les meilleures voitures dans GTA V ?', 1, 1, '2024-02-14 16:17:29', '2024-02-14 18:48:51', NULL),
(2, 'Pour les courses, je te recommande la Pariah, elle est super rapide.', 30, 1, '2024-02-14 16:17:29', '2024-02-14 17:10:52', NULL),
(3, 'J’adore la Zentorno pour son look et sa vitesse.', 31, 1, '2024-02-14 16:17:29', '2024-02-14 17:10:55', NULL),
(4, 'Ne sous-estime pas la Sultan RS, pas la plus rapide mais super pour la conduite en ville.', 31, 1, '2024-02-14 16:17:29', '2024-02-14 16:17:29', NULL),
(5, 'La Vigilante pour le fun, elle a un boost comme dans les films!La Vigilante pour le fun, elle a un boost comme dans les films!La Vigilante pour le fun, elle a un boost comme dans les films!La Vigilante pour le fun, elle a un boost comme dans les films!La Vigilante pour le fun, elle a un boost comme dans les films!La Vigilante pour le fun, elle a un boost comme dans les films!La Vigilante pour le fun, elle a un boost comme dans les films!La Vigilante pour le fun, elle a un boost comme dans les films!La Vigilante pour le fun, elle a un boost comme dans les films!La Vigilante pour le fun, elle a un boost comme dans les films!La Vigilante pour le fun, elle a un boost comme dans les films!La Vigilante pour le fun, elle a un boost comme dans les films!La Vigilante pour le fun, elle a un boost comme dans les films!La Vigilante pour le fun, elle a un boost comme dans les films!La Vigilante pour le fun, elle a un boost comme dans les films!La Vigilante pour le fun, elle a un boost comme dans les films!La Vigilante pour le fun, elle a un boost comme dans les films!La Vigilante pour le fun, elle a un boost comme dans les films!La Vigilante pour le fun, elle a un boost comme dans les films!La Vigilante pour le fun, elle a un boost comme dans les films!', 34, 1, '2024-02-14 16:17:29', '2024-02-14 17:39:48', NULL),
(6, 'C\'est quand la sortie ?', 1, 2, '2024-02-14 18:29:58', '2024-02-14 18:31:06', NULL),
(7, 'WOW t\'as lag ouuuu', 1, 1, '2024-02-15 18:25:13', '2024-02-15 18:25:13', NULL),
(8, 'Salut, l\'équipe quelqu\'un coco la date de sortie ? Je la trouve nulle part', 1, 3, '2024-02-15 19:17:11', '2024-02-15 19:17:11', NULL),
(9, 'teest', 1, 4, '2024-02-15 19:18:04', '2024-02-15 19:18:04', NULL),
(10, 'AHAHA', 1, 1, '2024-02-15 19:35:11', '2024-02-15 19:35:11', NULL),
(11, 'ouin ouin', 1, 5, '2024-02-15 19:37:00', '2024-02-15 19:37:00', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

CREATE TABLE `role` (
  `id` int NOT NULL,
  `roleName` varchar(35) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `role`
--

INSERT INTO `role` (`id`, `roleName`) VALUES
(1, 'superAdmin'),
(2, 'moderateur'),
(3, 'user');

-- --------------------------------------------------------

--
-- Structure de la table `sujet`
--

CREATE TABLE `sujet` (
  `id` int NOT NULL,
  `nom` varchar(255) NOT NULL,
  `auteur` int NOT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deletedAt` timestamp NULL DEFAULT NULL,
  `idCategorie` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `sujet`
--

INSERT INTO `sujet` (`id`, `nom`, `auteur`, `createdAt`, `updatedAt`, `deletedAt`, `idCategorie`) VALUES
(1, 'GTA', 1, '2024-02-11 16:57:55', '2024-02-11 16:57:55', NULL, 1),
(2, 'Avatar', 1, '2024-02-15 19:17:11', '2024-02-15 19:17:11', NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `topic`
--

CREATE TABLE `topic` (
  `id` int NOT NULL,
  `nom` varchar(255) NOT NULL,
  `auteur` int NOT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deletedAt` timestamp NULL DEFAULT NULL,
  `idSujet` int NOT NULL,
  `valide` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `topic`
--

INSERT INTO `topic` (`id`, `nom`, `auteur`, `createdAt`, `updatedAt`, `deletedAt`, `idSujet`, `valide`) VALUES
(1, 'Braquaque GTA5 Online', 1, '2024-02-11 16:58:46', '2024-02-15 19:33:09', NULL, 1, 1),
(2, 'GTA6', 1, '2024-02-11 17:12:09', '2024-02-14 18:33:23', NULL, 1, 0),
(3, 'Date de sortie Avatar', 1, '2024-02-15 19:17:11', '2024-02-15 19:17:11', NULL, 2, 0);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `pseudo` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `idRole` int NOT NULL,
  `template` varchar(25) DEFAULT NULL,
  `bloque` tinyint(1) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deletedAt` timestamp NULL DEFAULT NULL,
  `emailCheck` tinyint(1) DEFAULT '0',
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `dateToken` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `pseudo`, `email`, `password`, `idRole`, `template`, `bloque`, `createdAt`, `updatedAt`, `deletedAt`, `emailCheck`, `token`, `dateToken`) VALUES
(1, 'Martin', 'martinsimongo@gmail.com', '$2y$10$HU3lT/HbgTGimarFkl/dpO8gBKzUORfqJ99hyDVv8.tDUkP8rNQ4e', 1, '', NULL, '2024-02-07 20:45:53', '2024-02-15 21:55:15', NULL, 1, NULL, NULL),
(30, 'Kamil', 'totor@gmail.com', '$2y$10$Mp6XmEd/WvZ0WRrSaA7pB.jNaRz/yjniHEhHyFSgx3yjZsa.NCGEm', 3, '', NULL, '2024-02-12 12:43:50', '2024-02-16 11:43:23', NULL, 1, NULL, NULL),
(31, 'Gator', 'martin.simn91@gmail.com', '$2y$10$Vgu2ZsFhsV145h11VHzYq.RBcGRsJLbs36ElKh9DmjbRb0qXq4tB6', 3, NULL, NULL, '2024-02-14 11:43:50', '2024-02-14 13:22:43', NULL, 1, NULL, NULL),
(32, 'Martin', 'martinsimongso@gmail.com', '$2y$10$qOtrDavco6lGfmiGmuZWmOapQ.8H1nhwIOwfbwhdN6wDAOcO6drm2', 1, NULL, NULL, '2024-02-14 12:34:54', '2024-02-14 12:34:54', NULL, 0, NULL, NULL),
(33, 'Bob', 'toto@gmail.com', '$2y$10$B.JuVLgkVnxCvU/FJZEm0.wPgTRYroR.XV4atyV7hz6GHkC2dLbM2', 3, NULL, NULL, '2024-02-14 13:09:05', '2024-02-14 13:26:22', NULL, 0, NULL, NULL),
(34, 'Totoche', 'ms@gmail.com', '$2y$10$hKINrVpKMfm6ug7fbaMvzeKnv3Hiz1qaDNAyAIXJjFLosQZo.GLES', 3, NULL, NULL, '2024-02-14 13:10:59', '2024-02-15 21:17:12', NULL, 1, NULL, NULL),
(35, 'Bicou', 'martinsimoiuhiungo@gmail.com', '$2y$10$CHP.h0EP5rpufH0GrAjmGOcQbWjBq0ggaMHqLElceXGTEYrNumkb2', 3, NULL, NULL, '2024-02-14 13:12:30', '2024-02-14 15:18:28', NULL, 1, NULL, NULL),
(36, 'Bisbis', 'martinbis@gmail.com', '$2y$10$KFizYPxFVlAByyf9OYUaduJ.qveSTxN9/gcU7Pyx0zZ3pLkcuoISO', 2, NULL, NULL, '2024-02-14 18:48:13', '2024-02-14 19:04:47', NULL, 1, NULL, NULL),
(37, 'touAnk', 'touAnk@gmail.com', '$2y$10$m1NM3FBkb0mUeZNTVbXgLO2DUICx.qF41ueMmn2sSgLuPD1AUFgaa', 3, 'template1', NULL, '2024-02-15 21:39:13', '2024-02-15 21:40:40', NULL, 1, NULL, NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `content`
--
ALTER TABLE `content`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auteur` (`auteur`),
  ADD KEY `idTopic` (`idTopic`);

--
-- Index pour la table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `sujet`
--
ALTER TABLE `sujet`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auteur` (`auteur`),
  ADD KEY `idCategorie` (`idCategorie`);

--
-- Index pour la table `topic`
--
ALTER TABLE `topic`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auteur` (`auteur`),
  ADD KEY `idSujet` (`idSujet`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_unique` (`email`),
  ADD KEY `idRole` (`idRole`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `content`
--
ALTER TABLE `content`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `role`
--
ALTER TABLE `role`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `sujet`
--
ALTER TABLE `sujet`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `topic`
--
ALTER TABLE `topic`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `content`
--
ALTER TABLE `content`
  ADD CONSTRAINT `content_ibfk_1` FOREIGN KEY (`auteur`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `content_ibfk_2` FOREIGN KEY (`idTopic`) REFERENCES `topic` (`id`);

--
-- Contraintes pour la table `sujet`
--
ALTER TABLE `sujet`
  ADD CONSTRAINT `sujet_ibfk_1` FOREIGN KEY (`auteur`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `sujet_ibfk_2` FOREIGN KEY (`idCategorie`) REFERENCES `categorie` (`id`);

--
-- Contraintes pour la table `topic`
--
ALTER TABLE `topic`
  ADD CONSTRAINT `topic_ibfk_1` FOREIGN KEY (`auteur`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `topic_ibfk_2` FOREIGN KEY (`idSujet`) REFERENCES `sujet` (`id`);

--
-- Contraintes pour la table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`idRole`) REFERENCES `role` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
