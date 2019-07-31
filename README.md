# Marello Monolithic Development Repository

This monolithic repository is used by the product development team as the main source code repository ("upstream repository") for all development activities. It contains the source code of all products and applications. The code changes and the new code are frequently distributed to the individual application repositories ("downstream repositories"). Occasionally, code changes are accepted back from the downstream repositories (e.g. pull requests from the community get tested in the mono repo on the version the PR has been created for).

## Repository Structure

The monolithic repository contains codes of individual packages, applications and environments: 

- applications - an application is a Symfony application that contains referenceres to all required package dependencies.
In order to avoid duplication of dependencies, they are handled with 
[path](https://getcomposer.org/doc/05-repositories.md#path) repository type in dev.json files.
- package - a package is a group of related functional modules that are used primarily together in a certain application.
- environments - basic docker setup for development purposes which includes docker-compose files and build files for applications to run

## Installation and Initialization

* [Install composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx) globally 
* Clone repository to the local environment:
```bash
git clone https://github.com/marellocommerce/development-mono-repository.git
```
* Go to the cloned repository folder:
```bash
cd development-mono-repository
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
COMPOSER=dev.json composer install --working-dir=applications/marello-application
```
* Install application via web or command line interface or use composer script in dev.json 'marello-reset'
```bash
COMPOSER=dev.json COMPOSER_PROCESS_TIMEOUT=3000 composer marello-reset --working-dir=applications/marello-application
```
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

## Release manager experience

* Start at #installation and initialization
* Add / make sure remote repositories have been added to main repo
* Checkout new branch if necessary (create from develop when it's a new version otherwise create from maintenance branch if available)
* Test application(s); run tests and try install of application and update of existing application
* If tests are all ok, merge back into develop and master branch
* Push branches (develop & master) to remote repo (mono repository)
* Check versions in the composer.json files before pushing to the individual downstream repositories
* Git subtree push to the individual downstream repositories to the corresponding branch (needs to be done for all individual downstream repositories)

**NOTE**: subtree push to the packages first since they are needed to create the composer lock files for the applications
```bash
git subtree push  --prefix <package or application directory in mono-repo> <remote repository> <remote branch> --squash
```
* Create tags on the remote repository / add release notes, from the correct branch with the correct new tag
* Create maintenance branch for released version (if it's is a new version)
* Repeat previous steps for applications and generate lock files
* Push new maintenance branch to remote of the mono repository

**Pushing to individual downstream repo's master branches, make sure you've pulled from the downstream repo's first** 
git push marello $(git subtree split --prefix=package/marello --onto=marello/master):master
git push marello-enterprise $(git subtree split --prefix=package/marello-enterprise --onto=marello-enterprise/master):master
git push marello-subscriptions $(git subtree split --prefix=package/marello-subscriptions --onto=marello-subscriptions/master):master



## Checking PR's
* Create branch locally based of the branch on which the PR is based
* Git subtree pull into the correct package or application from forked repo or remote repo 
```bash
git subtree pull --prefix <package or application> <remote repo> <remote branch> --squash
``` 
* Review changes
* Run tests against changes
* Merge into maintenance or master branch
* Push the changes to downstream repositories
```bash
git subtree push --prefix <package or application> <remote repo> <remote branch> --squash
``` 