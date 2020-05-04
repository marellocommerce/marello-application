MarelloMagentoBundle
===================

Bundle provides integration with Magento e-commerce solution.

### GENERAL INFO
The bundle will provide integration per Magento Website/Store to sync the following data-entities with Marello instance:

* Orders
* Products
* Product Prices
* Inventory
* Tax data
* Category linking / product catalog assignment

### Notes
Marello integration with Magento is documented by the ORO team using OROCRM, the instruction is completely the same except for minor details elaborated here:

* An extra Magento Extension is needed on top of the _OroCRM Bridge Magento Extenstion_ to provide Marello Catalog Codes.
    * https://github.com/marellocommerce/magento-marello-bridge
    * https://github.com/oroinc/magento-orocrm-bridge
* Similar integration between Magento and ORO can be found on this [link](https://oroinc.com/orocrm/doc/2.0/admin-guide/integrations/magento-channel-integration)

### Requirements
Marello Magento Bridge requires Marello CE 1.5.* or Marello EE 1.1.* and Magento 1.9.* installed in order to function correctly.

### USAGE

* CLI:

```
$ ./app/console oro:cron:integration:sync --help
Usage:
  oro:cron:integration:sync [options] [--] [<connector-parameters>]...

Arguments:
  connector-parameters                             Additional connector parameters array. Format - parameterKey=parameterValue

Options:
  -i, --integration[=INTEGRATION]                  If option exists sync will be performed for given integration id
  -h, --help                                       Display this help message
  -q, --quiet                                      Do not output any message
  -V, --version                                    Display this application version
      --ansi                                       Force ANSI output
      --no-ansi                                    Disable ANSI output
  -n, --no-interaction                             Do not ask any interactive question
  -s, --shell                                      Launch the shell.
      --process-isolation                          Launch commands from shell as a separate process.
  -e, --env=ENV                                    The Environment name. [default: "dev"]
      --no-debug                                   Switches off debug mode.
      --current-user=CURRENT-USER                  ID, username or email of the user that should be used as current user
      --current-organization=CURRENT-ORGANIZATION  ID or name of the organization that should be used as current organization
      --disabled-listeners=DISABLED-LISTENERS      Disable optional listeners, "all" to disable all listeners, command "oro:platform:optional-listeners" shows all listeners (multiple values allowed)
  -con, --connector[=CONNECTOR]                    If option exists sync will be performed for given connector name
  -v|vv|vvv, --verbose                             Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  Runs synchronization for integration
```

### Using Composer

If you don't have Composer yet, download it and follow the instructions on
https://getcomposer.org/ or just run the following command:

```bash
    curl -s https://getcomposer.org/installer | php
```

If you're using Composer to install the Marello Magento Integration, you will need to add the package as a dependency in the composer.json.
In order to add the dependency the following command should be executed from the (OroCommerce/Marello)installation directory: 

* Notice:
Where needed please add the repository in your composer.json:

```
    "marello-magento-bundle": {
        "type": "vcs",
        "url": "https://github.com/marellocommerce/marello-magento-bundle.git"
    }
```

```bash
    php -dmemory_limit=-1 $(which composer) require marellocommerce/marello-magento-bundle dev-master -vvv
```

* Admin GUI:
`System → Integrations → Manage Integrations `

### LICENCE
Marello

The Open Software License version 3.0

Copyright (c) 2015-2018, Madia B.V.

Full license is at: http://opensource.org/licenses/OSL-3.0


#### API REFERENCE
[Internal DOC API](./Resources/doc/api)
