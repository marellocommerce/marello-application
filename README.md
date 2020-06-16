Marello Magento Integration
========================

Package that contains bundles related to the integration between Marello and Magento 2
Marello (Enterprise) contains the possibility to integrate with the Commerce platform Magento 2. This integration allows business owners to use their Magento online store as a SalesChannel in Marello (Enterprise).

Requirements
------------

The Marello Magento bundle requires Marello (EE) 3.0.x or above and having a Magento (Enterprise) 2.2.x or above installed, in order to function correctly. 

General information
------------

The integration between Marello (Enterprise) and Magento  (Enterprise) is using the Magento 2 API in order to perform synchronisations . 

The integration will allow business owners to synchronise:

* Orders;
* Products;
* Product Prices;
* Inventory;

Configuration
------------
In order to configure the integration, make sure you have setup the applications correctly. Please advice with your implementation partner to verify your setup, or see the technical overview below.

### Configuration prerequisites

* Integration setup in Magento which has the necessary resource access, https://devdocs.magento.com/guides/v2.3/get-started/authentication/gs-authentication-token.html

__Above prerequisites should be met before configuring your integration in Marello.__

### Integration Configuration

### Using Composer

If you don't have Composer yet, download it and follow the instructions on
https://getcomposer.org/ or just run the following command:

```bash
    curl -s https://getcomposer.org/installer | php
```

If you're using Composer to install the Marello Magento bundle, you will need to add the package as a dependency in the composer.json.
In order to add the dependency the following command should be executed from the (Marello)installation directory: 
```bash
    php composer.phar require marellocommerce/marello-magento2-bundle
```

After adding the Marello Magento bundle as an new dependency, you should update in order to get the latest versions and updating the composer.lock file

```bash
    php composer.phar update
```

The configuration for the integration should be done in the Marello Application. __If the setup is done with multiple instances, this is especially important!__
The integration can be created on the 'Integration' page and can be found in Marello in:
* _System → Integrations → Manage Integrations → Create Integration._
