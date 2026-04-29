-- Smart Class Notes Organizer — Database Schema
-- MySQL 8.0+

CREATE DATABASE IF NOT EXISTS noteorg CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE noteorg;

-- Users table
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,         -- bcrypt hash
    university  VARCHAR(150),
    year        VARCHAR(20),
    avatar      VARCHAR(255),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Subjects table
CREATE TABLE subjects (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    name        VARCHAR(100) NOT NULL,
    color       VARCHAR(30) DEFAULT 'purple',  -- purple | teal | amber | coral | blue | green
    icon        VARCHAR(10)  DEFAULT '📚',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Notes table
CREATE TABLE notes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    subject_id  INT,
    title       VARCHAR(255) NOT NULL,
    content     TEXT,
    is_favorite TINYINT(1) DEFAULT 0,
    is_pinned   TINYINT(1) DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL
);

-- Tags table
CREATE TABLE tags (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    name        VARCHAR(50) NOT NULL,
    UNIQUE KEY unique_user_tag (user_id, name),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Note–Tag pivot
CREATE TABLE note_tags (
    note_id INT NOT NULL,
    tag_id  INT NOT NULL,
    PRIMARY KEY (note_id, tag_id),
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id)  REFERENCES tags(id)  ON DELETE CASCADE
);

-- File attachments
CREATE TABLE attachments (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    note_id     INT NOT NULL,
    user_id     INT NOT NULL,
    filename    VARCHAR(255) NOT NULL,   -- original name
    filepath    VARCHAR(255) NOT NULL,   -- stored path
    filetype    VARCHAR(50),             -- mime type
    filesize    INT,                     -- bytes
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (note_id)  REFERENCES notes(id)  ON DELETE CASCADE,
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE
);

-- Helpful indexes
CREATE INDEX idx_notes_user    ON notes(user_id);
CREATE INDEX idx_notes_subject ON notes(subject_id);
CREATE INDEX idx_notes_fav     ON notes(is_favorite);
CREATE FULLTEXT INDEX ft_notes ON notes(title, content);
