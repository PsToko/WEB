-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Εξυπηρετητής: 127.0.0.1
-- Χρόνος δημιουργίας: 13 Νοε 2024 στις 23:00:22
-- Έκδοση διακομιστή: 10.4.28-MariaDB
-- Έκδοση PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Βάση δεδομένων: `thesismanagementsystem`
--

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `examination`
--

CREATE TABLE `examination` (
  `examinationID` int(11) NOT NULL,
  `thesisID` int(11) NOT NULL,
  `supervisorID` int(11) NOT NULL,
  `member1ID` int(11) NOT NULL,
  `member2ID` int(11) NOT NULL,
  `examinationDate` date NOT NULL,
  `examinationMethod` enum('online','in person') NOT NULL,
  `location` varchar(255) NOT NULL,
  `finalGrade` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Άδειασμα δεδομένων του πίνακα `examination`
--

INSERT INTO `examination` (`examinationID`, `thesisID`, `supervisorID`, `member1ID`, `member2ID`, `examinationDate`, `examinationMethod`, `location`, `finalGrade`) VALUES
(1, 3, 11, 12, 13, '2024-03-15', 'in person', 'Room 204, Science Building', 85.50),
(2, 4, 12, 10, 9, '2025-01-10', 'online', 'https://exam.example.com/ai-healthcare', NULL);

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `invitations`
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
-- Άδειασμα δεδομένων του πίνακα `invitations`
--

INSERT INTO `invitations` (`invitationID`, `thesisID`, `studentID`, `professorID`, `status`, `sentDate`, `responseDate`) VALUES
(1, 1, 1, 10, 'accepted', '2024-01-18', '2024-01-20'),
(2, 1, 1, 13, 'accepted', '2024-01-18', '2024-02-01'),
(3, 2, 2, 11, 'pending', '2024-01-22', NULL),
(4, 2, 2, 13, 'rejected', '2024-01-22', '2024-01-23'),
(5, 3, 3, 13, 'accepted', '2024-02-01', '2024-02-05'),
(6, 3, 3, 12, 'accepted', '2024-02-01', '2024-02-07'),
(7, 4, 5, 9, 'accepted', '2024-02-10', '2024-02-10'),
(8, 4, 5, 10, 'accepted', '2024-02-10', '2024-03-10');

--
-- Δείκτες `invitations`
--
DELIMITER $$
CREATE TRIGGER `assign_committee_member` AFTER UPDATE ON `invitations` FOR EACH ROW BEGIN
    -- Check if the invitation status was changed to 'accepted'
    IF NEW.status = 'accepted' THEN
        -- Check the Thesis table to assign the professor as member1 or member2
        IF (SELECT member1ID FROM Thesis WHERE thesisID = NEW.thesisID) IS NULL THEN
            -- Assign professor to member1ID if it is NULL
            UPDATE Thesis
            SET member1ID = NEW.professorID
            WHERE thesisID = NEW.thesisID;
        ELSEIF (SELECT member2ID FROM Thesis WHERE thesisID = NEW.thesisID) IS NULL THEN
            -- Assign professor to member2ID if member1ID is already filled and member2ID is NULL
            UPDATE Thesis
            SET member2ID = NEW.professorID
            WHERE thesisID = NEW.thesisID;
        END IF;

        -- After assigning, check if both member1ID and member2ID are filled
        IF (SELECT member1ID FROM Thesis WHERE thesisID = NEW.thesisID) IS NOT NULL
           AND (SELECT member2ID FROM Thesis WHERE thesisID = NEW.thesisID) IS NOT NULL THEN
            -- Delete all pending invitations for the same thesis as there are no more vacancies
            DELETE FROM Invitations
            WHERE thesisID = NEW.thesisID AND status = 'pending';
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `professors`
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
-- Άδειασμα δεδομένων του πίνακα `professors`
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
-- Δομή πίνακα για τον πίνακα `secretaries`
--

CREATE TABLE `secretaries` (
  `Secretary_ID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Surname` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Άδειασμα δεδομένων του πίνακα `secretaries`
--

INSERT INTO `secretaries` (`Secretary_ID`, `Name`, `Surname`, `email`, `mobile`) VALUES
(14, 'Ivy', 'White', 'ivy.white@example.com', '7788990011'),
(15, 'Jack', 'Clark', 'jack.clark@example.com', '8899001122'),
(16, 'Kara', 'Black', 'kara.black@example.com', '9900112233'),
(19, 'John', 'Martinez', 'john.martinez@example.com', '5559879876');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `students`
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
-- Άδειασμα δεδομένων του πίνακα `students`
--

INSERT INTO `students` (`Student_ID`, `AM`, `Name`, `Surname`, `Has_Thesis`, `Address`, `email`, `mobile`, `landline`) VALUES
(1, 12345678, 'Alice', 'Smith', 1, '123 University Ave', 'alice.smith@example.com', '1234567890', '1234005678'),
(2, 87654321, 'Bob', 'Johnson', 0, '456 College St', 'bob.johnson@example.com', '0987654321', '4321009876'),
(3, 23456789, 'Carol', 'Davis', 1, '789 Campus Dr', 'carol.davis@example.com', '1029384756', '1029034756'),
(4, 34567890, 'David', 'Miller', 0, '321 Dorm Rd', 'david.miller@example.com', '5647382910', '5647002910'),
(5, 45678901, 'Eve', 'Wilson', 1, '654 Lecture Ln', 'eve.wilson@example.com', '9081726354', '9081006354'),
(6, 56789012, 'Frank', 'Adams', 0, '1000 Lab St', 'frank.adams@example.com', '2233445566', '2233005566'),
(7, 67890123, 'Grace', 'Young', 0, '2000 Science Blvd', 'grace.young@example.com', '3344556677', '3344006677'),
(8, 78901234, 'Hank', 'Green', 0, '3000 Technology Way', 'hank.green@example.com', '4455667788', '4455007788'),
(17, 12345679, 'Mike', 'Taylor', 0, '123 College Ave', 'mike.taylor@example.com', '5551231234', '5550001111');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `thesis`
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
  `finalGrade` decimal(4,2) DEFAULT NULL,
  `postedDate` date NOT NULL,
  `assignmentDate` date DEFAULT NULL,
  `completionDate` date DEFAULT NULL,
  `examinationDate` date DEFAULT NULL,
  `withdrawalDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Άδειασμα δεδομένων του πίνακα `thesis`
--

INSERT INTO `thesis` (`thesisID`, `title`, `description`, `status`, `supervisorID`, `member1ID`, `member2ID`, `studentID`, `finalGrade`, `postedDate`, `assignmentDate`, `completionDate`, `examinationDate`, `withdrawalDate`) VALUES
(1, 'AI in Healthcare', 'Exploring AI applications in healthcare.', 'active', 9, 10, 13, 1, NULL, '2024-01-15', '2024-02-01', NULL, NULL, NULL),
(2, 'Quantum Computing', 'Study of quantum computing applications.', 'under assignment', 10, NULL, NULL, 2, NULL, '2024-01-20', NULL, NULL, NULL, NULL),
(3, 'Blockchain Security', 'Blockchain and cybersecurity integration.', 'finalized', 11, 12, 13, 3, 85.50, '2024-02-05', '2024-02-07', '2024-03-10', '2024-03-15', NULL),
(4, 'Data Privacy', 'Data privacy measures in technology.', 'under review', 12, 10, 9, 5, NULL, '2024-02-10', '2024-03-10', '2024-07-15', '2025-01-10', NULL),
(5, 'Sustainable Computing', 'Eco-friendly computing solutions.', 'withdrawn', 13, NULL, NULL, 4, NULL, '2024-02-20', '2024-03-01', NULL, NULL, '2024-04-01');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `thesiscomments`
--

CREATE TABLE `thesiscomments` (
  `commentID` int(11) NOT NULL,
  `thesisID` int(11) NOT NULL,
  `professorID` int(11) NOT NULL,
  `comment` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Άδειασμα δεδομένων του πίνακα `thesiscomments`
--

INSERT INTO `thesiscomments` (`commentID`, `thesisID`, `professorID`, `comment`) VALUES
(1, 1, 9, 'This research direction in AI is promising.'),
(2, 1, 10, 'Consider ethical implications in AI use.'),
(3, 2, 10, 'Quantum computing needs further exploration.'),
(4, 3, 11, 'Blockchain has strong potential in security.'),
(5, 3, 12, 'Cybersecurity aspect needs enhancement.');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `user`
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
-- Άδειασμα δεδομένων του πίνακα `user`
--

INSERT INTO `user` (`ID`, `Name`, `Surname`, `email`, `mobile`, `Username`, `Password`, `role`) VALUES
(1, 'Alice', 'Smith', 'alice.smith@example.com', '1234567890', 'alice_smith', 'password123', 'student'),
(2, 'Bob', 'Johnson', 'bob.johnson@example.com', '0987654321', 'bob_johnson', 'password456', 'student'),
(3, 'Carol', 'Davis', 'carol.davis@example.com', '1029384756', 'carol_davis', 'password789', 'student'),
(4, 'David', 'Miller', 'david.miller@example.com', '5647382910', 'david_miller', 'password321', 'student'),
(5, 'Eve', 'Wilson', 'eve.wilson@example.com', '9081726354', 'eve_wilson', 'password654', 'student'),
(6, 'Frank', 'Adams', 'frank.adams@example.com', '2233445566', 'frank_adams', 'password789', 'student'),
(7, 'Grace', 'Young', 'grace.young@example.com', '3344556677', 'grace_young', 'password321', 'student'),
(8, 'Hank', 'Green', 'hank.green@example.com', '4455667788', 'hank_green', 'password654', 'student'),
(9, 'Dr. Charles', 'Williams', 'charles.williams@example.com', '1122334455', 'charles_w', 'password789', 'professor'),
(10, 'Dr. Diana', 'Brown', 'diana.brown@example.com', '5566778899', 'diana_b', 'password101', 'professor'),
(11, 'Dr. Frank', 'Garcia', 'frank.garcia@example.com', '6677889911', 'frank_g', 'password202', 'professor'),
(12, 'Dr. Isabel', 'Martinez', 'isabel.martinez@example.com', '6677554433', 'isabel_m', 'password303', 'professor'),
(13, 'Dr. John', 'Taylor', 'john.taylor@example.com', '4433221100', 'john_taylor', 'password404', 'professor'),
(14, 'Ivy', 'White', 'ivy.white@example.com', '7788990011', 'ivy_white', 'password505', 'secretary'),
(15, 'Jack', 'Clark', 'jack.clark@example.com', '8899001122', 'jack_clark', 'password606', 'secretary'),
(16, 'Kara', 'Black', 'kara.black@example.com', '9900112233', 'kara_black', 'password707', 'secretary'),
(17, 'Mike', 'Taylor', 'mike.taylor@example.com', '5551231234', 'mike', '123', 'student'),
(18, 'Jim', 'Brown', 'jim.brown@example.com', '5553214321', 'jim', '456', 'professor'),
(19, 'John', 'Martinez', 'john.martinez@example.com', '5559879876', 'john', '789', 'secretary');

--
-- Ευρετήρια για άχρηστους πίνακες
--

--
-- Ευρετήρια για πίνακα `examination`
--
ALTER TABLE `examination`
  ADD PRIMARY KEY (`examinationID`),
  ADD KEY `thesisID` (`thesisID`),
  ADD KEY `supervisorID` (`supervisorID`),
  ADD KEY `member1ID` (`member1ID`),
  ADD KEY `member2ID` (`member2ID`);

--
-- Ευρετήρια για πίνακα `invitations`
--
ALTER TABLE `invitations`
  ADD PRIMARY KEY (`invitationID`),
  ADD KEY `thesisID` (`thesisID`),
  ADD KEY `studentID` (`studentID`),
  ADD KEY `professorID` (`professorID`);

--
-- Ευρετήρια για πίνακα `professors`
--
ALTER TABLE `professors`
  ADD PRIMARY KEY (`Professor_ID`);

--
-- Ευρετήρια για πίνακα `secretaries`
--
ALTER TABLE `secretaries`
  ADD PRIMARY KEY (`Secretary_ID`);

--
-- Ευρετήρια για πίνακα `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`Student_ID`),
  ADD UNIQUE KEY `AM` (`AM`);

--
-- Ευρετήρια για πίνακα `thesis`
--
ALTER TABLE `thesis`
  ADD PRIMARY KEY (`thesisID`),
  ADD KEY `supervisorID` (`supervisorID`),
  ADD KEY `member1ID` (`member1ID`),
  ADD KEY `member2ID` (`member2ID`),
  ADD KEY `studentID` (`studentID`);

--
-- Ευρετήρια για πίνακα `thesiscomments`
--
ALTER TABLE `thesiscomments`
  ADD PRIMARY KEY (`commentID`),
  ADD KEY `thesisID` (`thesisID`),
  ADD KEY `professorID` (`professorID`);

--
-- Ευρετήρια για πίνακα `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- AUTO_INCREMENT για άχρηστους πίνακες
--

--
-- AUTO_INCREMENT για πίνακα `examination`
--
ALTER TABLE `examination`
  MODIFY `examinationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT για πίνακα `invitations`
--
ALTER TABLE `invitations`
  MODIFY `invitationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT για πίνακα `thesis`
--
ALTER TABLE `thesis`
  MODIFY `thesisID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT για πίνακα `thesiscomments`
--
ALTER TABLE `thesiscomments`
  MODIFY `commentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT για πίνακα `user`
--
ALTER TABLE `user`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Περιορισμοί για άχρηστους πίνακες
--

--
-- Περιορισμοί για πίνακα `examination`
--
ALTER TABLE `examination`
  ADD CONSTRAINT `examination_ibfk_1` FOREIGN KEY (`thesisID`) REFERENCES `thesis` (`thesisID`),
  ADD CONSTRAINT `examination_ibfk_2` FOREIGN KEY (`supervisorID`) REFERENCES `professors` (`Professor_ID`),
  ADD CONSTRAINT `examination_ibfk_3` FOREIGN KEY (`member1ID`) REFERENCES `professors` (`Professor_ID`),
  ADD CONSTRAINT `examination_ibfk_4` FOREIGN KEY (`member2ID`) REFERENCES `professors` (`Professor_ID`);

--
-- Περιορισμοί για πίνακα `invitations`
--
ALTER TABLE `invitations`
  ADD CONSTRAINT `invitations_ibfk_1` FOREIGN KEY (`thesisID`) REFERENCES `thesis` (`thesisID`),
  ADD CONSTRAINT `invitations_ibfk_2` FOREIGN KEY (`studentID`) REFERENCES `students` (`Student_ID`),
  ADD CONSTRAINT `invitations_ibfk_3` FOREIGN KEY (`professorID`) REFERENCES `professors` (`Professor_ID`);

--
-- Περιορισμοί για πίνακα `professors`
--
ALTER TABLE `professors`
  ADD CONSTRAINT `professors_ibfk_1` FOREIGN KEY (`Professor_ID`) REFERENCES `user` (`ID`);

--
-- Περιορισμοί για πίνακα `secretaries`
--
ALTER TABLE `secretaries`
  ADD CONSTRAINT `secretaries_ibfk_1` FOREIGN KEY (`Secretary_ID`) REFERENCES `user` (`ID`);

--
-- Περιορισμοί για πίνακα `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`Student_ID`) REFERENCES `user` (`ID`);

--
-- Περιορισμοί για πίνακα `thesis`
--
ALTER TABLE `thesis`
  ADD CONSTRAINT `thesis_ibfk_1` FOREIGN KEY (`supervisorID`) REFERENCES `professors` (`Professor_ID`),
  ADD CONSTRAINT `thesis_ibfk_2` FOREIGN KEY (`member1ID`) REFERENCES `professors` (`Professor_ID`),
  ADD CONSTRAINT `thesis_ibfk_3` FOREIGN KEY (`member2ID`) REFERENCES `professors` (`Professor_ID`),
  ADD CONSTRAINT `thesis_ibfk_4` FOREIGN KEY (`studentID`) REFERENCES `students` (`Student_ID`);

--
-- Περιορισμοί για πίνακα `thesiscomments`
--
ALTER TABLE `thesiscomments`
  ADD CONSTRAINT `thesiscomments_ibfk_1` FOREIGN KEY (`thesisID`) REFERENCES `thesis` (`thesisID`),
  ADD CONSTRAINT `thesiscomments_ibfk_2` FOREIGN KEY (`professorID`) REFERENCES `professors` (`Professor_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
