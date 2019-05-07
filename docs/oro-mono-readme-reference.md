# Oro Monolithic Development Repository

Oro, Inc. team works on multiple different initiatives, products, projects and applications. Some changes may have
global impact (for example, a change may affect all products), so the source code organization should allow to perform them in an efficient way.

This monolithic repository is used by the product development team as the main source code repository ("upstream repository") for all development activities. It contains the source code of all products and applications. The code changes and the new code are frequently distributed to the invididual application repositories ("downstream repositories"). Occasionally, code changes are accepted back from the downstream repositories (e.g. pull requests from the community may first get into individual downstream repositories, and then they are accepted to this upstream monolithic repository).

Branch | Travis | SensioLabsInsight
--- | --- | ---
master | [![Build Status](https://travis-ci.com/laboro/dev.svg?token=xpj6qKNzq4qGqYEzx4Vm&branch=master)](https://travis-ci.com/laboro/dev) | [![SensioLabsInsight](https://insight.sensiolabs.com/projects/72e37cec-75b7-4b2b-bc8a-72544beaa446/mini.png)](https://insight.sensiolabs.com/projects/72e37cec-75b7-4b2b-bc8a-72544beaa446)
maintenance/2.0 | [![Build Status](https://travis-ci.com/laboro/dev.svg?token=xpj6qKNzq4qGqYEzx4Vm&branch=maintenance/2.0)](https://travis-ci.com/laboro/dev) |
maintenance/crm-enterprise-1.12 | [![Build Status](https://travis-ci.com/laboro/dev.svg?token=xpj6qKNzq4qGqYEzx4Vm&branch=maintenance/crm-enterprise-1.12)](https://travis-ci.com/laboro/dev) |
maintenance/crm-enterprise-1.11 | [![Build Status](https://travis-ci.com/laboro/dev.svg?token=xpj6qKNzq4qGqYEzx4Vm&branch=maintenance/crm-enterprise-1.11)](https://travis-ci.com/laboro/dev) |
maintenance/crm-enterprise-1.10 | [![Build Status](https://travis-ci.com/laboro/dev.svg?token=ZQTVuDeae7tzGmhppfq9&branch=maintenance/crm-enterprise-1.10)](https://travis-ci.com/laboro/dev)

## Repository Structure

The monolithic repository contains codes of individual packages, applications, documentation and additional tools: 

- application - an application is a Symfony application that contains referenceres to all required package dependencies.
In order to avoid duplication of dependencies, they are handled with 
[path](https://getcomposer.org/doc/05-repositories.md#path) repository type in composer.json files.
- documentation - documentation for all products.
- package - a package is a group of related functional modules that are used primarily together in a certain application.
- tool - various tools necessary for repository and code maintenance, and IDE integration.

## Installation and Initialization

* [Install composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx) globally 
* Clone repository to the local environment:
```bash
git clone git@github.com:laboro/dev.git
```
* Go to the cloned repository folder:
```bash
cd dev
```
* Make sure you have installed [fxpio/composer-asset-plugin](https://github.com/fxpio/composer-asset-plugin/blob/master/Resources/doc/index.md) globally (per user). If not, install:
```bash
composer self-update
composer global require "fxp/composer-asset-plugin"
```
* Install tools in `tool` folder:
```bash
composer install --working-dir=tool
```
* Install all dependencies for the application you are going to work on, for example:
```bash
COMPOSER=dev.json composer install --working-dir=application/crm
```
* Install application via web or command line interface
* Repeat the previous two steps (install application dependencies and run application installer) for as many applications as necessary.

## Development Experience

* Enable PHPStorm configuration for the application you are going to work on:
```bash
php tool/console phpstorm:init-application {application_name}
```
* Create a feature branch
* Perform code changes and testing
* Push your branch to the remote repository and create a pull request

*Note:* to see all existing applications run `phpstorm:init-application` without parameters:
```bash
php tool/console phpstorm:init-application
```

### Code style checks

```
php application/platform/bin/phpcs -p --encoding=utf-8 --extensions=php --standard=package/platform/build/phpcs.xml package/
```

```
php application/platform/bin/phpmd --suffixes php package/ text package/platform/build/phpmd.xml
```

### Semantic Versioning

Please follow the [php-semver-checker](package/platform/build/php-semver-checker.yml.dist) configuration file 
to make sure that your changes are satisfy the requirements of the target branch.

All changes in Pull Request will be qualified according to configuration mentioned above.

- If all changes will qualified as PATCH - *PR also will qualified as PATCH*
- If one or more changes will qualified as MINOR and other changes as PATCH - *PR will qualified as MINOR*
- If any changes will qualified as MAJOR regardless other changes - *PR will qualified as MAJOR*

For maintenance branches like ```maintenance/2.0``` only PATCH changes is allowed.

### IDE

PHPStorm is the recommended IDE for Oro projects. The following plugins may help to improve developer experience:

 * [Php Inspections (EA Extended)](https://plugins.jetbrains.com/plugin/7622) - This plugin is a Static Code Analysis tool for PHP (aka inspections in JetBrains products).
 * [PHP Annotations](https://plugins.jetbrains.com/plugin/7320) - Provides php annotation support for PhpStorm and IntelliJ
 * [PHP inheritDoc helper](https://plugins.jetbrains.com/plugin/7656) - Folds inheritDoc docblocks and shows the inherited text instead.
 * [Symfony2 Plugin](https://plugins.jetbrains.com/plugin/7219) - PhpStorm plugin to detect ContainerInterface::get result type ... and that does many other things now :)
 * [Oro PHPStorm Plugin](https://plugins.jetbrains.com/plugin/8449) - Oro PHPStorm Plugin Plugin for the PHPStorm that will help to increase the development speed on the projects based on the OroPlatform.
 
## Maintenance

See [MAINTENANCE.md](./MAINTENANCE.md) for details.
