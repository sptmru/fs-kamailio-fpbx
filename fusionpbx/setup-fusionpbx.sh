#!/bin/bash

# Set environment variables
DB_HOST=${DB_HOST:-localhost}
DB_PORT=${DB_PORT:-5432}
DB_NAME=${DB_NAME:-fusionpbx}
DB_USER=${DB_USER:-fusionpbx}
DB_PASS=${DB_PASS:-fusionpbx}

# Update the FusionPBX configuration
sed -i "s|{database_host}|${DB_HOST}|" /var/www/fusionpbx/core/config.php
sed -i "s|{database_port}|${DB_PORT}|" /var/www/fusionpbx/core/config.php
sed -i "s|{database_name}|${DB_NAME}|" /var/www/fusionpbx/core/config.php
sed -i "s|{database_username}|${DB_USER}|" /var/www/fusionpbx/core/config.php
sed -i "s|{database_password}|${DB_PASS}|" /var/www/fusionpbx/core/config.php

# Start Apache server
exec apache2-foreground
