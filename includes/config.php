<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'your_db_username');
define('DB_PASSWORD', 'your_db_password');
define('DB_NAME', 'portfolyo_db');

// Attempt to connect to MySQL database
$connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Check connection
if (!$connection) {
    die("ERROR: Could not connect to database. " . mysqli_connect_error());
}

// Create database if it doesn't exist
$createDB = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if (mysqli_query($connection, $createDB)) {
    mysqli_select_db($connection, DB_NAME);
} else {
    die("ERROR: Could not create database. " . mysqli_error($connection));
}

// Create users table
$usersTable = "CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'default-avatar.png',
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_verified TINYINT(1) DEFAULT 0,
    verification_code VARCHAR(100),
    is_admin TINYINT(1) DEFAULT 0
)";

// Create portfolios table
$portfoliosTable = "CREATE TABLE IF NOT EXISTS portfolios (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    platform VARCHAR(50) NOT NULL,
    link VARCHAR(255) NOT NULL,
    title VARCHAR(100),
    followers INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

// Create followers table
$followersTable = "CREATE TABLE IF NOT EXISTS followers (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    follower_id INT NOT NULL,
    followed_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_follow (follower_id, followed_id),
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (followed_id) REFERENCES users(id) ON DELETE CASCADE
)";

// Create comments table
$commentsTable = "CREATE TABLE IF NOT EXISTS comments (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    portfolio_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (portfolio_id) REFERENCES portfolios(id) ON DELETE CASCADE
)";

// Create notifications table
$notificationsTable = "CREATE TABLE IF NOT EXISTS notifications (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL, 
    sender_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    content TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
)";

// Create sponsorship_offers table for marketplace
$sponsorshipOffersTable = "CREATE TABLE IF NOT EXISTS sponsorship_offers (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    collaboration_type VARCHAR(50) NOT NULL,
    min_budget DECIMAL(10,2) DEFAULT 0,
    max_budget DECIMAL(10,2) DEFAULT 0,
    currency VARCHAR(10) DEFAULT 'TL',
    requirements TEXT,
    deliverables TEXT,
    deadline DATE NOT NULL,
    is_brand TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

// Create sponsorship_applications table for marketplace
$sponsorshipApplicationsTable = "CREATE TABLE IF NOT EXISTS sponsorship_applications (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    offer_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    contact_email VARCHAR(100) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (offer_id) REFERENCES sponsorship_offers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

// Execute create table queries
$tables = [
    $usersTable, 
    $portfoliosTable, 
    $followersTable, 
    $commentsTable, 
    $notificationsTable,
    $sponsorshipOffersTable,
    $sponsorshipApplicationsTable
];

foreach ($tables as $table) {
    if (!mysqli_query($connection, $table)) {
        die("ERROR: Could not create tables. " . mysqli_error($connection));
    }
}

// Global database connection variable
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$conn) {
    die("ERROR: Could not connect to database. " . mysqli_connect_error());
}

/**
 * Sanitize user input
 * @param string $data The data to sanitize
 * @return string The sanitized data
 */
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}
?>
