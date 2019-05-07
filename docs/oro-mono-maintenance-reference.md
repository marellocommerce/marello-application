# Maintenance

This repository is using [git-subtree](https://github.com/git/git/blob/master/contrib/subtree/git-subtree.txt)
to synchronize the source code with individual downstream repsoitories.

The maintenance cycle includes a few typical tasks:

### Add a new subtree

If you would like to add new downstream repository, you should add a new record to the configuration file in
[configuration.yml](./tool/src/Oro/Cli/Command/Repository/configuration.yml) and run the following command:

```bash
php tool/console repository:sync REPO_NAME
```

### Accept changes from an individual downstream repository into the monolithic

In order to update a subtree in the monolithic repository with the new code from an individual downstream repository,
run the following command:

```bash
php tool/console repository:sync
```

### Send changes from the monolithic into an individual downstream repository

In order to send new code from the monolithic repository into an individual downstream repository,
run the following command:

```bash
php tool/console repository:sync --subtree-push
```

*Note:* please pay attention to the output, produced by the repository:sync command output:
* If a conflict occurs during the subtree merge you must resolve it and run the command again.
* If you see **There are untracked files in the working tree, to continue please clean the working tree.
Use "git status" command to see details.** in the output, it indicates that you have local 
changes that should be committed before executing the command.

### Accept changes from a specific branch of the downstream repository

In order to update a subtree in the monolithic repository with the new code from a specific branch 
of an invidual downstream repository, run the following command:

```bash
php tool/console repository:sync --branch=some-branch --subtree-pull
```

*Note:* The specified branch will be created in an individual downstream repository if it doesn't exist there yet

### Send changes into a branch of an individual downstream repository

In order to send the new code from the monolithic repository into a specific branch of an individual downstream
repository, run the following command:

```bash
php tool/console repository:sync --branch=some-branch --subtree-push
```

*Note:* The specified branch will be created in an individual downstream repository if it doesn't exist there yet

### Check branches accross multiple repositories

In order to get a list of the repositories where the specified branch exists, run the following command:

```bash
php tool/console repository:sync --branch=some-branch --dry-run
```

*Note:* please pay attention to the output, produced by the repository:sync command output:
* If a conflict occurs during the subtree merge you must resolve it and run the command again.
* If you see **There are untracked files in the working tree, to continue please clean the working tree.
Use "git status" command to see details.** in the output, it indicates that you have local 
changes that should be committed before executing the command.

# Creating new maintenance branch

* Add branch configuration to [configuration.yml](./tool/src/Oro/Cli/Command/Repository/configuration.yml)
```
branches:
    maintenance/crm-enterprise-1.11: # maintenance branch in dev repository
        application/crm-enterprise: '1.11'
        application/crm: '1.9'
        application/platform: '1.9'
        package/platform: '1.9'               # branch '1.9' from package/platform (git@github.com:laboro/platform.git) used
        package/platform-enterprise: '1.11'   # branch '1.11' from package/platform-enterprise (git@github.com:laboro/platform-enterprise.git) used
        package/crm: '1.9'                    # branch '1.9' from package/crm (git@github.com:laboro/crm.git) used
        package/crm-enterprise: '1.11'        # branch '1.11' from package/crm-enterprise (git@github.com:laboro/crm-enterprise.git) used
        package/dotmailer: '1.9'
        package/ldap: '1.11'
        package/mailchimp: '1.9'
        package/magento-abandoned-cart: '1.9'
        package/google-hangout: '1.9'
        package/serialized-fields: '1.9'
        package/demo-data: '1.11'
        package/zendesk: '1.9'
        package/magento-contact-us: '1.9'
```

* Create new maintenance branch form master
```
git checkout -b maintenance/crm-enterprise-1.11
```

# Creating new maintenance branch from previous source versions
* Add branch configuration to [configuration.yml](./tool/src/Oro/Cli/Command/Repository/configuration.yml)
```
branches:
    maintenance/crm-enterprise-1.11: # maintenance branch in dev repository
        application/crm-enterprise: '1.11'
        application/crm: '1.9'
        application/platform: '1.9'
        package/platform: '1.9'               # branch '1.9' from package/platform (git@github.com:laboro/platform.git) used
        package/platform-enterprise: '1.11'   # branch '1.11' from package/platform-enterprise (git@github.com:laboro/platform-enterprise.git) used
        package/crm: '1.9'                    # branch '1.9' from package/crm (git@github.com:laboro/crm.git) used
        package/crm-enterprise: '1.11'        # branch '1.11' from package/crm-enterprise (git@github.com:laboro/crm-enterprise.git) used
        package/dotmailer: '1.9'
        package/ldap: '1.11'
        package/mailchimp: '1.9'
        package/magento-abandoned-cart: '1.9'
        package/google-hangout: '1.9'
        package/serialized-fields: '1.9'
        package/demo-data: '1.11'
        package/zendesk: '1.9'
        package/magento-contact-us: '1.9'
```

* Create new maintenance branch form master
```
git checkout -b maintenance/crm-enterprise-1.11
```

* Reset changes to first repository commit
```
git reset --hard 17e0be67fedeea1d6a36c63e36bca900366589c5
```

* Copy tools to your branch
```
git checkout master -- .idea .gitignore .travis.sh .travis.yml travis.php.ini tool
```

* Update build scripts if necessary
* Commit changes
* Run branch command, it will import new subtree using branches from upstreams according to configuration
```
php tool/console repository:branch-sync --two-way --force --subtree-add
```

* Update composer.json (for application and packages) and add composer.lock to repository (applications only)
```
git checkout master -- application/crm-enterprise/phpunit.xml.dist
composer install --working-dir=application/crm-enterprise
git add -f application/crm-enterprise/composer.lock
```

## Required files changes are
* remove composer.lock from `application/crm-enterprise/.gitignore`
* replace specific packages versions listed in package directory in `application/crm-enterprise/composer.json` with `"oro/crm": "self.version"`
* package `package/crm/composer.json` should use same `"oro/platform": "self.version"` to point internal versions
* add new repository with package type to `application/crm-enterprise/composer.json`
```
  "repositories": [
    {
      "type": "path",
      "url": "../../package/*"
    }
  ]
```
