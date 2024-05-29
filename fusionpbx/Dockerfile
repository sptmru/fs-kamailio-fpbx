FROM debian:bookworm

RUN apt-get update \
  && apt-get install -y wget systemd systemd-sysv ca-certificates git lsb-release dialog gpg snmpd

RUN wget -O - https://raw.githubusercontent.com/fusionpbx/fusionpbx-install.sh/master/debian/pre-install.sh | sh;
RUN cd /usr/src/fusionpbx-install.sh/debian/resources && ./php.sh
RUN cd /usr/src/fusionpbx-install.sh/debian/resources && ./nginx.sh
RUN cd /usr/src/fusionpbx-install.sh/debian/resources && ./fusionpbx.sh
RUN cd /usr/src/fusionpbx-install.sh/debian/resources && ./applications.sh

EXPOSE 80