Marello Unified Commerce Application
==============================

Marello Unified Commerce Management provides capabilities to meet and exceed rising expectations in commerce. Add and manage any sales channel, gain real-time insight into your B2C and B2B orders, inventory, fulfillment, customers and more. A unique unified experience allows shoppers to buy anywhere, fulfill anywhere, and return anywhere with one piece of software, one single version of the truth.

This document contains information on how to download, install, and start
using Marello.

## Requirements

Marello is a Symfony 6.4 based application with the following requirements:

* PHP 8.3 or above with command line interface
* PHP Extensions
    * ctype
    * curl
    * fileinfo
    * gd
    * intl (ICU library 4.4 and above)
    * json
    * mbstring
    * sodium
    * openssl
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
* PostgreSQL 16.1

## Installation instructions

As both Symfony and Marello use [Composer][1] to manage their dependencies, this is the recommended way to install Marello.

- Clone Marello application repository:

```bash
git clone -b x.y.z https://github.com/marellocommerce/marello-application.git
```

where x.y.z is the latest [release tag](https://github.com/marellocommerce/marello-application/releases) or use the latest master:

```bash
    git clone https://github.com/marellocommerce/marello-application.git
```

- Install [Composer][1] globally following the official Composer installation documentation

- Make sure that you have [Node.js][4] >=20.10.0, <21 installed and NPM >10

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

Additional performance configurations and optimizations can be found in the [Oro docs][2]

## Web Server Configuration

The Marello application is based on the Symfony standard application so web server configuration recommendations are the [same][5].

## Package Manager Configuration

Github OAuth token should be configured in package manager settings

[1]:  https://getcomposer.org/
[2]:  https://doc.oroinc.com/backend/setup/system-requirements/performance-optimization/
[4]:  https://nodejs.org/en/download/package-manager
[5]:  https://symfony.com/doc/5.4/setup/web_server_configuration.html
[6]:  https://doc.oroinc.com/backend/setup/dev-environment/community-edition/
[7]:  https://supervisord.org/
