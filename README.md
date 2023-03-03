# PIALAB - Procédure de déploiement en production

## Introduction

La procédure ci-dessous permet d'installer les composantes du système PiaLab (base de données,
application "back" d'administration et API, application "front") sur un serveur unique.

## Prérequis

Configurer un serveur Linux, préférablement Debian 11, et assurez-vous de pouvoir vous connecter
avec l'utilisateur **root** (ou bien un utilisateur _sudoer_).

Les composantes systèmes suivantes doivent être installées sur le serveur hôte :

* git
* docker

De plus vous devez avoir un accès SSH valide au dépôt Git de PiaLab ; consulter l'équipe PiaLab
en cas de doute ou pour obtenir votre compte ou transmettre votre clé publique.

Vous devez par ailleurs avoir à disposition les informations suivantes, qui seront nécessaires au
cours du déploiement :

* **[TENANT CODE]** :   
  votre code client, transmis par l'équipe PiaLab (pour avoir un affichage AIPD/PIA plus que "traitements", entrer "sncf")
* **[BRANCH RELEASE]** :   
  le nom de la branche à déployer, transmis par l'équipe PiaLab
* **[SUPER USER USERNAME]** :   
  le code utilisateur du super utilisateur
* **[SUPER USER PASSWORD]** :   
  le mot de passe du super utilisateur
* **[SUPER USER EMAIL]** :   
  le courriel du super utilisateur
* **[SMTP CONNECTION]** :   
  l'URI du serveur SMTP utilisé pour relayer l'envoi de courriels par le système, au format
  `smtp://[USER]:[PASSWORD]@[DOMAIN]:[PORT]`
* **[FRONT DOMAIN]** :   
  le nom de domaine pour accéder au _front_ (par défaut **http://localhost:4200**)
* **[BACK DOMAIN]** :   
  le nom de domaine pour accéder au _back_ (par défaut **http://localhost:8000**)
* **[SSO URL]** :  
  l'URL du serveur SSO
* **[SSO CONNECT ID]** :   
  l'identifiant de connexion au serveur SSO
* **[SSO CONNECT SECRET]** :  
  la clé secrète de connexion au serveur SSO
* **[SSO CALLBACK]** :  
  l'URL de retour après l'identification SSO


Concernant les noms de domaine, se reporter à la section **Exposition du système** ci-dessous pour
plus de détails.


## Déploiement initial

Lors du déploiement initial, il est nécessaire d'obtenir le code du présent projet (PiaLab Deploy)
dans un répertoire racine (ici `pialab`) :

Soit via SSH si votre clé publique a été enregistrée sur le dépôt :

```
git clone ssh://git@git.3035.app:2222/pialab/deploy.git \
  --single-branch \
  --depth 1 \
  --branch master \
  pialab
```

Soit par HTTP si vous posséder un compte utilisateur sur le dépôt :

```
git clone https://git.3035.app/pialab/deploy.git \
  --single-branch \
  --depth 1 \
  --branch master \
  pialab
```

En étant positionné dans le répertoire racine, obtenir le code du système (_front_ et _back_) en
veillant à bien préciser le nom de la branche transmise par l'équipe Pialab :

* `cd pialab`
* obtenir le code _front_   
  ```
  git clone ssh://git@git.3035.app:2222/pialab/front.git \
    --single-branch \
    --depth 1 \
    --branch [BRANCH RELEASE]
  ```
* obtenir le code _back_   
  ```
  git clone ssh://git@git.3035.app:2222/pialab/back.git \
    --single-branch \
    --depth 1 \
    --branch [BRANCH RELEASE]
  ```

Construire l'image du conteneur Docker, en veillant bien à positionner les variables de
construction :

```
docker build . \
  --build-arg tenant=[TENANT CODE] \
  --build-arg su_username=[SUPER USER USERNAME] \
  --build-arg su_password=[SUPER USER PASSWORD] \
  --build-arg su_email=[SUPER USER EMAIL] \
  --build-arg smtp=[SMTP CONNECTION] \
  --build-arg front=[FRONT DOMAIN] \
  --build-arg back=[BACK DOMAIN] \
  --build-arg sso_url=[SSO URL] \
  --build-arg sso_connect_id=[SSO CONNECT ID] \
  --build-arg sso_connect_secret=[SSO CONNECT SECRET] \
  --build-arg sso_callback=[SSO CALLBACK] \
  -t pialab
```

Lancer le conteneur piaLab :

```
docker run -p 8000:80 -p 4200:4200 -t -d \
  --restart always \
  --name pialab \
  --hostname pialab \
  --mount source=pialab_db,target=/var/lib/postgresql \
  pialab
```

Créer une application dans le système PiaLab :

```
docker exec -it pialab \
  /var/www/pialab-back/bin/console \
    pia:application:create \
    --name [TENANT CODE] \
    --url [FRONT DOMAIN]
```

Prendre note des clés affichées **[CLIENT ID]** et **[CLIENT SECRET]**.

Stopper et supprimer le conteneur pialab :

* ```docker stop pialab```
* ```docker rm pialab```

Relancer une construction de l'image Docker en précisant cette fois les clés d'application obtenues
à l'étape précédente :

```
docker build . \
  --build-arg tenant=[TENANT CODE] \
  --build-arg su_username=[SUPER USER USERNAME] \
  --build-arg su_password=[SUPER USER PASSWORD] \
  --build-arg su_email=[SUPER USER EMAIL] \
  --build-arg smtp=[SMTP CONNECTION] \
  --build-arg front=[FRONT DOMAIN] \
  --build-arg back=[BACK DOMAIN] \
  --build-arg sso_url=[SSO URL] \
  --build-arg sso_connect_id=[SSO CONNECT ID] \
  --build-arg sso_connect_secret=[SSO CONNECT SECRET] \
  --build-arg sso_callback=[SSO CALLBACK] \
  --build-arg client_id=[CLIENT ID] \
  --build-arg client_secret=[CLIENT SECRET] \
  -t pialab
```

Lancer le conteneur piaLab :

```
docker run -p 8000:80 -p 4200:4200 -t -d \
  --restart always \
  --name pialab \
  --hostname pialab \
  --mount source=pialab_db,target=/var/lib/postgresql \
  pialab
```

Il est alors possible de se connecter au _back_ (cf. plus bas : Exposition du système) pour
finaliser la configuration :

* créer une structure
* modifier le super utilisateur pour lui affecter l'application et la structure
* ajouter des utilisateurs


## Montée en version du système

**Attention** : avant de lancer la procédure de montée en version, assurez-vous d'avoir bien pris
note des valeurs des clés de l'application **[CLIENT ID]** et **[CLIENT SECRET]**.

Pour une montée en version du système, suivre les étapes suivantes
en étant positionné dans le répertoire dédié :

* `cd pialab`

S'assurer d'avoir la dernière version du code de déploiement :

* `git pull`

Stopper l'exécution en cours et supprimer le conteneur, et supprimer le code source du système :   

* `docker stop pialab`
* `docker rm pialab`
* `docker container prune`
* `docker image prune`
* `rm -Rf ./back`
* `rm -Rf ./front`

Obtenir le code du système (_front_ et _back_) en veillant à bien préciser le nom de la branche
transmise par l'équipe Pialab :

* obtenir le code _front_   
  ```
  git clone ssh://git@git.3035.app:2222/pialab/front.git \
    --single-branch \
    --depth 1 \
    --branch [BRANCH RELEASE]
  ```
* obtenir le code _back_   
  ```
  git clone ssh://git@git.3035.app:2222/pialab/back.git \
    --single-branch \
    --depth 1 \
    --branch [BRANCH RELEASE]
  ```

Construire l'image du conteneur Docker, en veillant bien à positionner les variables de
construction :

```
docker build . \
  --build-arg tenant=[TENANT CODE] \
  --build-arg su_username=[SUPER USER USERNAME] \
  --build-arg su_password=[SUPER USER PASSWORD] \
  --build-arg su_email=[SUPER USER EMAIL] \
  --build-arg smtp=[SMTP CONNECTION] \
  --build-arg front=[FRONT DOMAIN] \
  --build-arg back=[BACK DOMAIN] \
  --build-arg sso_url=[SSO URL] \
  --build-arg sso_connect_id=[SSO CONNECT ID] \
  --build-arg sso_connect_secret=[SSO CONNECT SECRET] \
  --build-arg sso_callback=[SSO CALLBACK] \
  --build-arg client_id=[CLIENT ID] \
  --build-arg client_secret=[CLIENT SECRET] \
  -t pialab \
  --no-cache=true
```

Lancer le conteneur piaLab :

```
docker run -p 8000:80 -p 4200:4200 -t -d \
  --restart always \
  --name pialab \
  --hostname pialab \
  --mount source=pialab_db,target=/var/lib/postgresql \
  pialab
```


## Exposition du système

La composante _back_ du système Pialab écoute sur le port 8000, donc l'URL http://localhost:8000.

La composante _front_ du système Pialab écoute sur le port 4200, donc l'URL http://localhost:4200.

Le déploiement peut être complété par l'installation d'un serveur web sur la machine hôte,
configuré avec deux "virtual host proxy" respectivement pour les composantes _back_ et _front_.

Exemple de configuration avec le serveur web **Nginx**, le domaine principal **mondomaine.tld**, et
les sous-domaines **pialab** et **admin.pialab** respectivement pour le _front_ et le _back_ :

```
server {
    listen 80;
    server_name admin.pialab.mondomaine.tld;

    gzip on;

    # Proxy pass to docker
    location / {
        expires -1;
        proxy_pass_header Server;
        proxy_redirect off;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Scheme $scheme;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_connect_timeout 10;
        proxy_read_timeout 100;
        proxy_pass http://localhost:8000; # Docker
    }
}

server {
    listen 80;
    server_name pialab.mondomaine.tld;

    gzip on;

    # Proxy pass to docker
    location / {
        expires -1;
        proxy_pass_header Server;
        proxy_redirect off;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Scheme $scheme;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_connect_timeout 10;
        proxy_read_timeout 100;
        proxy_pass http://localhost:4200; # Docker
    }
}
```

## Sauvegarde des données

La base de données Pialab, au format PostgreSQL, est montée sur un volume Docker nommé
**pialab_db**.

Les données sont donc conservées sur la machine hôte même si le conteneur pialab est détruit (lors
d'une montée en version par exemple).

Il est conseillé d'effectuer une sauvegarde régulière du volume (se reporter à la documentation
Docker).
