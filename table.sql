CREATE TABLE Student (
	SID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	FName varchar(255),
	LName varchar(255),
	Email VARCHAR(50),
	Contact varchar(255),
	SPass varchar(255)
);


CREATE TABLE Tutor (
	TID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	FName varchar(255),
	LName varchar(255),
	Email varchar(255),
	Contact varchar(255),
	TPass varchar(255)
);

CREATE TABLE Admin (
	AID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	FName varchar(255),
	LName varchar(255),
	Email varchar(255),
	Contact varchar(255),
	APass varchar(255)
);


INSERT INTO Student (FName, LName, Email, Contact, SPass)
VALUES ("Cardinal", "Smith", "cardinal10@gmail.com", "60142637985", "cs_10");

INSERT INTO Tutor (FName, LName, Email, Contact, TPass)
VALUES ("Jess", "John", "tjess1@gmail.com", "60175341968", "tjess1");

INSERT INTO Admin (FName, LName, Email, Contact, APass)
VALUES ("Abby", "James", "123abby@gmail.com", "601947589632", "admin123");

SELECT * FROM Student;
SELECT * FROM Tutor;
SELECT * FROM Admin;

update student set SPass = "cs_1010" where SID = 1;

/* Below this is the new updated queries  */

CREATE TABLE StudentAssignment (
    assignID INT PRIMARY KEY AUTO_INCREMENT,
    SID INT,
    TID INT,
    FOREIGN KEY (SID) REFERENCES Student(SID),
    FOREIGN KEY (TID) REFERENCES Tutor(TID)
);

CREATE TABLE Course (
    courseID INT AUTO_INCREMENT PRIMARY KEY,
    courseName VARCHAR(255),
    startDate DATE,
    endDate DATE,
    courseDesc TEXT,
    TID INT,
    FOREIGN KEY (TID) REFERENCES Tutor(TID)
);

/* Add this table  */

CREATE TABLE CourseStudent (
    csID INT AUTO_INCREMENT PRIMARY KEY,
    courseID INT,
    SID INT,
    FOREIGN KEY (courseID) REFERENCES Course(courseID),
    FOREIGN KEY (SID) REFERENCES Student(SID),
    UNIQUE KEY (courseId, SID)
);


/*NEW QUERY*/

CREATE TABLE MeetingStudent (
    meetingID INT AUTO_INCREMENT PRIMARY KEY,
    courseTitle TEXT,
    meetingDate DATE,
    meetingTime TIME,
    meetingLocation TEXT,
    meetingDesc TEXT,
    TID INT,
    FOREIGN KEY (TID) REFERENCES Tutor(TID)
);

/* update table MeetingStudent */
ALTER TABLE MeetingStudent
ADD COLUMN status VARCHAR(20) DEFAULT 'Pending' AFTER meetingDesc;

ALTER TABLE MeetingStudent
ADD COLUMN SID INT AFTER TID,
ADD FOREIGN KEY (SID) REFERENCES Student(SID);

=======
/*Adam changes*/

ALTER TABLE Student ADD COLUMN last_login TIMESTAMP NULL;

ALTER TABLE Tutor ADD COLUMN last_login TIMESTAMP NULL;

ALTER TABLE Admin ADD COLUMN last_login TIMESTAMP NULL;

CREATE TABLE BlogPost (
    PostID INT PRIMARY KEY AUTO_INCREMENT,
    Title VARCHAR(255),
    Content TEXT,
    StudentID INT,
    TutorID INT,
    ImagePath VARCHAR(255),
    UserRole ENUM('student', 'tutor'),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (StudentID) REFERENCES Student(SID) ON DELETE CASCADE,
    FOREIGN KEY (TutorID) REFERENCES Tutor(TID) ON DELETE CASCADE

);

CREATE TABLE Tutorial (
    tutorialID INT AUTO_INCREMENT PRIMARY KEY,
    tutorID INT, 
    tutorialTitle VARCHAR(255),
    tutorialDescription TEXT,
    tutorialFilePath VARCHAR(255),
    uploadDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutorID) REFERENCES Tutor(TID) 
);


CREATE TABLE TutorialAnswer (
    tutorialAnswerID INT AUTO_INCREMENT PRIMARY KEY,
    tutorialID INT,
    SID INT,
    tutorialAnswerTitle TEXT,
    tutorComment TEXT,
    tutorialAnswerFilePath VARCHAR(255),
    uploadDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (SID) REFERENCES Student(SID),
    FOREIGN KEY (tutorialID) REFERENCES Tutorial(tutorialID)
);

CREATE TABLE Note (
    noteID INT AUTO_INCREMENT PRIMARY KEY,
    tutorID INT, 
    courseID INT,
    noteTitle VARCHAR(255),
    noteDescription TEXT,
    noteFilePath VARCHAR(255),
    uploadDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutorID) REFERENCES Tutor(TID),
    FOREIGN KEY (courseID) REFERENCES Course(courseID)
);


ALTER TABLE Tutorial
ADD COLUMN CourseID INT,
ADD FOREIGN KEY (CourseID) REFERENCES Course(courseID);

CREATE TABLE Messages ( 
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    TID INT,
    SID INT,
    messageContent TEXT,
    sender_type TEXT,
    receiver_type TEXT,
    readStatus TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (TID) REFERENCES Tutor(TID) ON DELETE CASCADE,
    FOREIGN KEY (SID) REFERENCES Student(SID) ON DELETE CASCADE
);


