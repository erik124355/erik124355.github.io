-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 25.09.2025 klo 13:00
-- Palvelimen versio: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `moduuli_5_tehtava_2`
--

-- --------------------------------------------------------

--
-- Rakenne taululle `elokuva`
--

CREATE TABLE `elokuva` (
  `ElokuvaID` int(11) NOT NULL,
  `Nimi` varchar(100) NOT NULL,
  `Julkaisuvuosi` int(11) NOT NULL,
  `Vuokrahinta` decimal(10,2) NOT NULL,
  `Arvio` int(11) DEFAULT NULL,
  `LajityyppiID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `elokuva`
--

INSERT INTO `elokuva` (`ElokuvaID`, `Nimi`, `Julkaisuvuosi`, `Vuokrahinta`, `Arvio`, `LajityyppiID`) VALUES
(1, 'What Women Want', 2001, 3.00, 5, 4),
(2, 'Chocolat', 1999, 3.00, 5, 4),
(3, 'Enemy at the Gates', 2001, 3.00, 7, 2),
(4, 'Almost Famous', 2000, 3.00, 8, 4);

-- --------------------------------------------------------

--
-- Rakenne taululle `jasen`
--

CREATE TABLE `jasen` (
  `JasenID` int(11) NOT NULL,
  `Nimi` varchar(100) NOT NULL,
  `Osoite` varchar(150) NOT NULL,
  `Liittymispaivamaara` date NOT NULL,
  `Syntymavuosi` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `jasen`
--

INSERT INTO `jasen` (`JasenID`, `Nimi`, `Osoite`, `Liittymispaivamaara`, `Syntymavuosi`) VALUES
(1, 'Erkki Piktänen', 'Kajantie 2', '1990-09-09', 1968),
(2, 'Tommi Lahti', 'Nörttikuja 3', '1991-01-01', 1975),
(3, 'Marita Lahti', 'Nörttikuja 3', '1991-01-01', 1970),
(4, 'Pekka Tapoi', 'Soittajankatu 4', '1995-11-09', 1980);

-- --------------------------------------------------------

--
-- Rakenne taululle `lajityyppi`
--

CREATE TABLE `lajityyppi` (
  `LajityyppiID` int(11) NOT NULL,
  `Tyyppinimi` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `lajityyppi`
--

INSERT INTO `lajityyppi` (`LajityyppiID`, `Tyyppinimi`) VALUES
(1, 'Kauhu'),
(2, 'Toiminta'),
(3, 'Romantiikka'),
(4, 'Komedia'),
(5, 'Draama');

-- --------------------------------------------------------

--
-- Rakenne taululle `vuokraus`
--

CREATE TABLE `vuokraus` (
  `VuokraID` int(11) NOT NULL,
  `JasenID` int(11) NOT NULL,
  `ElokuvaID` int(11) NOT NULL,
  `PalautusPVM` date NOT NULL,
  `VuokraPVM` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `elokuva`
--
ALTER TABLE `elokuva`
  ADD PRIMARY KEY (`ElokuvaID`),
  ADD KEY `LajityyppiID` (`LajityyppiID`);

--
-- Indexes for table `jasen`
--
ALTER TABLE `jasen`
  ADD PRIMARY KEY (`JasenID`);

--
-- Indexes for table `lajityyppi`
--
ALTER TABLE `lajityyppi`
  ADD PRIMARY KEY (`LajityyppiID`);

--
-- Indexes for table `vuokraus`
--
ALTER TABLE `vuokraus`
  ADD PRIMARY KEY (`VuokraID`),
  ADD KEY `JasenID` (`JasenID`),
  ADD KEY `ElokuvaID` (`ElokuvaID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `elokuva`
--
ALTER TABLE `elokuva`
  MODIFY `ElokuvaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jasen`
--
ALTER TABLE `jasen`
  MODIFY `JasenID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lajityyppi`
--
ALTER TABLE `lajityyppi`
  MODIFY `LajityyppiID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `vuokraus`
--
ALTER TABLE `vuokraus`
  MODIFY `VuokraID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Rajoitteet vedostauluille
--

--
-- Rajoitteet taululle `elokuva`
--
ALTER TABLE `elokuva`
  ADD CONSTRAINT `elokuva_ibfk_1` FOREIGN KEY (`LajityyppiID`) REFERENCES `lajityyppi` (`LajityyppiID`);

--
-- Rajoitteet taululle `vuokraus`
--
ALTER TABLE `vuokraus`
  ADD CONSTRAINT `vuokraus_ibfk_1` FOREIGN KEY (`JasenID`) REFERENCES `jasen` (`JasenID`),
  ADD CONSTRAINT `vuokraus_ibfk_2` FOREIGN KEY (`ElokuvaID`) REFERENCES `elokuva` (`ElokuvaID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
