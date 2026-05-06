-- Wapenamanda Political Registration Database Schema

CREATE DATABASE IF NOT EXISTS wapenamanda_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wapenamanda_db;

-- Main Users
CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  surname VARCHAR(100),
  ward VARCHAR(100),
  tribe VARCHAR(100),
  phone VARCHAR(20),
  email VARCHAR(100) UNIQUE,
  id_image VARCHAR(255),
  age INT,
  dob DATE,
  employment_status ENUM('employed','selfEmployed','student','unemployed'),
  company VARCHAR(255),
  study_level ENUM('primary','high','college','university'),
  school_name VARCHAR(255),
  marital_status ENUM('single','married'),
  constituency ENUM('Tsak','Wapenamanda'),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Spouse
CREATE TABLE IF NOT EXISTS spouses (
  spouse_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  name VARCHAR(100),
  surname VARCHAR(100),
  ward VARCHAR(100),
  tribe VARCHAR(100),
  phone VARCHAR(20),
  email VARCHAR(100),
  id_image VARCHAR(255),
  age INT,
  dob DATE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Children
CREATE TABLE IF NOT EXISTS children (
  child_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  name VARCHAR(100),
  surname VARCHAR(100),
  age INT,
  dob DATE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Candidates (Admins)
CREATE TABLE IF NOT EXISTS candidates (
  candidate_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  ward VARCHAR(100),
  constituency ENUM('Tsak','Wapenamanda'),
  email VARCHAR(100) UNIQUE,
  password_hash VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
