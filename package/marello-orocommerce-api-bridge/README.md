Marello OroCommerce Bridge Api
========================

Package that contains bundles related to the extension of OroCommerce's API resources in order to integrate with Marello.
This package should be present on the OroCommerce instance whenever integrating with Marello either in a single instance or as separate instances.


Requirements
------------

This package requires you to have OroCommerce 1.5.x or above installed, with the exception of 3.0 version, in order to extend the API Data resources

Installation instructions
------------
### Using Composer

If you don't have Composer yet, download it and follow the instructions on
https://getcomposer.org/ or just run the following command:

```bash
    curl -s https://getcomposer.org/installer | php
```

If you're using Composer to install the Marello OroCommerce Api Bridge, you will need to add the package as a dependency in the composer.json.
In order to add the dependency the following command should be executed from the (OroCommerce/Marello)installation directory: 
```bash
    php composer.phar require marellocommerce/marello-orocommerce-api-bridge
```

After adding the Marello OroCommerce Api Bridge as an new dependency, you should update in order to get the latest versions and updating the composer.lock file

```bash
    php composer.phar update
```