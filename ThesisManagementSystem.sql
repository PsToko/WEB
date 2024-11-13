-- Create the database
CREATE DATABASE IF NOT EXISTS ThesisManagementSystem;

-- Use the newly created database
USE ThesisManagementSystem;

-- Drop and Create User table
DROP TABLE IF EXISTS User;
-- User table: Stores basic information for all users (students, professors, secretaries)
CREATE TABLE User (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(50) NOT NULL,
    Surname VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mobile VARCHAR(15),
    Username VARCHAR(50) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    role ENUM('student', 'professor', 'secretary') NOT NULL
);

-- Drop and Create Students table
DROP TABLE IF EXISTS Students;
-- Students table: Contains specific details for users with a student role
CREATE TABLE Students (
    Student_ID INT PRIMARY KEY,
    AM INT(8) UNIQUE NOT NULL,
    Name VARCHAR(50) NOT NULL,
    Surname VARCHAR(50) NOT NULL,
    Has_Thesis BOOLEAN DEFAULT FALSE,
    Address VARCHAR(255),
    email VARCHAR(100),
    mobile VARCHAR(15),
    landline VARCHAR(15),
    FOREIGN KEY (Student_ID) REFERENCES User(ID)
);

-- Drop and Create Professors table
DROP TABLE IF EXISTS Professors;
-- Professors table: Contains specific details for users with a professor role
CREATE TABLE Professors (
    Professor_ID INT PRIMARY KEY,
    Name VARCHAR(50) NOT NULL,
    Surname VARCHAR(50) NOT NULL,
    Subject VARCHAR(100),
    email VARCHAR(100),
    mobile VARCHAR(15),
    FOREIGN KEY (Professor_ID) REFERENCES User(ID)
    );

-- Drop and Create Secretaries table
DROP TABLE IF EXISTS Secretaries;
-- Secretaries table: Contains specific details for users with a secretary role
CREATE TABLE Secretaries (
    Secretary_ID INT PRIMARY KEY,
    Name VARCHAR(50) NOT NULL,
    Surname VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    mobile VARCHAR(15),
    FOREIGN KEY (Secretary_ID) REFERENCES User(ID)
    );

-- Drop and Create Thesis table
DROP TABLE IF EXISTS Thesis;
-- Thesis table: Stores information on each thesis, including committee members and status
CREATE TABLE Thesis (
    thesisID INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('under assignment', 'active', 'under review', 'finalized', 'withdrawn') NOT NULL,
    supervisorID INT NOT NULL,
    member1ID INT,
    member2ID INT,
    studentID INT,
    finalGrade DECIMAL(4,2),
    postedDate DATE NOT NULL,
    assignmentDate DATE,
    completionDate DATE,
    examinationDate DATE,
    withdrawalDate DATE,
    FOREIGN KEY (supervisorID) REFERENCES Professors(Professor_ID),
    FOREIGN KEY (member1ID) REFERENCES Professors(Professor_ID),
    FOREIGN KEY (member2ID) REFERENCES Professors(Professor_ID),
    FOREIGN KEY (studentID) REFERENCES Students(Student_ID)
);

-- Drop and Create ThesisComments table
DROP TABLE IF EXISTS ThesisComments;
-- ThesisComments table: Stores comments by professors on theses
CREATE TABLE ThesisComments (
    commentID INT AUTO_INCREMENT PRIMARY KEY,
    thesisID INT NOT NULL,
    professorID INT NOT NULL,
    comment VARCHAR(300) NOT NULL,
    FOREIGN KEY (thesisID) REFERENCES Thesis(thesis_id),
    FOREIGN KEY (professorID) REFERENCES Professors(Professor_ID)
);

-- Drop and Create Invitations table
DROP TABLE IF EXISTS Invitations;
-- Invitations table: Stores invitations sent to professors by students for committee membership
CREATE TABLE Invitations (
    invitationID INT AUTO_INCREMENT PRIMARY KEY,
    thesisID INT NOT NULL,
    studentID INT NOT NULL,
    professorID INT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
    sentDate DATE NOT NULL,
    responseDate DATE,
    FOREIGN KEY (thesisID) REFERENCES Thesis(thesisID),
    FOREIGN KEY (studentID) REFERENCES Students(Student_ID),
    FOREIGN KEY (professorID) REFERENCES Professors(Professor_ID)
);

-- Drop and Create Examination table
DROP TABLE IF EXISTS Examination;
-- Examination table: Records thesis examination details including date, method, and location
CREATE TABLE Examination (
    examinationID INT AUTO_INCREMENT PRIMARY KEY,
    thesisID INT NOT NULL,
    supervisorID INT NOT NULL,
    member1ID INT NOT NULL,
    member2ID INT NOT NULL,
    examinationDate DATE NOT NULL,
    examinationMethod ENUM('online', 'in person') NOT NULL,
    location VARCHAR(255) NOT NULL,
    finalGrade DECIMAL(4,2),
    FOREIGN KEY (thesisID) REFERENCES Thesis(thesisID),
    FOREIGN KEY (supervisorID) REFERENCES Professors(Professor_ID),
    FOREIGN KEY (member1ID) REFERENCES Professors(Professor_ID),
    FOREIGN KEY (member2ID) REFERENCES Professors(Professor_ID)
);


########################################################################################################################################################


-- Insert sample data into User table
INSERT INTO User (Name, Surname, email, mobile, Username, Password, role)
VALUES 
    ('Alice', 'Smith', 'alice.smith@example.com', '1234567890', 'alice_smith', 'password123', 'student'),
    ('Bob', 'Johnson', 'bob.johnson@example.com', '0987654321', 'bob_johnson', 'password456', 'student'),
    ('Carol', 'Davis', 'carol.davis@example.com', '1029384756', 'carol_davis', 'password789', 'student'),
    ('David', 'Miller', 'david.miller@example.com', '5647382910', 'david_miller', 'password321', 'student'),
    ('Eve', 'Wilson', 'eve.wilson@example.com', '9081726354', 'eve_wilson', 'password654', 'student'),
    ('Frank', 'Adams', 'frank.adams@example.com', '2233445566', 'frank_adams', 'password789', 'student'),
    ('Grace', 'Young', 'grace.young@example.com', '3344556677', 'grace_young', 'password321', 'student'),
    ('Hank', 'Green', 'hank.green@example.com', '4455667788', 'hank_green', 'password654', 'student'),
    ('Dr. Charles', 'Williams', 'charles.williams@example.com', '1122334455', 'charles_w', 'password789', 'professor'),
    ('Dr. Diana', 'Brown', 'diana.brown@example.com', '5566778899', 'diana_b', 'password101', 'professor'),
    ('Dr. Frank', 'Garcia', 'frank.garcia@example.com', '6677889911', 'frank_g', 'password202', 'professor'),
    ('Dr. Isabel', 'Martinez', 'isabel.martinez@example.com', '6677554433', 'isabel_m', 'password303', 'professor'),
    ('Dr. John', 'Taylor', 'john.taylor@example.com', '4433221100', 'john_taylor', 'password404', 'professor'),
    ('Ivy', 'White', 'ivy.white@example.com', '7788990011', 'ivy_white', 'password505', 'secretary'),
    ('Jack', 'Clark', 'jack.clark@example.com', '8899001122', 'jack_clark', 'password606', 'secretary'),
    ('Kara', 'Black', 'kara.black@example.com', '9900112233', 'kara_black', 'password707', 'secretary');

-- Insert sample data into Students table
INSERT INTO Students (Student_ID, AM, Name, Surname, Has_Thesis, Address, email, mobile, landline)
VALUES
    (1, 12345678, 'Alice', 'Smith', TRUE, '123 University Ave', 'alice.smith@example.com', '1234567890', '1234005678'),
    (2, 87654321, 'Bob', 'Johnson', FALSE, '456 College St', 'bob.johnson@example.com', '0987654321', '4321009876'),
    (3, 23456789, 'Carol', 'Davis', TRUE, '789 Campus Dr', 'carol.davis@example.com', '1029384756', '1029034756'),
    (4, 34567890, 'David', 'Miller', FALSE, '321 Dorm Rd', 'david.miller@example.com', '5647382910', '5647002910'),
    (5, 45678901, 'Eve', 'Wilson', TRUE, '654 Lecture Ln', 'eve.wilson@example.com', '9081726354', '9081006354'),
    (6, 56789012, 'Frank', 'Adams', FALSE, '1000 Lab St', 'frank.adams@example.com', '2233445566', '2233005566'),
    (7, 67890123, 'Grace', 'Young', FALSE, '2000 Science Blvd', 'grace.young@example.com', '3344556677', '3344006677'),
    (8, 78901234, 'Hank', 'Green', FALSE, '3000 Technology Way', 'hank.green@example.com', '4455667788', '4455007788');

-- Insert sample data into Professors table
INSERT INTO Professors (Professor_ID, Name, Surname, Subject, email, mobile)
VALUES
    (9, 'Charles', 'Williams', 'Computer Science', 'charles.williams@example.com', '1122334455'),
    (10, 'Diana', 'Brown', 'Mathematics', 'diana.brown@example.com', '5566778899'),
    (11, 'Frank', 'Garcia', 'Physics', 'frank.garcia@example.com', '6677889911'),
    (12, 'Isabel', 'Martinez', 'Biology', 'isabel.martinez@example.com', '6677554433'),
    (13, 'John', 'Taylor', 'Chemistry', 'john.taylor@example.com', '4433221100');

-- Insert sample data into Secretaries table
INSERT INTO Secretaries (Secretary_ID, Name, Surname, email, mobile)
VALUES
    (14, 'Ivy', 'White', 'ivy.white@example.com', '7788990011'),
    (15, 'Jack', 'Clark', 'jack.clark@example.com', '8899001122'),
    (16, 'Kara', 'Black', 'kara.black@example.com', '9900112233');

-- Easy Username and Password

-- Insert additional sample data into User table
INSERT INTO User (Name, Surname, email, mobile, Username, Password, role)
VALUES 
    ('Mike', 'Taylor', 'mike.taylor@example.com', '5551231234', 'mike', '123', 'student'),
    ('Jim', 'Brown', 'jim.brown@example.com', '5553214321', 'jim', '456', 'professor'),
    ('John', 'Martinez', 'john.martinez@example.com', '5559879876', 'john', '789', 'secretary');

-- Insert into Students table for user 'Mike Taylor'
INSERT INTO Students (Student_ID, AM, Name, Surname, Has_Thesis, Address, email, mobile, landline)
VALUES
    (17, 12345679, 'Mike', 'Taylor', FALSE, '123 College Ave', 'mike.taylor@example.com', '5551231234', '5550001111');

-- Insert into Professors table for user 'Jim Brown'
INSERT INTO Professors (Professor_ID, Name, Surname, Subject, email, mobile)
VALUES
    (18, 'Jim', 'Brown', 'Computer Engineering', 'jim.brown@example.com', '5553214321');

-- Insert into Secretaries table for user 'John Martinez'
INSERT INTO Secretaries (Secretary_ID, Name, Surname, email, mobile)
VALUES
    (19, 'John', 'Martinez', 'john.martinez@example.com', '5559879876');

-- Insert sample data into Thesis table
INSERT INTO Thesis (title, description, status, supervisorID, member1ID, member2ID, studentID, finalGrade, postedDate, assignmentDate, completionDate, examinationDate, withdrawalDate)
VALUES
    ('AI in Healthcare', 'Exploring AI applications in healthcare.', 'active', 9, 10, 13, 1, NULL, '2024-01-15', '2024-02-01', NULL, NULL, NULL),
    ('Quantum Computing', 'Study of quantum computing applications.', 'under assignment', 10, NULL, NULL, 2, NULL, '2024-01-20', NULL, NULL, NULL, NULL),
    ('Blockchain Security', 'Blockchain and cybersecurity integration.', 'finalized', 11, 12, 13, 3, 85.5, '2024-02-05', '2024-02-07', '2024-03-10', '2024-03-15', NULL),
    ('Data Privacy', 'Data privacy measures in technology.', 'under review', 12, 10, 9, 5, NULL, '2024-02-10', '2024-03-10', '2024-07-15', '2025-01-10', NULL),
    ('Sustainable Computing', 'Eco-friendly computing solutions.', 'withdrawn', 13, NULL, NULL, 4, NULL, '2024-02-20', '2024-03-01', NULL, NULL, '2024-04-01');

-- Insert sample data into Examination table
INSERT INTO Examination (thesisID, supervisorID, member1ID, member2ID, examinationDate, examinationMethod, location, finalGrade)
VALUES
    (3, 11, 12, 13, '2024-03-15', 'in person', 'Room 204, Science Building', 85.5),
    (4, 12, 10, 9, '2025-01-10', 'online', 'https://exam.example.com/ai-healthcare', NULL);

-- Insert sample data into ThesisComments table
INSERT INTO ThesisComments (thesisID, professorID, comment)
VALUES
    (1, 9, 'This research direction in AI is promising.'),
    (1, 10, 'Consider ethical implications in AI use.'),
    (2, 10, 'Quantum computing needs further exploration.'),
    (3, 11, 'Blockchain has strong potential in security.'),
    (3, 12, 'Cybersecurity aspect needs enhancement.');

-- Insert sample data into Invitations table
INSERT INTO Invitations (thesisID, studentID, professorID, status, sentDate, responseDate)
VALUES
    (1, 1, 10, 'accepted', '2024-01-18', '2024-01-20'), 
    (1, 1, 13, 'accepted', '2024-01-18', '2024-02-01'),           
    (2, 2, 11, 'pending', '2024-01-22', NULL),  
    (2, 2, 13, 'rejected', '2024-01-22', '2024-01-23'),  
    (3, 3, 13, 'accepted', '2024-02-01', '2024-02-05'),  
    (3, 3, 12, 'accepted', '2024-02-01', '2024-02-07'),  
    (4, 5, 9, 'accepted', '2024-02-10', '2024-02-10'),            
    (4, 5, 10, 'accepted', '2024-02-10', '2024-03-10');           


######################################################################################################################################################################################################


DELIMITER //

CREATE TRIGGER assign_committee_member
AFTER UPDATE ON Invitations
FOR EACH ROW
BEGIN
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
END //

DELIMITER ;