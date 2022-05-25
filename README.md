# PIALAB - Procédure de déploiement en production

## Prérequis

Les composantes systèmes suivantes doivent être installées sur la machine hôte :

* git
* docker

De plus vous devez avoir un accès SSH valide au dépôt Git de PiaLab ; consulter l'équipe PiaLab
en cas de doute ou pour transmettre votre clé publique.

Vous devez par ailleurs avoir à disposition les informations suivantes, qui seront nécessaires au
cours du déploiement :

* **[TENANT CODE]** :   
  votre code client, transmis par l'équipe PiaLab
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


## Déploiement initial

Lors du déploiement initial, il est nécessaire d'obtenir le code du présent projet (PiaLab Deploy)
dans un répertoire racine (ici `pialab`) :

```
git clone ssh://git@git.pialab.io:2222/pialab/deploy.git \
  --single-branch \
  --depth 1 \
  --branch master
  pialab
```

En étant positionné dans le répertoire racine, obtenir le code du système (_front_ et _back_) en
veillant à bien préciser le nom de la branche transmise par l'équipe Pialab :

* `cd pialab`
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
    --url http://localhost:4200
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
* `rm -Rf ./back`
* `rm -Rf ./front`

Obtenir le code du système (_front_ et _back_) en veillant à bien préciser le nom de la branche
transmise par l'équipe Pialab :

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

Il est conseillé d'effectuer une sauvegarde régulière du volume (se reporter à la documentation
Docker).
