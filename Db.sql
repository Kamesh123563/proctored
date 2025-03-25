CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(255) NOT NULL,
    question TEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    option1 VARCHAR(255),
    option2 VARCHAR(255),
    option3 VARCHAR(255),
    option4 VARCHAR(255),
    marks INT NOT NULL
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(255),
    lastname VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(15),
    rollno VARCHAR(20),
    photo VARCHAR(255),
    username VARCHAR(255) UNIQUE,
    password VARCHAR(255)
);

