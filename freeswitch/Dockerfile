FROM debian:bookworm

RUN apt-get update \
  && apt-get install -y systemd systemd-sysv ca-certificates wget curl git

RUN wget -O - https://raw.githubusercontent.com/fusionpbx/fusionpbx-install.sh/master/debian/pre-install.sh | sh;

RUN cd /usr/src/fusionpbx-install.sh/debian && ./install.sh

# Copy the FusionPBX installation script
COPY db-init.php /var/www/fusionpbx/db-init.php

COPY entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]