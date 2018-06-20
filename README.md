OroMagentoBundle
===================

Bundle provides integration with Magento e-commerce solution.

### Table of contents

* [B2C Workflows](./Resources/doc/reference/workflows.md)
* [Automatic accounts discovery](./Resources/doc/reference/account_discovery.md)
* [EAV attributes support](./Resources/doc/reference/eav_attributes_support.md)

### Notes

In case of using this bundle OroMagentoBundle without OroBridge extension change_status_at date for
NewsletterSubscriber will be empty during import because of bug on Magento side


#### Product integration:
- name
- sku
- taxcode
- price
- weight
- status

#### Inventory:
- qty


#### Price:
- currency
- value

### Product Category Assignment / Linking
- 


#### QUESTIONS:
- Should marello over-write data on the store ? create / update


#### TODO
- Add internal reference based on entity ID to detect where the sync is.
- 2 way sync
- Move the bundle out of the core repo
- Investigate possibilities to match categorries
