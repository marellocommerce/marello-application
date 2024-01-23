Marello Enterprise
========================

What is Marello?
-----------

Marello Unified Commerce Management provides capabilities to meet and exceed rising expectations in commerce. Add and manage any sales channel, gain real-time insight into your B2C and B2B orders, inventory, fulfillment, customers and more. A unique unified experience allows shoppers to buy anywhere, fulfill anywhere, and return anywhere with one piece of software, one single version of the truth.

Installation
------------

This package requires an application to run it.
Please check the installation instructions in [marello-application-enterprise repository][1]

Use as dependency in composer
------------

```yaml
    "require": {
        "marellocommerce/marello-enterprise": "dev-master"
    }
```

In addition to adding it as dependency in composer, you might need to add an additional repository to get the enterprise version.
```yaml
    "repositories": {
        ...
        "marello-enterprise": {
            "type": "vcs",
            "url": "https://github.com/marellocommerce/marello-enterprise.git"
        }
    }
```

Run unit tests
--------------

Please make sure you have at least phpunit 9.5 or above.
To run unit tests of any bundles:

```bash
phpunit
```

[1]: https://github.com/marellocommerce/marello-application-ee