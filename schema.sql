-- SQL script to create tables
CREATE TABLE plugins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    version VARCHAR(50),
    mc_version VARCHAR(50),
    description TEXT,
    file_path VARCHAR(255),
    created_at DATETIME
);

CREATE TABLE downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plugin_id INT,
    downloaded_at DATETIME,
    FOREIGN KEY (plugin_id) REFERENCES plugins(id) ON DELETE CASCADE
);

CREATE TABLE updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT,
    created_at DATETIME
);

CREATE TABLE settings (
    `key` VARCHAR(50) PRIMARY KEY,
    `value` TEXT
);
