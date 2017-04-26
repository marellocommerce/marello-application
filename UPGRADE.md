UPGRADE NOTES
=======================

### Recommended upgrade sequence

  * Pull changes from the repository
```bash
git pull
git checkout <VERSION TO UPGRADE>
```
  * Upgrade composer dependency
```bash
php composer.phar install --prefer-dist
```
  * Remove old caches and assets
```bash
rm -rf app/cache/*
rm -rf web/js/*
rm -rf web/css/*
```
  * Upgrade platform
```bash
php app/console oro:platform:update --env=prod --force
```

***Note***
If you happen to upgrade from the Marello Application < 1.0.0-RC release, please be advised that there has been a platform update in Marello Application 1.0.0-RC.
If you have an active install already < 1.0.0-RC you need to run the following command:
```bash
composer global require "fxp/composer-asset-plugin:~1.2"
```

This prevents errors when running a ``composer update`` since OroPlatform 2.0.0 requires the asset plugin to be installed globally.
This issue has been raised in [issue #1](https://github.com/marellocommerce/marello-application/issues/1) in the Marello repository and [issue #557](https://github.com/orocrm/platform/issues/557) in the Platform repository

Read more on updating the platform from 1.10.x to 2.0.0 in the [Github repository of OroCRM UPGRADE-2.0](https://github.com/orocrm/crm/blob/master/UPGRADE-2.0.md)