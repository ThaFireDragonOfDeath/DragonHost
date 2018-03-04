#!/bin/bash

chmod -R 400 /srv/dragonhost/frontend
chmod -R 600 /srv/dragonhost/frontend/www
chown -R dh_admin:webspace_framework /srv/dragonhost/frontend

chmod -R 600 /srv/dragonhost/database
chown -R mysql /srv/dragonhost/database

chmod 644 /srv/dragonhost/users
chown root:root /srv/dragonhost/users

chmod -R 660 /srv/dragonhost/framework
chown -R dh_admin:webspace_framework /srv/dragonhost/framework

chmod -R 660 /srv/dragonhost/config
chown -R dh_admin:webspace_framework /srv/dragonhost/config
