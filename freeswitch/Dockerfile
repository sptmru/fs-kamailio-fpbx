FROM debian:bookworm

# Install necessary packages via APT
RUN apt-get update \
  && apt-get install -y gnupg2 wget lsb-release git cmake curl vim libmariadb-dev libpq-dev \
  libsqlite3-dev apache2 php libapache2-mod-php php-pgsql php-mysql php-pdo php-pdo-sqlite php-gd \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

# Download the GPG key for the repository
RUN wget --http-user=signalwire --http-password=pat_EoSrCyJGQgW89pasdEeggfCw \
  -O /usr/share/keyrings/signalwire-freeswitch-repo.gpg \
  https://freeswitch.signalwire.com/repo/deb/debian-release/signalwire-freeswitch-repo.gpg

# Create apt auth.conf with credentials
RUN echo "machine freeswitch.signalwire.com login signalwire password pat_EoSrCyJGQgW89pasdEeggfCw" > /etc/apt/auth.conf

# Add the SignalWire repository to sources list
RUN echo "deb [signed-by=/usr/share/keyrings/signalwire-freeswitch-repo.gpg] https://freeswitch.signalwire.com/repo/deb/debian-release/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/freeswitch.list \
  && echo "deb-src [signed-by=/usr/share/keyrings/signalwire-freeswitch-repo.gpg] https://freeswitch.signalwire.com/repo/deb/debian-release/ $(lsb_release -sc) main" >> /etc/apt/sources.list.d/freeswitch.list

# Install FreeSWITCH dependencies
RUN apt-get update \
  && apt-get build-dep -y freeswitch \
  && apt-get clean 

# Clone FreeSWITCH repository
RUN cd /usr/local/src \
  && git clone https://github.com/signalwire/freeswitch.git -bv1.10 freeswitch \
  && cd freeswitch \
  && git config pull.rebase true

# Copy FreeSWITCH modules.conf file
COPY ./modules.conf /usr/local/src/freeswitch/modules.conf

# Build and install FreeSWITCH
RUN cd /usr/local/src/freeswitch \
  && ./bootstrap.sh -j \
  && ./configure \
  && make && make install && make cd-sounds-install cd-moh-install

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Clone FusionPBX repository
RUN git clone https://github.com/fusionpbx/fusionpbx.git /var/www/fusionpbx

# Set FusionPBX directory ownership
RUN chown -R www-data:www-data /var/www/fusionpbx

# Copy the Apache configuration file
COPY apache-fusionpbx.conf /etc/apache2/sites-available/000-default.conf

# Create /etc/fusionpbx directory
RUN mkdir /etc/fusionpbx
RUN chmod 777 /etc/fusionpbx

# Copy the FusionPBX installation script
COPY db-init.php /var/www/fusionpbx/db-init.php

RUN ln -s /usr/local/freeswitch/conf /etc/freeswitch


RUN chown -R www-data:www-data /usr/local/freeswitch/conf

RUN sed -i '/<load module="mod_logfile"\/>/a \    <load module="mod_audio_stream"/>' /usr/local/freeswitch/conf/autoload_configs/modules.conf.xml
RUN sed -i '/<load module="mod_signalwire"\/>/a \    <load module="mod_avmd"/>' /usr/local/freeswitch/conf/autoload_configs/modules.conf.xml
RUN sed -i 's|<param name="listen-ip" value="::"/>|<param name="listen-ip" value="0.0.0.0"/>|g' /usr/local/freeswitch/conf/autoload_configs/event_socket.conf.xml
RUN sed -i 's|<!--<param name="apply-inbound-acl" value="loopback.auto"/>-->|<param name="apply-inbound-acl" value="lan"/>|' /usr/local/freeswitch/conf/autoload_configs/event_socket.conf.xml
RUN sed -i '/<list name="lan" default="allow">/,/<\/list>/c\<list name="lan" default="allow">\n      <node type="allow" cidr="0.0.0.0/0"/>\n<\/list>' /usr/local/freeswitch/conf/autoload_configs/acl.conf.xml

EXPOSE 5060 5060/udp 8021 80
RUN ln -s /usr/local/freeswitch/bin/fs_cli /usr/local/bin/fs_cli

COPY entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]