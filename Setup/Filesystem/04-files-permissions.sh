#!/bin/bash

chmod -R 500 /srv/dragonhost/frontend
chmod -R 700 /srv/dragonhost/frontend/www
chown -R dh_admin:webspace_framework /srv/dragonhost/frontend

chmod -R 770 /srv/dragonhost/database
chown -R mysql:mysql /srv/dragonhost/database

chmod 755 /srv/dragonhost/users
chown root:root /srv/dragonhost/users

chmod -R 770 /srv/dragonhost/framework
chown -R dh_admin:webspace_framework /srv/dragonhost/framework

chmod -R 770 /srv/dragonhost/config
chown -R dh_admin:webspace_framework /srv/dragonhost/config

chmod 755 /usr/bin/dh_cronscript.sh
chown root:root /usr/bin/dh_cronscript.sh
