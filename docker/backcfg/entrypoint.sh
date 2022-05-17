#!/bin/sh

/etc/init.d/postgresql start
sudo -u postgres psql template1 -f /create_role.psql \

cd /var/www/pialab-back/ \
    && bin/console doctrine:database:create --if-not-exists \
    && bin/console doctrine:migrations:migrate --no-interaction \
    && bin/console pia:user:create test@test.tld test --username=test \
    && bin/console pia:user:promote test@test.tld --role=ROLE_SUPER_ADMIN \
    && bin/console pia:user:promote test@test.tld --role=ROLE_REDACTOR \
    && bin/console pia:user:promote test@test.tld --role=ROLE_EVALUATOR \
    && bin/console pia:user:promote test@test.tld --role=ROLE_CONTROLLER \
    && bin/console pia:user:promote test@test.tld --role=ROLE_CONTROLLER_MULTI \
    && bin/console pia:user:promote test@test.tld --role=ROLE_DPO \
    && bin/console pia:user:promote test@test.tld --role=ROLE_SHARED_DPO

$@
