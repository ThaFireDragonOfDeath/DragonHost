CREATE DATABASE dh_users;
CREATE USER dh_internal IDENTIFIED BY 'testnormalpassword-123';
GRANT ALL PRIVILEGES ON dh_users.* TO 'dh_internal'@'localhost';
SET PASSWORD FOR 'root'@'localhost' = PASSWORD('testrootpassword-123');
FLUSH privileges;
