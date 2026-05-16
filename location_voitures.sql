-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 16 mai 2026 à 00:14
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `location_voitures`
--

-- --------------------------------------------------------

--
-- Structure de la table `clients`
--

CREATE TABLE `clients` (
  `ID` int(11) NOT NULL,
  `Nom` varchar(100) NOT NULL,
  `Adresse` text NOT NULL,
  `Numero_telephone` varchar(20) NOT NULL,
  `Email` varchar(150) NOT NULL,
  `Mot_de_passe` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `clients`
--

INSERT INTO `clients` (`ID`, `Nom`, `Adresse`, `Numero_telephone`, `Email`, `Mot_de_passe`, `created_at`) VALUES
(1, 'Anwer Elj', 'ras jebel,bizerte,tunisie', '50192517', 'anwarelj16@gmail.com', '$2y$10$vRPvdA0YcIuCUj9w/dt41u9EdCv8LbRrcPKtgroqKwHE4IiCNzWOy', '2026-05-07 22:28:35');

-- --------------------------------------------------------

--
-- Structure de la table `reservations`
--

CREATE TABLE `reservations` (
  `ID` int(11) NOT NULL,
  `Date_debut` date NOT NULL,
  `Date_fin` date NOT NULL,
  `Voiture_ID` int(11) NOT NULL,
  `Client_ID` int(11) NOT NULL,
  `Statut` enum('en_attente','confirmee','terminee','annulee') DEFAULT 'confirmee',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reservations`
--

INSERT INTO `reservations` (`ID`, `Date_debut`, `Date_fin`, `Voiture_ID`, `Client_ID`, `Statut`, `created_at`) VALUES
(1, '2026-05-10', '2026-05-21', 6, 1, 'terminee', '2026-05-07 22:28:59'),
(2, '2026-05-15', '2026-05-22', 7, 1, 'terminee', '2026-05-07 22:45:25'),
(3, '2026-05-10', '2026-05-29', 1, 1, 'terminee', '2026-05-08 23:22:16'),
(4, '2026-05-10', '2026-05-13', 7, 1, 'confirmee', '2026-05-09 00:01:06');

-- --------------------------------------------------------

--
-- Structure de la table `voitures`
--

CREATE TABLE `voitures` (
  `ID` int(11) NOT NULL,
  `Marque` varchar(50) NOT NULL,
  `Modele` varchar(50) NOT NULL,
  `Annee` year(4) NOT NULL,
  `Immatriculation` varchar(20) NOT NULL,
  `Disponibilite` tinyint(1) DEFAULT 1,
  `Prix_par_jour` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `Photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `voitures`
--

INSERT INTO `voitures` (`ID`, `Marque`, `Modele`, `Annee`, `Immatriculation`, `Disponibilite`, `Prix_par_jour`, `created_at`, `Photo`) VALUES
(1, 'Renault', 'Clio', '2021', '123-A-45', 1, 100.00, '2026-05-07 22:22:47', 'voiture_1778283593.webp'),
(2, 'Peugeot', '208', '2022', '456-B-78', 1, 400.00, '2026-05-07 22:22:47', 'voiture_1778284489.jpg'),
(3, 'Dacia', 'Sandero', '2020', '789-C-12', 1, 300.00, '2026-05-07 22:22:47', NULL),
(4, 'Toyota', 'Yaris', '2023', '321-D-65', 1, 450.00, '2026-05-07 22:22:47', NULL),
(5, 'Volkswagen', 'Polo', '2021', '654-E-98', 1, 420.00, '2026-05-07 22:22:47', NULL),
(6, 'Ford', 'Fiesta', '2020', '987-F-31', 1, 380.00, '2026-05-07 22:22:47', NULL),
(7, 'Hyundai', 'i20', '2022', '147-G-74', 0, 390.00, '2026-05-07 22:22:47', 'voiture_1778283552.jpg'),
(8, 'Kia', 'Picanto', '2023', '225', 1, 120.00, '2026-05-07 22:22:47', 'voiture_1778284977.jpg');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Index pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Voiture_ID` (`Voiture_ID`),
  ADD KEY `Client_ID` (`Client_ID`);

--
-- Index pour la table `voitures`
--
ALTER TABLE `voitures`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Immatriculation` (`Immatriculation`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `clients`
--
ALTER TABLE `clients`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `voitures`
--
ALTER TABLE `voitures`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`Voiture_ID`) REFERENCES `voitures` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`Client_ID`) REFERENCES `clients` (`ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
