#!/bin/bash

# Set environment variables
DB_HOST=${DB_HOST:-localhost}
DB_PORT=${DB_PORT:-5432}
DB_NAME=${DB_NAME:-fusionpbx}
DB_USERNAME=${DB_USERNAME:-fusionpbx}
DB_PASSWORD=${DB_PASSWORD:-fusionpbx}

ADMIN_USERNAME=${ADMIN_USERNAME:-admin}
ADMIN_PASSWORD=${ADMIN_PASSWORD:-adminpass}
DOMAIN_NAME=${DOMAIN_NAME:-example.com}

FS_HOST=${FS_HOST:-localhost}

# # Set the FusionPBX database configuration in the database initializing file
sed -i "s|{admin_username}|${ADMIN_USERNAME}|" /var/www/fusionpbx/db-init.php
sed -i "s|{admin_password}|${ADMIN_PASSWORD}|" /var/www/fusionpbx/db-init.php
sed -i "s|{domain_name}|${DOMAIN_NAME}|" /var/www/fusionpbx/db-init.php
sed -i "s|{database_host}|${DB_HOST}|" /var/www/fusionpbx/db-init.php
sed -i "s|{database_port}|${DB_PORT}|" /var/www/fusionpbx/db-init.php
sed -i "s|{database_name}|${DB_NAME}|" /var/www/fusionpbx/db-init.php
sed -i "s|{database_username}|${DB_USERNAME}|" /var/www/fusionpbx/db-init.php
sed -i "s|{database_password}|${DB_PASSWORD}|" /var/www/fusionpbx/db-init.php

# # Update FS directories permissions
/bin/chmod -R 777 /usr/local/freeswitch/conf
/bin/chmod -R 777 /usr/local/freeswitch/sounds
/bin/chmod -R 777 /usr/local/freeswitch/db
/bin/chmod -R 777 /usr/local/freeswitch/recordings
/bin/chmod -R 777 /usr/local/freeswitch/storage
/bin/chmod -R 777 /usr/local/freeswitch/scripts

/bin/chmod -R 777 /etc/fusionpbx
/bin/chown -R www-data:www-data /etc/fusionpbx

# # Initialize the FusionPBX database
/usr/bin/rm /etc/fusionpbx/config.conf
/usr/bin/php /var/www/fusionpbx/db-init.php
mkdir -p /var/log/freeswitch

# Start FreeSWITCH and Apache
# /usr/local/freeswitch/bin/freeswitch -nonat -nf -nc &
nginx
/etc/init.d/php8.1-fpm start
# /etc/init.d/postgresql start
/usr/bin/freeswitch -conf /etc/freeswitch -db /var/lib/freeswitch/db -log /var/log/freeswitch -nonat -nf -nc
# source /etc/apache2/envvars
# exec apache2 -DFOREGROUND