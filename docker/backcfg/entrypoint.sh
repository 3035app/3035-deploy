#!/bin/sh

/etc/init.d/postgresql start
sudo -u postgres psql template1 -f /create_role.psql \

cd /var/www/pialab-back/ \
    && bin/console doctrine:database:create --if-not-exists \
    && bin/console doctrine:migrations:migrate --no-interaction \
    && bin/console pia:user:create ${env_su_email} ${env_su_password} --username=${env_su_username} \
    && bin/console pia:user:promote ${env_su_email} --role=ROLE_SUPER_ADMIN \
    && bin/console pia:user:promote ${env_su_email} --role=ROLE_REDACTOR \
    && bin/console pia:user:promote ${env_su_email} --role=ROLE_EVALUATOR \
    && bin/console pia:user:promote ${env_su_email} --role=ROLE_CONTROLLER \
    && bin/console pia:user:promote ${env_su_email} --role=ROLE_CONTROLLER_MULTI \
    && bin/console pia:user:promote ${env_su_email} --role=ROLE_DPO \
    && bin/console pia:user:promote ${env_su_email} --role=ROLE_SHARED_DPO

$@
