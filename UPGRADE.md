UPGRADE NOTES
=======================

This upgrade sequence has been derived from the Oro(CRM/Commerce). More information can be found at https://oroinc.com/orocrm/doc/current/install-upgrade/upgrade[1]
for more details.

### Patch for 3.2.0
Trouble shooting entity extend issue when platform update runs, specifically in Marello version 3.2.0

If this error occurs:
The application update is not possible:
```
- The entities configuration has not applied schema changes for the following entities: Marello\Bundle\InvoiceBundle\Entity\Creditmemo, Marello\Bundle\InvoiceBundle\Entity\CreditmemoItem. Please update schema using "oro:entity-extend:update" CLI command (--dry-run option is available). Please note, that schema depends on source code and you may need to rollback to previous version of the source code.
```
Get patch version of Marello 3.2.1 to include a fix for the issue. To apply the fix,
Please:
- Stop the consumers
- Run bin/console oro:migration:load --force
- bin/console oro:entity-extend:update -vvv
- And lastly run bin/console oro:platform:update --force and bin/console c:c --env=prod


### Recommended upgrade sequence to Marello 3.0
To retrieve source code of a new version and upgrade your Marello instance, please execute the following steps:

  * Go to the Marello root folder and switch the application to the maintenance mode;

```bash
cd /path/to/application
php bin/console lexik:maintenance:lock --env=prod
```

  * Stop the cron tasks;
```bash
crontab -e -uwww-data
```
  * Comment this line:
```bash
*/1 * * * * /usr/bin/php /path/to/application/bin/console --env=prod oro:cron >> /dev/null
```

  * Stop all running consumers;
  * Create backups of your database and source code;
  * Pull changes from the repository;
  
####Update sequence to 3.0 additional step(s)
If you are going to upgrade from a Marello version lower than 2.2.x, please update to the latest 2.2.x version first!
This allows you to transition to 3.0 more easily and prevent issues during install. This step **is** necessary for a smooth transition during the changes made from the old Customer and new Customer

```bash
git pull
git checkout <VERSION TO UPGRADE>
```

If you have any customization or third party extensions installed, make sure that:
```bash
* your changes to "src/AppKernel.php" file are merged to the new file.
* your changes to "src/" folder are merged and it contains the custom files.
* your changes to "composer.json" file are merged to the new file.
* your changes to configuration files in "config/" folder are merged to the new files.
```

  * Upgrade composer dependency;
```bash
php composer.phar install --prefer-dist --no-dev
```

  * Remove old caches and assets;
```bash
rm -rf var/cache/*
rm -rf public/js/*
rm -rf public/css/*
```

  * Upgrade platform;
```bash
php bin/console oro:platform:update --env=prod --force
```

  * Remove the caches;
```
php bin/console cache:clear --env=prod
```

  * Enable cron;
```bash
crontab -e -uwww-data
```  
  * Uncomment this line:
```bash
*/1 * * * * /usr/bin/php /path/to/application/bin/console --env=prod oro:cron >> /dev/null
```

  * Switch your application back to normal mode from the maintenance mode;
```bash
php bin/console lexik:maintenance:unlock --env=prod
```

  * Start the consumers again
```bash
php bin/console oro:message-queue:consume --env=prod
```

**Note**
```
If PHP bytecode cache tools (e.g. opcache) are used, PHP-FPM (or Apache web server) should be restarted after the upgrade to flush cached bytecode from the previous installation.
```

[1]: https://oroinc.com/orocrm/doc/current/install-upgrade/upgrade