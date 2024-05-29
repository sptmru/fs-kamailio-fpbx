FROM debian:wheezy

RUN groupadd -r freeswitch && useradd -r -d /var/lib/freeswitch -s /bin/false -c 'FreeSWITCH' -g freeswitch freeswitch

# install basics
RUN \
  apt-get update && \
  DEBIAN_FRONTEND=noninteractive apt-get -y install git curl procps net-tools supervisor

# install all required packages
RUN rm -rf /var/lib/apt/lists/ && sed -e '/contrib.*non-free/! s/\(.*\)/\1 contrib non-free/' \
  -i /etc/apt/sources.list && \
  echo "deb http://repo.fusionpbx.com/fusionpbx/release/debian/ wheezy main" >/etc/apt/sources.list.d/fusionpbx.list && \
  apt-get update && \
  DEBIAN_FRONTEND=noninteractive apt-get --force-yes -y install unixodbc uuid memcached libtiff5 libtiff-tools time bison htop screen libpq5 lame \
  fusionpbx-app-conference-centers fusionpbx-app-conferences-active fusionpbx-app-meetings fusionpbx-app-conferences \
  fusionpbx-app-adminer fusionpbx-app-backup fusionpbx-app-call-broadcast fusionpbx-app-call-center fusionpbx-app-call-center-active \
  fusionpbx-app-call-flows fusionpbx-app-content fusionpbx-app-edit fusionpbx-app-exec fusionpbx-app-hot-desking fusionpbx-app-schemas \
  fusionpbx-app-services fusionpbx-app-sipml5 fusionpbx-app-sql-query fusionpbx-app-traffic-graph fusionpbx-app-xmpp \
  sqlite3 ssl-cert nginx php5-cli php5-common php-apc php5-gd \
  php-db php5-fpm php5-memcache php5-sqlite php5-imap php5-mcrypt php5-curl \
  fusionpbx-core fusionpbx-app-calls fusionpbx-app-calls-active fusionpbx-app-call-block \
  fusionpbx-app-contacts fusionpbx-app-destinations fusionpbx-app-dialplan fusionpbx-app-dialplan-inbound \
  fusionpbx-app-dialplan-outbound fusionpbx-app-extensions fusionpbx-app-follow-me fusionpbx-app-gateways \
  fusionpbx-app-ivr-menu fusionpbx-app-login fusionpbx-app-log-viewer fusionpbx-app-modules fusionpbx-app-music-on-hold \
  fusionpbx-app-recordings fusionpbx-app-registrations fusionpbx-app-ring-groups fusionpbx-app-settings \
  fusionpbx-app-sip-profiles fusionpbx-app-sip-status fusionpbx-app-system fusionpbx-app-time-conditions \
  fusionpbx-app-xml-cdr fusionpbx-app-vars fusionpbx-app-voicemails fusionpbx-app-voicemail-greetings \
  fusionpbx-conf fusionpbx-scripts fusionpbx-sqldb fusionpbx-theme-enhanced fusionpbx-music-default

# define configuration environment variables
ENV FUSIONPBX_WWW_ROOT /var/www
ENV FUSIONPBX_CONF /etc/fusionpbx
ENV FUSIONPBX_DATA /var/lib/fusionpbx
ENV FUSIONPBX_DB /var/lib/fusionpbx/db
ENV FUSIONPBX_DEFAULT_COUNTRY CH
ENV FUSIONPBX_REPO http://fusionpbx.googlecode.com/svn/trunk/
ENV FUSIONPBX_REVISION HEAD

ENV UPLOAD_SIZE 25M

# edit php5-fpm configuration
RUN sed -e "s#^\(\s*\)[;]\{0,1\}\(\s*upload_max_filesize\s\)[^;\S]*#\1\2= ${UPLOAD_SIZE}#" \
  -e "s#^\(\s*\)[;]\{0,1\}\(\s*post_max_size\s\)[^;\S]*#\1\2= ${UPLOAD_SIZE}#" \
  -i /etc/php5/fpm/php.ini
RUN sed -e "s#^\(\s*\)[;]\{0,1\}\(\s*daemonize\s\)[^;\S]*#\1\2= no#" \
  -i /etc/php5/fpm/php-fpm.conf

# edit nginx configuration
COPY fusionpbx-nginx.conf /etc/nginx/sites-available/

RUN sed	-e "s#^\(\s*\)[#]\{0,1\}\(\s*worker_processes\s\)[^;\S]*#\1\22#" \
  -e "s#^\(\s*\)[#]\{0,1\}\(\s*multi_accept\s\)[^;\S]*#\1\2on#" \
  -e "s#\(\(\s*\)default_type\s*application/octet-stream\s*;\)#\1\n\n\2open_file_cache max=1000 inactive=20s;\n\2open_file_cache_valid 30s;\n\2open_file_cache_min_uses 2;\n\2open_file_cache_errors off;\n\n\2fastcgi_cache_path /var/cache/nginx levels=1:2 keys_zone=microcache:15M max_size=1000m inactive=60m;\n\n#" \
  -e "s#\(\(\s*\)gzip\s*on\s*;\)#\1\n\2gzip_static on;#" \
  -e '1,/^\s*$/ s/^\s*$/daemon off;\n/' \
  -i /etc/nginx/nginx.conf

RUN adduser freeswitch www-data && \
  adduser freeswitch dialout && \
  adduser www-data freeswitch && \
  adduser www-data audio && \
  adduser www-data dialout

# backup default fusionpbx conf
RUN mkdir -p /usr/share/examples/fusionpbx/default && \
  mv /etc/fusionpbx/* /usr/share/examples/fusionpbx/default/

# backup fusionpbx default music
RUN mkdir -p /usr/share/examples/fusionpbx/default_sounds && \
  mv /var/lib/fusionpbx/sounds/* /usr/share/examples/fusionpbx/default_sounds/

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker-entrypoint.sh /
RUN chmod +x /docker-entrypoint.sh /freeswitch.sh
ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord"]

VOLUME ["$FUSIONPBX_CONF", "$FUSIONPBX_DATA", "/var/log"]
EXPOSE 80 443 16384  16385  16386  16387  16388  16389  16390  16391  16392  16393
