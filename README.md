bfw-sql
=
Module SQL pour BFW


---

__Installation :__

Il est recommandé d'utiliser composer pour installer le framework :

Pour récupérer composer:
```
curl -sS https://getcomposer.org/installer | php
```

Pour installer le framework, créez un fichier "composer.json" à la racine de votre projet, et ajoutez-y ceci:
```
{
    "require": {
        "bulton-fr/bfw-sql": "@stable"
    }
}
```

Enfin, pour lancer l'installation, 2 étapes sont nécessaires :

Récupérer le module via composer :
```
php composer.phar install
```
Via un utilitaire du framework BFW, créer un lien vers le module dans le dossier module du projet :
```
sh vendor/bin/bfw_loadModules
```
