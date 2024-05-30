# Use an official PHP image as a parent image
FROM php:7.4-apache

# Install necessary packages
RUN apt-get update && apt-get install -y \
  git \
  curl \
  sudo \
  vim \
  libmariadb-dev \
  libpq-dev \
  libsqlite3-dev \
  && docker-php-ext-install pdo_mysql pdo_pgsql pdo_sqlite \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set up FusionPBX
RUN git clone https://github.com/fusionpbx/fusionpbx.git /var/www/fusionpbx

# Set permissions
RUN chown -R www-data:www-data /var/www/fusionpbx

# Copy the Apache configuration file
COPY apache-fusionpbx.conf /etc/apache2/sites-available/000-default.conf

# Copy the setup script
COPY setup-fusionpbx.sh /usr/local/bin/setup-fusionpbx.sh
RUN chmod +x /usr/local/bin/setup-fusionpbx.sh

# Create /etc/fusionpbx directory
RUN mkdir /etc/fusionpbx
RUN chmod 777 /etc/fusionpbx

# Copy the DB initialization script
COPY db-init.php /var/www/fusionpbx/db-init.php
COPY setup-fusionpbx.sh /usr/local/bin/setup-fusionpbx.sh
RUN chmod +x /usr/local/bin/setup-fusionpbx.sh

# Create DB structure and config
RUN /usr/local/bin/setup-fusionpbx.sh

# Expose port 80
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]
