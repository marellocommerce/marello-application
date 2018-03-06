Marello OroCommerce Enterprise Application
==============================

Marello OroCommerce Enterprise Application is combination of both OroCommerce and Marello Enterprise editions. 
This document contains information on how to download, install, and start
using Marello Enterprise.

## Requirements

Marello - OroCommerce Enterprise is a Symfony 2 based application with the following requirements:

* PHP 7.0 or above with command line interface
* PHP Extensions
    * GD
    * Mcrypt
    * JSON
    * ctype
    * Tokenizer
    * SimpleXML
    * PCRE
    * ICU
* MySQL 5.1 or above
* PostgreSQL 9.1 or above

## Installation instructions

As Symfony, OroCommerce and Marello Enterprise use [Composer][2] to manage their dependencies, this is the recommended way to install Marello Enterprise.

- Clone Marello Enterprise application repository:

```bash
git clone -b x.y.z https://github.com/marellocommerce/marello-application-ee.git
```

where x.y.z is the latest [release tag](https://github.com/marellocommerce/marello-application-ee/releases) or use the latest master:

```bash
git clone https://github.com/marellocommerce/marello-application-ee.git
```

- Install [Composer][3] globally following the official Composer [installation documentation][4]
and install [fxpio/composer-asset-plugin][5] plugin for it:

```bash
composer global require "fxp/composer-asset-plugin:~1.2"
```

- Make sure that you have [NodeJS][4] installed

- Install Marello Enterprise dependencies with composer. If installation process seems too slow you can use `--prefer-dist` option. Go to marello-application folder and run composer installation:

```bash
composer install --prefer-dist --no-dev
```

- Create the database with the name specified on previous step (default name is "marello_application").

- On some systems it might be necessary to temporarily increase memory_limit setting to 1 GB in php.ini configuration file for the duration of the installation process:
```bash
memory_limit=1024M
```

**Note:** After the installation is finished the memory_limit configuration can be changed back to the recommended value (512 MB or more).

- Install application and admin user with Installation Wizard by opening install.php in the browser or from CLI:

```bash  
php app/console oro:install --env prod
```

**Note** If the installation process times out, add the `--timeout=0` argument to the oro:install command.

- Enable WebSockets messaging

```bash
php app/console clank:server --env prod
```

- Configure crontab or scheduled tasks execution to run the command below every minute:

```bash
php app/console oro:cron --env prod

```
- Launch the message queue processing:
```bash
php app/console oro:message-queue:consume --env=prod
```
**Note** We do recommend to use a supervisor for running the ``oro:message-queue:consume`` command. This will make sure that the command and the consumer will run all the time. This has become important for every Oro Platform based application since a lot of background tasks depend on the consumer to run. For more information about configuration and what supervisor can do for you can either through the [Oro(CRM) docs][6] or the [site of Supervisord][7].
 
**Note:** ``app/console`` is a path from project root folder. Please make sure you are using full path for crontab configuration or if you running console command from other location.

## Installation notes

Installed PHP Accelerators must be compatible with Symfony and Doctrine (support DOCBLOCKs)

Note that the port used in Websocket must be open in firewall for outgoing/incoming connections

Using MySQL 5.6 on HDD is potentially risky because of performance issues

Recommended configuration for this case:

    innodb_file_per_table = 0

And ensure that timeout has default value

    wait_timeout = 28800

See [Optimizing InnoDB Disk I/O][3] for more

### Additional dependencies
The Marello OroCommerce combination has additional dependencies in order to function properly and create the integration between both applications.
These dependencies are:
* https://github.com/marellocommerce/marello-orocommerce-bridge, general integration bundle with the code of the connector
* https://github.com/marellocommerce/marello-orocommerce-api-bridge, extension of the OroCommerce API in order to add additional API endpoints for Marello to integrate

## PostgreSQL installation notes

You need to load `uuid-ossp` extension for proper doctrine's `guid` type handling.
Log into database and run sql query:

```bash
CREATE EXTENSION "uuid-ossp";
```

## Web Server Configuration

The Marello Enterprise application is based on the Symfony standard application so web server configuration recommendations are the [same][5].

### Opcache recommended configuration
```bash
;256Mb for php7
opcache.memory_consumption=256
opcache.max_accelerated_files=32531
opcache.interned_strings_buffer=32
```

Additional recommended configuration settings can be found on the [Symfony website](http://symfony.com/doc/2.8/performance.html)


## Package Manager Configuration

Github OAuth token should be configured in package manager settings

[1]:  http://symfony.com/doc/2.8/book/installation.html
[2]:  http://getcomposer.org/
[3]:  http://dev.mysql.com/doc/refman/5.6/en/optimizing-innodb-diskio.html
[4]:  https://github.com/joyent/node/wiki/Installing-Node.js-via-package-manager
[5]:  http://symfony.com/doc/2.8/cookbook/configuration/web_server_configuration.html
[6]:  https://www.orocrm.com/documentation/2.0/book/installation#activating-background-tasks
[7]:  http://supervisord.org/