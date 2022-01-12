Marello Unified Commerce Enterprise Application
==============================

Marello Unified Commerce Enterprise Application is the commercial version of Marello Application bundled with extended capabilities of OroPlatform Enterprise.
This document contains information on how to download, install, and start using Marello Unified Commerce Enterprise.

## Requirements

Marello Enterprise is a Symfony 4.4 based application with the following requirements:

* PHP 7.4.14 or above with command line interface
* PHP Extensions
    * ctype
    * curl
    * fileinfo
    * gd
    * intl (ICU library 4.4 and above)
    * json
    * mbstring
    * openssl
    * mysql
    * pcre
    * simplexml
    * tokenizer
    * xml
    * zip
    * imap
    * soap
    * bcmath
    * ldap  
    * pgsql
* MySQL 8.0 or PostgreSQL >=12.5 (optional)
* Elasticsearch >=7.7, <8.0 (optional)
* RabbitMQ 3.8.x (optional)

## Installation instructions

As both Symfony and Marello Enterprise use [Composer][1] to manage their dependencies, this is the recommended way to install Marello.

- Clone Marello application repository:

```bash
git clone -b x.y.z https://github.com/marellocommerce/marello-application-ee.git
```

where x.y.z is the latest [release tag](https://github.com/marellocommerce/marello-application-ee/releases) or use the latest master:

```bash
    git clone https://github.com/marellocommerce/marello-application-ee.git
```

- Install [Composer][1] globally following the official Composer installation documentation

- Make sure that you have [NodeJS][4] >=12.0, <15.0 installed

- Install Marello dependencies with composer. If installation process seems too slow you can use `--prefer-dist` option. Go to marello-application folder and run composer installation:

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
php bin/console oro:install --env prod
```

**Note** If the installation process times out, add the `--timeout=0` argument to the oro:install command.

- Enable WebSockets messaging

```bash
php bin/console gos:websocket:server --env prod
```

- Configure crontab or scheduled tasks execution to run the command below every minute:

```bash
php bin/console oro:cron --env prod

```
- Launch the message queue processing:
```bash
php bin/console oro:message-queue:consume --env=prod
```
**Note** We do recommend to use a supervisor for running the ``oro:message-queue:consume`` command. This will make sure that the command and
the consumer will run all the time. This has become important for every Oro Platform based application since a lot of background tasks depend
 on the consumer to run. For more information about configuration and what supervisor can do for you can either be found in the [Oro(CRM) docs][6] or the
 [site of Supervisord][7].
 
**Note:** ``bin/console`` is a path from project root folder. Please make sure you are using full path for crontab configuration or if you running console command from other location.

## Installation notes

Installed PHP Accelerators must be compatible with Symfony and Doctrine (support DOCBLOCKs)

Note that the port used in Websocket must be open in firewall for outgoing/incoming connections

Using MySQL 8.0.x on HDD is potentially risky because of performance issues

Recommended configuration for this case:

    innodb_file_per_table = 0

And ensure that timeout has default value

    wait_timeout = 28800

See [Oro's recommendation regarding optimization][2] and [Optimizing InnoDB Disk I/O][3] for more

## PostgreSQL installation notes

You need to load `uuid-ossp` extension for proper doctrine's `guid` type handling.
Log into database and run sql query:

```
CREATE EXTENSION "uuid-ossp";
```

## Web Server Configuration

The Marello Enterprise application is based on the Symfony standard application so web server configuration recommendations are the [same][5].

## Package Manager Configuration

Github OAuth token should be configured in package manager settings

[1]:  https://getcomposer.org/
[2]:  https://doc.oroinc.com/backend/setup/system-requirements/database-optimization/
[3]:  https://dev.mysql.com/doc/refman/8.0/en/optimizing-innodb-diskio.html
[4]:  https://github.com/joyent/node/wiki/Installing-Node.js-via-package-manager
[5]:  https://symfony.com/doc/4.4/setup/web_server_configuration.html
[6]:  https://oroinc.com/orocrm/doc/current/install-upgrade/installation-quick-start-dev/crm#step-4-post-installation-environment-configuration
[7]:  https://supervisord.org/
