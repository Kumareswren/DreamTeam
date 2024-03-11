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
