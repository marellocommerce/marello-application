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
  * Disable APC, OpCache, other code accelerators
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

### IMPORTANT NOTE
Some of the new features introduced BC breaks. We are aware of the breaks and we are sorry for that. We had to introduce some of them in order to create a more solid base.
We are currently in the process of trying to collect all of the BC breaks and will try and publish them.