-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 11 juin 2026 à 10:45
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `leboncoin_sio1`
--

-- --------------------------------------------------------

--
-- Structure de la table `annonces`
--

CREATE TABLE `annonces` (
  `id` int(11) NOT NULL,
  `titre` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `date_publication` datetime DEFAULT current_timestamp(),
  `id_utilisateur` int(11) NOT NULL,
  `id_categorie` int(11) NOT NULL,
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `annonces`
--

INSERT INTO `annonces` (`id`, `titre`, `description`, `prix`, `date_publication`, `id_utilisateur`, `id_categorie`, `photo`) VALUES
(7, 'Roborock S8 Pro Ultra ', 'La brosse du S8 Pro Ultra est automatiquement soulevée jusqu’à 6 mm2 en mode serpillière et lorsque l’aspirateur robot retourne sur la station d’accueil, ce qui permet d’éviter toute contamination croisée. Cette fonctionnalité permet également aux utilisateurs de nettoyer tous les liquides sans endommager le robot aspirateur.', 549.99, '2026-03-20 09:38:28', 9, 4, '69bd07849ffd9_Capture_d___cran_2026-03-20_093603.png'),
(8, 'Machine a laver BEKO 12kg', 'Machine a laver BEKO SMART INVERTER - Capacité de lavage : 12LKg - Vitesse d\'essorage : 1400 tr/mmin - Nombre de programmes : 15 programmes - Classe Énergétique : 10% Plus Efficace Que A+++ - Afficheur digitale - Steam cure avec Rafraîchissement - Programmes téléchargeables : Programme Mixte, Serviette, Programme Peluche Jouets, Programme de Rideaux - Niveau Sonore Lavage : 54 dBA - Niveau Sonore d\'essorage : 78 dBA - Consommation Annuelle d\'Energie : 252 kWh - Consommation d\'Eau Annuelle : 13045 Litres - Connectivité : Bluetooth - Poids : 84 Kg - Dimensions (HxLxP) : 84,5 x 60 x 63cm - Couleur : Grey -', 1699.99, '2026-03-20 09:46:50', 10, 4, '69bd097a6c243_Capture_d___cran_2026-03-20_094523.png'),
(9, 'Iphone 12 Pro Max', 'Grand écran OLED 6,7 pouces très net et lumineux\r\nPuissante puce A14 Bionic (rapide et fluide)\r\nCompatible 5G pour internet rapide\r\nTriple caméra + LiDAR pour photos/vidéos très avancées\r\nDesign premium (acier inox + verre)\r\nStockage : 128 Go à 512 Go', 299.99, '2026-03-20 09:53:04', 10, 2, '69bd0af08dfc0_Capture_d___cran_2026-03-20_094829.png'),
(10, 'Planche de pompe', ' Planche Pompe Musculation avec Poignées Antiglisse ', 12.99, '2026-03-20 09:57:43', 10, 7, '69bd0c07a1f83_Capture_d___cran_2026-03-20_095617.png'),
(11, 'Crampons F50', 'Paire de crampons adidas F50 en bon état.\r\n\r\nChaussures très légères et confortables, idéales pour les joueurs rapides. Bon maintien du pied et excellent toucher de balle. Semelles en bon état, crampons peu usés.\r\nUtilisées quelques fois seulement. Vendues car mon fils ne les utilise plus.\r\nPointure : 43.5\r\nRemise en main propre ', 64.99, '2026-03-20 10:12:56', 11, 7, '69bd0f987b865_Capture_d___cran_2026-03-20_100945.png'),
(12, 'Chemise à carreaux ', 'Chemise à carreaux en très bon état.\r\nStyle classique avec motif à carreaux rouge et bleu, facile à porter au quotidien ou pour une tenue habillée décontractée. Tissu agréable et confortable, bonne qualité.\r\nPeu portée, aucun défaut à signaler.\r\nTaille : L\r\nRemise en main propre', 7.99, '2026-03-20 10:17:32', 11, 6, '69bd10acb30f0_Capture_d___cran_2026-03-20_101606.png');

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `nom`, `description`) VALUES
(1, 'Informatique', 'Ordinateurs, portables, accessoires'),
(2, 'Téléphonie', 'Smartphones, accessoires'),
(3, 'Image & Son', 'Téléviseurs, enceintes, appareils photo'),
(4, 'Électroménager', 'Réfrigérateurs, lave-linge, fours'),
(5, 'Maison & Jardin', 'Meubles, décoration, outils'),
(6, 'Vêtements', 'Vêtements, chaussures, accessoires'),
(7, 'Sports & Loisirs', 'Vélos, équipements sportifs'),
(8, 'Autres', 'Tout ce qui ne rentre pas dans les autres catégories');

-- --------------------------------------------------------

--
-- Structure de la table `favoris`
--

CREATE TABLE `favoris` (
  `id` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_annonce` int(11) NOT NULL,
  `date_ajout` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `id_expediteur` int(11) NOT NULL,
  `id_destinataire` int(11) NOT NULL,
  `id_annonce` int(11) DEFAULT NULL,
  `contenu` text NOT NULL,
  `date_envoi` datetime DEFAULT current_timestamp(),
  `est_lu` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id`, `id_expediteur`, `id_destinataire`, `id_annonce`, `contenu`, `date_envoi`, `est_lu`) VALUES
(6, 8, 9, 7, 'Bonjour j\'aimerai acheter votre aspirateur ', '2026-03-20 09:40:12', 1),
(7, 13, 11, 11, 'Bonjour je suis intereser ', '2026-03-25 10:37:21', 1),
(8, 13, 11, 11, '???', '2026-03-25 10:41:19', 1);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `date_inscription` datetime DEFAULT current_timestamp(),
  `last_password_change` datetime DEFAULT current_timestamp(),
  `password_history` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `telephone`, `date_inscription`, `last_password_change`, `password_history`) VALUES
(8, 'Nadjar', 'Ethan Binhas Daoud', 'nadjar.ethan209@gmail.com', '$2y$10$ubGWndBVgVJ6x78u0oF7h.CXvmSlqfHfnsJvGd4e3IrnRvFb/suA2', '0769029907', '2026-03-20 09:13:27', '2026-05-14 12:10:01', NULL),
(9, 'Dupont', 'Thomas', 'ThomasDup@gmail.com', '$2y$10$uh3PatA/hrhySt3R5FMKAu0yHOoEwelY3VxWA6qMOJZ9gCWFliImq', '', '2026-03-20 09:34:31', '2026-05-14 12:10:01', NULL),
(10, 'Dubois', 'Pierre ', 'PierreDubois@gmail.com', '$2y$10$eW23r8wHbNVjAalG.Jv.ReUMC/1i7TCkg/lkqTtvhQuBjJ4mm..Du', '', '2026-03-20 09:42:17', '2026-05-14 12:10:01', NULL),
(11, 'Maurraux', 'Sarah', 'SarahMorraux@gmail.com', '$2y$10$P0l1KZF9u077K6gIDX.Fh.MZoMSgkenr5mGPP2zdWSU6Lv.43geOu', '', '2026-03-20 10:07:42', '2026-05-14 12:10:01', NULL),
(12, 'Test', 'Test', 'Test@gmail.com', '$2y$10$pkrHzLi52ipAkd5r9cU5S.3aebUVCebEoukYqpuCtMT1Tza.8aiyO', '', '2026-03-25 10:04:25', '2026-05-14 12:10:01', NULL),
(13, 'Test', 'Test', 'Test1@gmail.com', '$2y$10$136WgJEXuhRzJBsR25wvweeKTKSAvzDHUfXYc4BTr/eVkFbr9TFaW', '', '2026-03-25 10:07:04', '2026-05-14 12:10:01', NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `annonces`
--
ALTER TABLE `annonces`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_categorie` (`id_categorie`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom` (`nom`);

--
-- Index pour la table `favoris`
--
ALTER TABLE `favoris`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_utilisateur` (`id_utilisateur`,`id_annonce`),
  ADD KEY `id_annonce` (`id_annonce`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_expediteur` (`id_expediteur`),
  ADD KEY `id_destinataire` (`id_destinataire`),
  ADD KEY `id_annonce` (`id_annonce`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `annonces`
--
ALTER TABLE `annonces`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `favoris`
--
ALTER TABLE `favoris`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `annonces`
--
ALTER TABLE `annonces`
  ADD CONSTRAINT `annonces_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `annonces_ibfk_2` FOREIGN KEY (`id_categorie`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `favoris`
--
ALTER TABLE `favoris`
  ADD CONSTRAINT `favoris_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favoris_ibfk_2` FOREIGN KEY (`id_annonce`) REFERENCES `annonces` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`id_expediteur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`id_destinataire`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`id_annonce`) REFERENCES `annonces` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
