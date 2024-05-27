FROM debian:bookworm

RUN apt-get update \
  && apt-get install -y wget systemd systemd-sysv ca-certificates

RUN wget -O - https://raw.githubusercontent.com/fusionpbx/fusionpbx-install.sh/master/debian/pre-install.sh | sh;

EXPOSE 80