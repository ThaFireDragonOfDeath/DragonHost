#!/bin/bash

# Copy php framework and backend files
cp -f ../../Backend/dbinterface.php /srv/dragonhost/framework
cp -f ../../Backend/dbrootinterface.php /srv/dragonhost/framework
cp -f ../../Backend/inc_backend.php /srv/dragonhost/framework
cp -f ../../Backend/inc_frontend.php /srv/dragonhost/framework
cp -f ../../Backend/jobmanager.php /srv/dragonhost/framework
cp -f ../../Backend/logger.php /srv/dragonhost/framework
cp -f ../../Backend/projectconfigs.php /srv/dragonhost/framework
cp -f ../../Backend/security.php /srv/dragonhost/framework
cp -f ../../Backend/sqlbackend.php /srv/dragonhost/framework
cp -f ../../Backend/systeminterface.php /srv/dragonhost/framework

# Copy service files
cp -f ../../Backend/Dh_Cronscript.service /lib/systemd/system
cp -f ../../Backend/dh_cronscript.sh /usr/bin

# Copy config files
cp -f ../../Config/apache/frontendsite.conf /etc/apache2/sites-available/000-default.conf
cp -f ../../Config/apache/apache2.conf /etc/apache2
cp -f ../../Config/ftp/vsftpd.conf /etc
cp -f ../../Config/mariadb/50-server.cnf /etc/mysql/mariadb.conf.d/50-server.cnf
cp -fr ../../Config/php /srv/dragonhost/config

# Copy frontend site files
cp -f ../../Webfrontend/account.php /srv/dragonhost/frontend/www
cp -f ../../Webfrontend/index.html /srv/dragonhost/frontend/www
