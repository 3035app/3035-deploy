FROM debian:11

ARG PSQL_VER=11
ARG PHP_VER=7.4

RUN apt-get update \
    && apt-get install --no-install-recommends -y apt-transport-https lsb-release ca-certificates net-tools lsof wget sudo less \
    && apt-get install --no-install-recommends -y git curl build-essential unzip gnupg \
    && apt-get install --no-install-recommends -y nano \
    && apt-get autoremove -y && apt-get clean

RUN apt-get update \
    && apt-get install --no-install-recommends -y php${PHP_VER} php${PHP_VER}-cli php${PHP_VER}-pgsql php${PHP_VER}-mysql php${PHP_VER}-curl php${PHP_VER}-json php${PHP_VER}-gd php${PHP_VER}-intl php${PHP_VER}-sqlite3 php${PHP_VER}-gmp php${PHP_VER}-geoip php${PHP_VER}-mbstring php${PHP_VER}-redis php${PHP_VER}-xml php${PHP_VER}-zip php${PHP_VER}-xdebug \
    && apt-get install --no-install-recommends -y php${PHP_VER}-xdebug \
    && apt-get install -y apache2 libapache2-mod-php${PHP_VER} php${PHP_VER}-pgsql php${PHP_VER}-cli php${PHP_VER}-mbstring php${PHP_VER}-json php${PHP_VER}-xml php${PHP_VER}-zip php${PHP_VER}-curl \
    && apt-get upgrade -y \
    && apt-get autoremove -y && apt-get clean \
    && a2dismod mpm_event && a2enmod php${PHP_VER} && a2enmod rewrite

RUN apt-get install -y php-symfony-console

# setup apt repository to be able to install older version of postgresql
RUN curl https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor | sudo tee /etc/apt/trusted.gpg.d/apt.postgresql.org.gpg >/dev/null
RUN sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list'
RUN apt-get update \
    && apt-get install -y postgresql-${PSQL_VER}

RUN echo "phar.readonly = Off" >> /etc/php/${PHP_VER}/cli/conf.d/42-phar-readonly.ini \
    && echo "memory_limit=-1" >> /etc/php/${PHP_VER}/cli/conf.d/42-memory-limit.ini \
    && echo "date.timezone=Europe/Paris" >> /etc/php/${PHP_VER}/cli/conf.d/68-date-timezone.ini

RUN echo "host  all  all  0.0.0.0/0  md5" >> /etc/postgresql/${PSQL_VER}/main/pg_hba.conf

RUN curl -sS https://getcomposer.org/installer | php -- --version=1.10.25 --install-dir=/usr/local/bin --filename=composer \
    && chmod 755 /usr/local/bin/composer

COPY back/ /var/www/pialab-back/
COPY docker/backcfg/000-default.conf /etc/apache2/sites-available/
RUN mkdir -p --mode=777 /var/www/pialab-back/var

# TODO: issue on libs versions -- waiting for Sf upgrade
# RUN cd /var/www/pialab-back/ && composer install --no-dev
# RUN cd /var/www/pialab-back/ && composer update --no-dev

COPY docker/backcfg/vendor/ /var/www/pialab-back/vendor
COPY docker/backcfg/.env /var/www/pialab-back

COPY docker/backcfg/create_role.psql /
COPY docker/backcfg/entrypoint.sh /
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

WORKDIR /var/www/pialab-back/

EXPOSE 80/tcp

CMD ["/usr/sbin/apachectl", "-D", "FOREGROUND"]

LABEL author=PiaLab
