-- SQL script to create tables
CREATE TABLE plugins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    description TEXT,
    created_at DATETIME
);

CREATE TABLE plugin_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plugin_id INT,
    version VARCHAR(50),
    mc_version VARCHAR(50),
    file_path VARCHAR(255),
    created_at DATETIME,
    FOREIGN KEY (plugin_id) REFERENCES plugins(id) ON DELETE CASCADE
);

CREATE TABLE downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version_id INT,
    downloaded_at DATETIME,
    FOREIGN KEY (version_id) REFERENCES plugin_versions(id) ON DELETE CASCADE
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
