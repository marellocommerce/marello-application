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
Read more on updating the platform from 1.10.x to 2.0.0 in the [Github repository of OroCRM UPGRADE-2.0][https://github.com/orocrm/crm/blob/master/UPGRADE-2.0.md]