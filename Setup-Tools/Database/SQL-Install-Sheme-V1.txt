CREATE TABLE jobs (
    jobid INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    jobtype INT NOT NULL,
    jobstate INT NOT NULL,
    jobmessage TEXT
);

CREATE TABLE jobs_user (
    jobuser_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    jobid INT UNSIGNED NOT NULL,
    userid INT UNSIGNED NOT NULL,
    password-enc VARCHAR(256)
);

CREATE TABLE jobs_db (
    jobdb_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    jobid INT UNSIGNED NOT NULL,
    database_name VARCHAR(64) NOT NULL,
    password_enc VARCHAR(256)
);

CREATE TABLE maintenance (
    maint_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    jobid INT UNSIGNED NOT NULL,
    maint_state INT NOT NULL,
    maint_message TEXT
);

CREATE TABLE users (
    userid INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(32) NOT NULL,
    password_hash VARCHAR(256) NOT NULL,
    userspace INT UNSIGNED NOT NULL,
    userstate INT NOT NULL
);

CREATE TABLE databases (
    database_name VARCHAR(64) NOT NULL PRIMARY KEY,
    userid INT UNSIGNED NOT NULL,
    dbstate INT NOT NULL
);

CREATE TABLE kvconfigs (
    key VARCHAR(64) NOT NULL PRIMARY KEY,
    value VARCHAR(64) NOT NULL
);
