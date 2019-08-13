# Registering new Product types

Developers are able to create new product types of their liking. By default Marello offers a 'simple' product type as a default.
In order to register a new Product type, developers should create a service for their new product type as follows:

```yaml
    marello_product.product_type.simple:
        class: 'Marello\Bundle\ProductBundle\Model\ProductType'
        arguments:
            - name: 'simple'
              label: 'Simple'
              attribute_family_code: 'marello_default'
        tags:
            - { name: marello_product.product_type }
```

Above configuration will allow you to create a ProductType with a name, label and a attribute family code.
The attribute family code will have the ProductType attached to it and will load this attribute family when selecting the type when creating a Product.

The class used for the service can be either the default ProductType like shown above or developers can use their own
by creating a class which could either extend the `Marello\Bundle\ProductBundle\Model\ProductType` model class or implement the `Marello\Bundle\ProductBundle\Model\ProductTypeInterface`
