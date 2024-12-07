-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 07, 2024 at 02:18 AM
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
-- Database: `thesismanagementsystem_new`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcementID` int(11) NOT NULL,
  `thesisID` int(11) NOT NULL,
  `createdBy` int(11) NOT NULL,
  `announcementText` text NOT NULL,
  `createdDate` datetime NOT NULL DEFAULT current_timestamp(),
  `examinationDate` datetime NOT NULL,
  `examinationMethod` enum('online','in person') NOT NULL,
  `location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcementID`, `thesisID`, `createdBy`, `announcementText`, `createdDate`, `examinationDate`, `examinationMethod`, `location`) VALUES
(11, 9, 18, 'The examination for the thesis titled \"geia\" will be held on 2024-12-04 00:00:00. The examination method is online, and it will take place at zoom link.', '2024-12-07 03:12:28', '2024-12-04 00:00:00', 'online', 'zoom link');

-- --------------------------------------------------------

--
-- Table structure for table `examination`
--

CREATE TABLE `examination` (
  `examinationID` int(11) NOT NULL,
  `thesisID` int(11) NOT NULL,
  `supervisorID` int(11) NOT NULL,
  `member1ID` int(11) NOT NULL,
  `member2ID` int(11) NOT NULL,
  `examinationDate` datetime DEFAULT NULL,
  `examinationMethod` enum('online','in person') DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `finalGrade` decimal(2,1) DEFAULT NULL,
  `st_thesis` varchar(50) DEFAULT NULL,
  `StudentID` int(11) DEFAULT NULL,
  `can_review` int(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `examination`
--

INSERT INTO `examination` (`examinationID`, `thesisID`, `supervisorID`, `member1ID`, `member2ID`, `examinationDate`, `examinationMethod`, `location`, `finalGrade`, `st_thesis`, `StudentID`, `can_review`) VALUES
(1, 3, 11, 12, 13, '2024-03-15 00:00:00', 'in person', 'Room 204, Science Building', 9.9, NULL, NULL, NULL),
(2, 4, 12, 10, 9, '2025-01-23 00:00:00', 'in person', 'CEID', 8.5, 'hpc_ex01.pdf', 5, 1),
(5, 9, 18, 9, 12, '2024-12-04 00:00:00', 'online', 'zoom link', NULL, 'business.pdf', 21, 1),
(6, 2, 10, 9, 18, '2024-12-05 00:00:00', 'online', 'zoom', NULL, NULL, 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `invitations`
--

CREATE TABLE `invitations` (
  `invitationID` int(11) NOT NULL,
  `thesisID` int(11) NOT NULL,
  `studentID` int(11) NOT NULL,
  `professorID` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `sentDate` date NOT NULL,
  `responseDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invitations`
--

INSERT INTO `invitations` (`invitationID`, `thesisID`, `studentID`, `professorID`, `status`, `sentDate`, `responseDate`) VALUES
(1, 1, 1, 10, 'accepted', '2024-01-18', '2024-01-20'),
(2, 1, 1, 13, 'accepted', '2024-01-18', '2024-02-01'),
(5, 3, 3, 13, 'accepted', '2024-02-01', '2024-02-05'),
(6, 3, 3, 12, 'accepted', '2024-02-01', '2024-02-07'),
(7, 4, 5, 9, 'accepted', '2024-02-10', '2024-02-10'),
(8, 4, 5, 10, 'accepted', '2024-02-10', '2024-03-10'),
(22, 9, 21, 9, 'accepted', '2024-11-23', '2024-11-23'),
(23, 9, 21, 11, 'accepted', '2024-11-23', '2024-11-25'),
(24, 19, 17, 9, 'accepted', '2024-11-25', '2024-11-25'),
(26, 19, 17, 11, 'accepted', '2024-11-25', '2024-11-25'),
(27, 2, 2, 9, 'accepted', '2024-11-25', '2024-11-25'),
(28, 2, 2, 18, 'accepted', '2024-11-25', '2024-11-25');

-- --------------------------------------------------------

--
-- Table structure for table `links`
--

CREATE TABLE `links` (
  `link_id` int(11) NOT NULL,
  `examinationID` int(11) NOT NULL,
  `StudentID` int(11) NOT NULL,
  `link` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `links`
--

INSERT INTO `links` (`link_id`, `examinationID`, `StudentID`, `link`) VALUES
(1, 2, 5, 'https://www.youtube.com/'),
(3, 2, 5, 'https://www.youtube.com/'),
(8, 2, 5, 'https://www.facebook.com/'),
(9, 2, 5, 'https://outlook.live.com/mail/0/inbox/'),
(11, 2, 5, 'https://www.facebook.com/'),
(12, 2, 5, 'https://outlook.live.com/mail/0/junkemail'),
(13, 2, 5, 'http://localhost/WEB_24-25/your_thesis.php'),
(15, 2, 5, 'https://www.hjhh.com/'),
(16, 2, 5, 'http://localhost/WEB7777_24-25/your_thesis.php'),
(17, 2, 5, 'https://www.000h.com/'),
(18, 2, 5, 'https://wppo.com/'),
(19, 5, 21, 'https://www.facebook.com/');

-- --------------------------------------------------------

--
-- Table structure for table `professors`
--

CREATE TABLE `professors` (
  `Professor_ID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Surname` varchar(50) NOT NULL,
  `Subject` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `professors`
--

INSERT INTO `professors` (`Professor_ID`, `Name`, `Surname`, `Subject`, `email`, `mobile`) VALUES
(9, 'Charles', 'Williams', 'Computer Science', 'charles.williams@example.com', '1122334455'),
(10, 'Diana', 'Brown', 'Mathematics', 'diana.brown@example.com', '5566778899'),
(11, 'Frank', 'Garcia', 'Physics', 'frank.garcia@example.com', '6677889911'),
(12, 'Isabel', 'Martinez', 'Biology', 'isabel.martinez@example.com', '6677554433'),
(13, 'John', 'Taylor', 'Chemistry', 'john.taylor@example.com', '4433221100'),
(18, 'Jim', 'Brown', 'Computer Engineering', 'jim.brown@example.com', '5553214321');

-- --------------------------------------------------------

--
-- Table structure for table `secretaries`
--

CREATE TABLE `secretaries` (
  `Secretary_ID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Surname` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `secretaries`
--

INSERT INTO `secretaries` (`Secretary_ID`, `Name`, `Surname`, `email`, `mobile`) VALUES
(14, 'Ivy', 'White', 'ivy.white@example.com', '7788990011'),
(15, 'Jack', 'Clark', 'jack.clark@example.com', '8899001122'),
(16, 'Kara', 'Black', 'kara.black@example.com', '9900112233'),
(19, 'John', 'Martinez', 'john.martinez@example.com', '5559879876');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `Student_ID` int(11) NOT NULL,
  `AM` int(8) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Surname` varchar(50) NOT NULL,
  `Has_Thesis` tinyint(1) DEFAULT 0,
  `Address` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `landline` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`Student_ID`, `AM`, `Name`, `Surname`, `Has_Thesis`, `Address`, `email`, `mobile`, `landline`) VALUES
(1, 12345678, 'Alice', 'Smith', 1, '123 University Ave', 'alice.smith@example.com', '1234567890', '1234005678'),
(2, 87654321, 'Bob', 'Johnson', 1, '456 College St', 'bob.johnson@example.com', '0987654321', '4321009876'),
(3, 23456789, 'Carol', 'Davis', 1, '789 Campus Dr', 'carol.davis@example.com', '1029384756', '1029034756'),
(4, 34567890, 'David', 'Miller', 0, '321 Dorm Rd', 'david.miller@example.com', '5647382910', '5647002910'),
(5, 45678901, 'Eve', 'Wilson', 1, '655 Lecture Ln', 'eve.wilson@example.com', '9081726354', '9081006354'),
(6, 56789012, 'Frank', 'Adams', 0, '1000 Lab St', 'frank.adams@example.com', '2233445566', '2233005566'),
(7, 67890123, 'Grace', 'Young', 1, '2000 Science Blvd', 'grace.young@example.com', '3344556677', '3344006677'),
(8, 78901234, 'Hank', 'Green', 0, '3000 Technology Way', 'hank.green@example.com', '4455667788', '4455007788'),
(17, 12345679, 'Mike', 'Taylor', 1, '123 College Ave', 'mike.taylor@example.com', '5551231234', '5550001111'),
(21, 1074459, 'Giannis', 'Ioannou', 1, 'Venizelou', 'john@upatras.gr', '6945093821', '5');

-- --------------------------------------------------------

--
-- Table structure for table `thesis`
--

CREATE TABLE `thesis` (
  `thesisID` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('under assignment','active','under review','finalized','withdrawn') NOT NULL,
  `supervisorID` int(11) NOT NULL,
  `member1ID` int(11) DEFAULT NULL,
  `member2ID` int(11) DEFAULT NULL,
  `studentID` int(11) DEFAULT NULL,
  `finalGrade` decimal(2,1) DEFAULT NULL,
  `postedDate` date NOT NULL,
  `assignmentDate` date DEFAULT NULL,
  `completionDate` date DEFAULT NULL,
  `examinationDate` datetime DEFAULT NULL,
  `withdrawalDate` date DEFAULT NULL,
  `pdf` varchar(50) DEFAULT NULL,
  `withdrawn_comment` enum('from professor','from secretary') DEFAULT NULL,
  `general_assembly` varchar(20) DEFAULT NULL,
  `member1Grade` double(2,1) DEFAULT NULL,
  `member2Grade` double(2,1) DEFAULT NULL,
  `nemertes` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `thesis`
--

INSERT INTO `thesis` (`thesisID`, `title`, `description`, `status`, `supervisorID`, `member1ID`, `member2ID`, `studentID`, `finalGrade`, `postedDate`, `assignmentDate`, `completionDate`, `examinationDate`, `withdrawalDate`, `pdf`, `withdrawn_comment`, `general_assembly`, `member1Grade`, `member2Grade`, `nemertes`) VALUES
(1, 'AI in Healthcare', 'Exploring AI applications in healthcare.', 'active', 9, 18, 13, 1, NULL, '2024-01-15', '2024-02-01', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL),
(2, 'Quantum Computing', 'Study of quantum computing applications.', 'under review', 10, 9, 18, 2, NULL, '2024-01-20', '2024-11-25', NULL, '2024-12-05 00:00:00', NULL, '', NULL, '3/2023', NULL, NULL, NULL),
(3, 'Blockchain Security', 'Blockchain and cybersecurity integration.', 'finalized', 11, 12, 13, 3, 8.5, '2024-02-05', '2024-02-07', '2024-03-10', '2024-03-15 00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL),
(4, 'Data Privacy', 'Data privacy measures in technology.', 'under review', 12, 10, 9, 5, 7.2, '2024-02-10', '2024-03-10', '2024-07-15', '2025-01-23 00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL),
(5, 'Sustainable Computing', 'Eco-friendly computing solutions.', 'withdrawn', 18, NULL, NULL, 4, NULL, '2024-02-20', '2024-03-01', NULL, NULL, '2024-04-01', '', NULL, NULL, NULL, NULL, NULL),
(7, 'test', 'test', 'withdrawn', 18, 9, 13, 17, NULL, '2022-11-14', '2022-11-15', NULL, NULL, '2024-11-24', '', 'from professor', '6/2024', NULL, NULL, NULL),
(9, 'geia', 'geia', 'under review', 18, 9, 12, 21, 7.8, '2024-11-14', '2024-11-25', NULL, '2024-12-04 00:00:00', NULL, '', NULL, '10/2024', 8.0, 6.2, 'https://nemertes.library.upatras.gr/home'),
(10, 'p', 'p', 'under assignment', 18, NULL, NULL, NULL, NULL, '2024-11-14', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL),
(11, 'hmm', 'ooooooooooooooo', 'under assignment', 18, NULL, NULL, NULL, NULL, '2024-11-14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'woah', 'woah', 'under assignment', 18, NULL, NULL, NULL, NULL, '2024-11-14', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL),
(14, 'θι', 'ι', 'under assignment', 18, NULL, NULL, NULL, NULL, '2024-11-14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'γ', 'γ', 'under assignment', 18, NULL, NULL, NULL, NULL, '2024-11-14', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL),
(16, 'o', 'o', 'under assignment', 18, NULL, NULL, 7, NULL, '2024-11-14', NULL, NULL, NULL, NULL, 'psthognks.pdf', NULL, NULL, NULL, NULL, NULL),
(17, 'elll', 'oxi', 'under assignment', 18, NULL, NULL, NULL, NULL, '2024-11-14', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL),
(18, 'data science', 'try to do data mining', 'under assignment', 9, NULL, NULL, NULL, NULL, '2024-11-18', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL),
(19, '123', '123', 'under review', 18, 9, 11, 17, NULL, '2024-11-19', '2024-11-25', NULL, NULL, NULL, 'Ergastiriaki_Askisi_24-25-1.0.pdf', NULL, NULL, NULL, NULL, NULL),
(20, 'Machine Learning', 'Create a program', 'under assignment', 11, NULL, NULL, NULL, NULL, '2024-11-25', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `thesiscomments`
--

CREATE TABLE `thesiscomments` (
  `commentID` int(11) NOT NULL,
  `thesisID` int(11) NOT NULL,
  `professorID` int(11) NOT NULL,
  `comment` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `thesiscomments`
--

INSERT INTO `thesiscomments` (`commentID`, `thesisID`, `professorID`, `comment`) VALUES
(1, 1, 9, 'This research direction in AI is promising.'),
(2, 1, 10, 'Consider ethical implications in AI use.'),
(3, 2, 10, 'Quantum computing needs further exploration.'),
(4, 3, 11, 'Blockchain has strong potential in security.'),
(5, 3, 12, 'Cybersecurity aspect needs enhancement.'),
(6, 1, 9, 'pop'),
(16, 1, 9, 'o'),
(17, 1, 9, 'i'),
(20, 1, 9, 'o'),
(23, 1, 9, 'υ'),
(26, 18, 9, 'ιο'),
(27, 1, 18, '6'),
(28, 1, 18, 'oi po iu'),
(29, 1, 18, 'test'),
(30, 1, 18, 'test'),
(31, 1, 18, '123'),
(32, 7, 18, 'test'),
(33, 7, 18, 'te'),
(34, 1, 18, 'w'),
(35, 9, 18, 'mustard');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `ID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Surname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `role` enum('student','professor','secretary') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`ID`, `Name`, `Surname`, `email`, `mobile`, `Username`, `Password`, `role`) VALUES
(1, 'Alice', 'Smith', 'alice.smith@example.com', '1234567890', 'alice_smith', 'password123', 'student'),
(2, 'Bob', 'Johnson', 'bob.johnson@example.com', '0987654321', 'bob', 'bob', 'student'),
(3, 'Carol', 'Davis', 'carol.davis@example.com', '1029384756', 'carol_davis', 'password789', 'student'),
(4, 'David', 'Miller', 'david.miller@example.com', '5647382910', 'david_miller', 'password321', 'student'),
(5, 'Eve', 'Wilson', 'eve.wilson@example.com', '9081726354', 'eve', 'eve', 'student'),
(6, 'Frank', 'Adams', 'frank.adams@example.com', '2233445566', 'frank1', 'frank', 'student'),
(7, 'Grace', 'Young', 'grace.young@example.com', '3344556677', 'grace_young', 'password321', 'student'),
(8, 'Hank', 'Green', 'hank.green@example.com', '4455667788', 'hank_green', 'password654', 'student'),
(9, 'Dr. Charles', 'Williams', 'charles.williams@example.com', '1122334455', 'charles_w', 'charles', 'professor'),
(10, 'Dr. Diana', 'Brown', 'diana.brown@example.com', '5566778899', 'diana_b', 'password101', 'professor'),
(11, 'Dr. Frank', 'Garcia', 'frank.garcia@example.com', '6677889911', 'frank_g', 'frank', 'professor'),
(12, 'Dr. Isabel', 'Martinez', 'isabel.martinez@example.com', '6677554433', 'isabel', '303', 'professor'),
(13, 'Dr. John', 'Taylor', 'john.taylor@example.com', '4433221100', 'john_taylor', 'password404', 'professor'),
(14, 'Ivy', 'White', 'ivy.white@example.com', '7788990011', 'ivy_white', 'password505', 'secretary'),
(15, 'Jack', 'Clark', 'jack.clark@example.com', '8899001122', 'jack_clark', 'password606', 'secretary'),
(16, 'Kara', 'Black', 'kara.black@example.com', '9900112233', 'kara_black', 'password707', 'secretary'),
(17, 'Mike', 'Taylor', 'mike.taylor@example.com', '5551231234', 'mike', '123', 'student'),
(18, 'Jim', 'Brown', 'jim.brown@example.com', '5553214321', 'jim', '456', 'professor'),
(19, 'John', 'Martinez', 'john.martinez@example.com', '5559879876', 'john', '789', 'secretary'),
(21, 'Giannis', 'Ioannou', 'john@upatras.gr', '6945093821', 'giannis', 'john', 'student');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcementID`),
  ADD KEY `thesisID` (`thesisID`),
  ADD KEY `createdBy` (`createdBy`);

--
-- Indexes for table `examination`
--
ALTER TABLE `examination`
  ADD PRIMARY KEY (`examinationID`),
  ADD KEY `thesisID` (`thesisID`),
  ADD KEY `supervisorID` (`supervisorID`),
  ADD KEY `member1ID` (`member1ID`),
  ADD KEY `member2ID` (`member2ID`);

--
-- Indexes for table `invitations`
--
ALTER TABLE `invitations`
  ADD PRIMARY KEY (`invitationID`),
  ADD KEY `thesisID` (`thesisID`),
  ADD KEY `studentID` (`studentID`),
  ADD KEY `professorID` (`professorID`);

--
-- Indexes for table `links`
--
ALTER TABLE `links`
  ADD PRIMARY KEY (`link_id`);

--
-- Indexes for table `professors`
--
ALTER TABLE `professors`
  ADD PRIMARY KEY (`Professor_ID`);

--
-- Indexes for table `secretaries`
--
ALTER TABLE `secretaries`
  ADD PRIMARY KEY (`Secretary_ID`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`Student_ID`),
  ADD UNIQUE KEY `AM` (`AM`);

--
-- Indexes for table `thesis`
--
ALTER TABLE `thesis`
  ADD PRIMARY KEY (`thesisID`),
  ADD KEY `supervisorID` (`supervisorID`),
  ADD KEY `member1ID` (`member1ID`),
  ADD KEY `member2ID` (`member2ID`),
  ADD KEY `studentID` (`studentID`);

--
-- Indexes for table `thesiscomments`
--
ALTER TABLE `thesiscomments`
  ADD PRIMARY KEY (`commentID`),
  ADD KEY `thesisID` (`thesisID`),
  ADD KEY `professorID` (`professorID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcementID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `examination`
--
ALTER TABLE `examination`
  MODIFY `examinationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `invitations`
--
ALTER TABLE `invitations`
  MODIFY `invitationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `links`
--
ALTER TABLE `links`
  MODIFY `link_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `thesis`
--
ALTER TABLE `thesis`
  MODIFY `thesisID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `thesiscomments`
--
ALTER TABLE `thesiscomments`
  MODIFY `commentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`thesisID`) REFERENCES `thesis` (`thesisID`),
  ADD CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`createdBy`) REFERENCES `professors` (`Professor_ID`);

--
-- Constraints for table `examination`
--
ALTER TABLE `examination`
  ADD CONSTRAINT `examination_ibfk_1` FOREIGN KEY (`thesisID`) REFERENCES `thesis` (`thesisID`),
  ADD CONSTRAINT `examination_ibfk_2` FOREIGN KEY (`supervisorID`) REFERENCES `professors` (`Professor_ID`),
  ADD CONSTRAINT `examination_ibfk_3` FOREIGN KEY (`member1ID`) REFERENCES `professors` (`Professor_ID`),
  ADD CONSTRAINT `examination_ibfk_4` FOREIGN KEY (`member2ID`) REFERENCES `professors` (`Professor_ID`);

--
-- Constraints for table `invitations`
--
ALTER TABLE `invitations`
  ADD CONSTRAINT `invitations_ibfk_1` FOREIGN KEY (`thesisID`) REFERENCES `thesis` (`thesisID`),
  ADD CONSTRAINT `invitations_ibfk_2` FOREIGN KEY (`studentID`) REFERENCES `students` (`Student_ID`),
  ADD CONSTRAINT `invitations_ibfk_3` FOREIGN KEY (`professorID`) REFERENCES `professors` (`Professor_ID`);

--
-- Constraints for table `professors`
--
ALTER TABLE `professors`
  ADD CONSTRAINT `professors_ibfk_1` FOREIGN KEY (`Professor_ID`) REFERENCES `user` (`ID`);

--
-- Constraints for table `secretaries`
--
ALTER TABLE `secretaries`
  ADD CONSTRAINT `secretaries_ibfk_1` FOREIGN KEY (`Secretary_ID`) REFERENCES `user` (`ID`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`Student_ID`) REFERENCES `user` (`ID`);

--
-- Constraints for table `thesis`
--
ALTER TABLE `thesis`
  ADD CONSTRAINT `thesis_ibfk_1` FOREIGN KEY (`supervisorID`) REFERENCES `professors` (`Professor_ID`),
  ADD CONSTRAINT `thesis_ibfk_2` FOREIGN KEY (`member1ID`) REFERENCES `professors` (`Professor_ID`),
  ADD CONSTRAINT `thesis_ibfk_3` FOREIGN KEY (`member2ID`) REFERENCES `professors` (`Professor_ID`),
  ADD CONSTRAINT `thesis_ibfk_4` FOREIGN KEY (`studentID`) REFERENCES `students` (`Student_ID`);

--
-- Constraints for table `thesiscomments`
--
ALTER TABLE `thesiscomments`
  ADD CONSTRAINT `thesiscomments_ibfk_1` FOREIGN KEY (`thesisID`) REFERENCES `thesis` (`thesisID`),
  ADD CONSTRAINT `thesiscomments_ibfk_2` FOREIGN KEY (`professorID`) REFERENCES `professors` (`Professor_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
