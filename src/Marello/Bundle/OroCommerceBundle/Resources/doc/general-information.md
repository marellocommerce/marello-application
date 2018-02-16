#General information.

eBay integration can be created only by user credentials which has account type 'Enterprise'.
Integration token can be taken here:

```code
https://developer.ebay.com/my/auth?index=0
expand "Get a User Token Here" -> select "Auth'n'Auth" -> click button -> follow redirect link -> sign in and click button "Agree" -> you will receive token
```

eBay bundle provides new integration type with possibilities:
- Create&Update&Delete Products
- Export TaxTable(for marketplaces which support TaxTable)
- Import Orders
- Updating Order status

Integration can be created on 'Integration' page:

```code
System -> Integrations -> Manage Integrations -> Create Integration
```

It is possible to create few different integrations with 'eBay' type.
- After integration will be saved, a new sales channel will be created based on this integration
- Every product which will be assignet to this sales channel will be synchronized with eBay (if this product is enabled and it has inventory quantity > 0)
- When will be created/updated/deleted TaxRule/TaxRate/TaxCode/TaxJurisdiction related to products assigned to this sales channel - will be done synchronization of all products assigned to this sales channel(so it is recommended to fill correct tax information before assigning products to sales channel based on integration )
- Orders import will be done only for products which exists on Marello side(will not be imported orders for products which will be created on eBay side directly)
- Order status will be exported to eBay when user will go by order workflow on Marello side
