FROM debian:11

ARG PSQL_VER=11
ARG PHP_VER=7.4

ARG tenant=generic
ARG su_username=test
ARG su_password=test
ARG su_email=test@test.tld
ARG smtp=smtp://127.0.0.1:1025
ARG front=http://localhost:4200
ARG back=http://localhost:8000
ARG sso_url
ARG sso_connect_id
ARG sso_connect_secret
ARG sso_callback
ARG client_id
ARG client_secret

ENV env_tenant=$tenant
ENV env_su_username=$su_username
ENV env_su_password=$su_password
ENV env_su_email=$su_email
ENV env_smtp=$smtp
ENV env_front=$front
ENV env_back=$back
ENV env_sso_url=$sso_url
ENV env_sso_connect_id=$sso_connect_id
ENV env_sso_connect_secret=$sso_connect_secret
ENV env_sso_callback=$sso_callback
ENV env_client_id=$client_id
ENV env_client_secret=$client_secret

#-------------------------------------------------------------------------------------------------#
# SYSTEM                                                                                          #
#-------------------------------------------------------------------------------------------------#

RUN apt-get update \
    && apt-get install --no-install-recommends -y apt-utils apt-transport-https lsb-release \
      ca-certificates net-tools lsof wget sudo less git curl build-essential unzip gnupg nano \
      apache2  \
    && apt-get autoremove -y \
    && apt-get clean

RUN a2dissite 000-default

SHELL ["/bin/bash", "-c"]

#-------------------------------------------------------------------------------------------------#
# BACK                                                                                            #
#-------------------------------------------------------------------------------------------------#

RUN apt-get update \
    && apt-get install --no-install-recommends -y php${PHP_VER} php${PHP_VER}-cli \
      php${PHP_VER}-pgsql php${PHP_VER}-mysql php${PHP_VER}-curl php${PHP_VER}-json \
      php${PHP_VER}-gd php${PHP_VER}-intl php${PHP_VER}-sqlite3 php${PHP_VER}-gmp \
      php${PHP_VER}-geoip php${PHP_VER}-mbstring php${PHP_VER}-redis php${PHP_VER}-xml \
      php${PHP_VER}-zip php${PHP_VER}-xdebug \
    && apt-get install -y libapache2-mod-php${PHP_VER} php${PHP_VER}-pgsql \
      php${PHP_VER}-cli php${PHP_VER}-mbstring php${PHP_VER}-json php${PHP_VER}-xml \
      php${PHP_VER}-zip php${PHP_VER}-curl php-symfony-console \
    && apt-get upgrade -y \
    && apt-get autoremove -y \
    && apt-get clean

RUN a2dismod mpm_event && a2enmod php${PHP_VER} && a2enmod rewrite

# setup apt repository to be able to install older version of postgresql
RUN curl https://www.postgresql.org/media/keys/ACCC4CF8.asc \
  | gpg --dearmor \
  | sudo tee /etc/apt/trusted.gpg.d/apt.postgresql.org.gpg >/dev/null
RUN sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" \
  > /etc/apt/sources.list.d/pgdg.list'
RUN apt-get update \
  && apt-get install -y postgresql-${PSQL_VER}

RUN echo "phar.readonly = Off" >> /etc/php/${PHP_VER}/cli/conf.d/42-phar-readonly.ini \
  && echo "memory_limit=-1" >> /etc/php/${PHP_VER}/cli/conf.d/42-memory-limit.ini \
  && echo "date.timezone=Europe/Paris" >> /etc/php/${PHP_VER}/cli/conf.d/68-date-timezone.ini

RUN echo "host  all  all  0.0.0.0/0  md5" >> /etc/postgresql/${PSQL_VER}/main/pg_hba.conf

COPY back/ /var/www/pialab-back/
COPY docker/backcfg/pialab-back.conf /etc/apache2/sites-available/
RUN mkdir -p --mode=777 /var/www/pialab-back/var

RUN a2ensite pialab-back

# TODO: issue on libs versions -- composer install and update postponed until Sf is upgraded
# RUN curl -sS https://getcomposer.org/installer \
#    | php -- --version=1.10.25 --install-dir=/usr/local/bin --filename=composer \
#    && chmod 755 /usr/local/bin/composer
# RUN cd /var/www/pialab-back/ && composer install --no-dev
# RUN cd /var/www/pialab-back/ && composer update --no-dev
# alternative is to copy the vendor folder
COPY docker/backcfg/vendor/ /var/www/pialab-back/vendor

COPY docker/backcfg/.env /var/www/pialab-back
RUN sed -i 's:^CORS_ALLOW_ORIGIN=.*:CORS_ALLOW_ORIGIN=^'"${env_front//:/\\:}"'*$:' /var/www/pialab-back/.env
RUN sed -i 's:^MAILER_URL=.*:MAILER_URL='"${env_smtp//:/\\:}"':' /var/www/pialab-back/.env
RUN sed -i 's:^SNCF_CONNECT_URL=.*:SNCF_CONNECT_URL='"${env_sso_url//:/\\:}"':' /var/www/pialab-back/.env
RUN sed -i 's/^.*SNCF_CONNECT_ID=.*/SNCF_CONNECT_ID='$env_sso_connect_id'/' /var/www/pialab-back/.env
RUN sed -i 's/^.*SNCF_CONNECT_SECRET=.*/SNCF_CONNECT_SECRET='$env_sso_connect_secret'/' /var/www/pialab-back/.env

COPY docker/backcfg/create_role.psql /

#-------------------------------------------------------------------------------------------------#
# FRONT                                                                                           #
#-------------------------------------------------------------------------------------------------#

RUN apt-get update \
  && apt-get install --no-install-recommends -y npm \
  && apt-get autoremove -y \
  && apt-get clean

COPY front/ /var/www/pialab-front/
COPY docker/frontcfg/pialab-front.conf /etc/apache2/sites-available/

RUN a2ensite pialab-front

COPY docker/frontcfg/environment.ts /var/www/pialab-front/src/environments
RUN sed -i 's/^.*tenant:.*/tenant: '"'$env_tenant'"'/' /var/www/pialab-front/src/environments/environment.ts
RUN sed -i 's#^.*host:.*#host: '"'${env_back//:/\\:}'"',#' /var/www/pialab-front/src/environments/environment.ts
RUN sed -i 's/^.*client_id:.*/client_id: '"'$env_client_id'"',/' /var/www/pialab-front/src/environments/environment.ts
RUN sed -i 's/^.*client_secret:.*/client_secret: '"'$env_client_secret'"',/' /var/www/pialab-front/src/environments/environment.ts
RUN sed -i 's#^.*callback_url:.*#callback_url: '"'${env_sso_callback//:/\\:}'"',#' /var/www/pialab-front/src/environments/environment.ts
RUN sed -i 's#^.*connect_url:.*#connect_url: '"'${env_sso_url//:/\\:}'"',#' /var/www/pialab-front/src/environments/environment.ts
RUN sed -i 's/^.*connect_id:.*/connect_id: '"'$env_sso_connect_id'"',/' /var/www/pialab-front/src/environments/environment.ts

WORKDIR /var/www/pialab-front/

RUN npm install -g @angular/cli

RUN npm clean-install

RUN npm run build

#-------------------------------------------------------------------------------------------------#
# EXPOSE & SERVE                                                                                  #
#-------------------------------------------------------------------------------------------------#

COPY docker/entrypoint.sh /
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

EXPOSE 80
EXPOSE 4200

CMD ["/usr/sbin/apachectl", "-D", "FOREGROUND"]

LABEL author=PiaLab
