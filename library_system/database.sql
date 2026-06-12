-- ============================================================
--  LibraFlow - Library Management System Database
--  Run this in phpMyAdmin or MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;

-- -------------------------------------------------------
-- Members table
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS members (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(120) UNIQUE NOT NULL,
    phone       VARCHAR(20),
    address     TEXT,
    joined_date DATE NOT NULL DEFAULT (CURDATE()),
    status      ENUM('active','suspended') DEFAULT 'active',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- Books table
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS books (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(200) NOT NULL,
    author       VARCHAR(150) NOT NULL,
    isbn         VARCHAR(20) UNIQUE,
    category     VARCHAR(80),
    publisher    VARCHAR(150),
    year         YEAR,
    total_copies INT  NOT NULL DEFAULT 1,
    available    INT  NOT NULL DEFAULT 1,
    added_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- Borrow records — implemented as a Queue (FIFO)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS borrow_queue (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    book_id     INT NOT NULL,
    member_id   INT NOT NULL,
    borrow_date DATE NOT NULL DEFAULT (CURDATE()),
    due_date    DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    status      ENUM('borrowed','returned','overdue') DEFAULT 'borrowed',
    queue_pos   INT NOT NULL DEFAULT 0,       -- position in the queue (DSA: Queue order)
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id)   REFERENCES books(id)   ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- Reservation stack — implemented as a Stack (LIFO demo)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS reservation_stack (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    book_id      INT NOT NULL,
    member_id    INT NOT NULL,
    reserved_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    stack_depth  INT NOT NULL DEFAULT 0,      -- stack position (DSA: Stack LIFO)
    status       ENUM('pending','fulfilled','cancelled') DEFAULT 'pending',
    FOREIGN KEY (book_id)   REFERENCES books(id)   ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- Categories (linked-list-style ordering supported)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    name     VARCHAR(80) UNIQUE NOT NULL,
    next_id  INT DEFAULT NULL   -- singly linked list pointer (DSA: Linked List)
);

-- -------------------------------------------------------
-- Admin users
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    username     VARCHAR(60) UNIQUE NOT NULL,
    password     VARCHAR(255) NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- Seed data
-- -------------------------------------------------------
INSERT INTO admins (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- default password: password

INSERT INTO categories (name) VALUES
('Fiction'),('Science'),('Technology'),('History'),('Mathematics'),
('Literature'),('Business'),('Philosophy'),('Art'),('Medicine');

INSERT INTO members (name, email, phone, address, joined_date) VALUES
('Kasun Perera',   'kasun@email.com',   '0771234567', 'Colombo 03', '2025-01-10'),
('Nimali Silva',   'nimali@email.com',  '0782345678', 'Kandy',       '2025-02-15'),
('Ruwan Fernando', 'ruwan@email.com',   '0763456789', 'Galle',       '2025-03-20'),
('Ayesha Bandara', 'ayesha@email.com',  '0754567890', 'Matara',      '2025-04-05'),
('Dilshan Jayawardena','dilshan@email.com','0745678901','Negombo',   '2025-05-01');

INSERT INTO books (title, author, isbn, category, publisher, year, total_copies, available) VALUES
('Introduction to Algorithms',     'Cormen, Leiserson, Rivest', '978-0262033848', 'Technology',   'MIT Press',       2009, 3, 3),
('Data Structures & Algorithms',   'Michael T. Goodrich',       '978-1118771334', 'Technology',   'Wiley',           2014, 2, 2),
('Clean Code',                     'Robert C. Martin',          '978-0132350884', 'Technology',   'Prentice Hall',   2008, 2, 2),
('The Great Gatsby',               'F. Scott Fitzgerald',       '978-0743273565', 'Fiction',      'Scribner',        1925, 4, 4),
('A Brief History of Time',        'Stephen Hawking',           '978-0553380163', 'Science',      'Bantam Books',    1988, 2, 2),
('Design Patterns',                'Gang of Four',              '978-0201633610', 'Technology',   'Addison-Wesley',  1994, 1, 1),
('The Art of War',                 'Sun Tzu',                   '978-1599869773', 'History',      'Filiquarian',     2007, 3, 3),
('Python Crash Course',            'Eric Matthes',              '978-1593279288', 'Technology',   'No Starch Press', 2019, 2, 2),
('Thinking, Fast and Slow',        'Daniel Kahneman',           '978-0374533557', 'Philosophy',   'Farrar Straus',   2011, 2, 2),
('1984',                           'George Orwell',             '978-0451524935', 'Fiction',      'Signet Classic',  1949, 3, 3);
