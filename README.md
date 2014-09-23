bfw-sql
=
Module SQL pour BFW

[![Build Status](https://travis-ci.org/bulton-fr/bfw-sql.svg?branch=1.0)](https://travis-ci.org/bulton-fr/bfw-sql) [![Coverage Status](https://coveralls.io/repos/bulton-fr/bfw-sql/badge.png?branch=1.0)](https://coveralls.io/r/bulton-fr/bfw-sql?branch=1.0) [![Dependency Status](https://www.versioneye.com/user/projects/5413ea429e162200400000bb/badge.svg?style=flat)](https://www.versioneye.com/user/projects/5413ea429e162200400000bb)

[![Latest Stable Version](https://poser.pugx.org/bulton-fr/bfw-sql/v/stable.svg)](https://packagist.org/packages/bulton-fr/bfw-sql) [![Latest Unstable Version](https://poser.pugx.org/bulton-fr/bfw-sql/v/unstable.svg)](https://packagist.org/packages/bulton-fr/bfw-sql) [![License](https://poser.pugx.org/bulton-fr/bfw-sql/license.svg)](https://packagist.org/packages/bulton-fr/bfw-sql)


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
./vendor/bin/bfw_loadModules
```
