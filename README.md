# PIALAB - Procédure de déploiement en production

## Déploiement initial

Lors du déploiement initial, il est nécessaire de créer un répertoire dédié à l'environnement
pialab :

* `mkdir pialab`


## Déploiement et montée en version du système

Lors du déploiement initial, ou pour une montée en version du système, suivre les étapes suivantes,
en étant positionné dans le répertoire dédié :

* `cd pialab`

Dans le cas d'une montée en version, stopper l'exécution en cours et supprimer le conteneur :   

* obtenir l'ID du conteneur Pialab :   
  `docker ps`
* stopper le conteneur :   
  `docker stop [CONTAINER ID]`
* supprimer le conteneur :   
  `docker rm [CONTAINER ID]`

Obtenir le code à jour du projet (_front_ et _back_) en veillant à bien préciser le nom de la
branche transmise par l'équipe Pialab :

* obtenir le code _front_   
  ```
  git clone ssh://git@git.pialab.io:2222/pialab/front.git \
  --single-branch \
  --depth 1 \
  --branch [BRANCH RELEASE]
  ```
* obtenir le code _back_   
  ```
  git clone ssh://git@git.pialab.io:2222/pialab/back.git \
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
  -t pialab
```

en test...

```
docker build . --build-arg su_username=superuser --build-arg su_password=password --build-arg su_email=superuser@domain.tld -t pialab
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

en test...

```
docker run -p 8000:80 -p 4200:4200 -t --name pialab --hostname pialab --mount source=pialab_db,target=/var/lib/postgresql pialab
```

## Exposition du système

La composante _back_ du système Pialab écoute sur le port 8000, donc l'URL http://localhost:8000.

La composante _front_ du système Pialab écoute sur le port 4200, donc l'URL http://localhost:4200.

Le déploiement peut être complété par l'installation d'un serveur web sur la machine hôte,
configuré avec deux "virtual host proxy" respectivement pour les composantes _back_ et _front_.


## Sauvegarde des données

La base de données Pialab, au format PostgreSQL, est montée sur un volume Docker nommé
**pialab_db**.

Les données sont donc conservées sur la machine hôte même si le conteneur pialab est détruit (lors
d'une montée en version par exemple).

Il est conseillé d'effectuer une sauvegarde régulière du volume.
