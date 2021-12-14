# Read-Models Bundle for Symfony

[![GitHub Actions Build Status](https://img.shields.io/github/workflow/status/somnambulist-tech/read-models-bundle/tests?logo=github)](https://github.com/somnambulist-tech/read-models-bundle/actions?query=workflow%3Atests)
[![Issues](https://img.shields.io/github/issues/somnambulist-tech/read-models-bundle?logo=github)](https://github.com/somnambulist-tech/read-models-bundle/issues)
[![License](https://img.shields.io/github/license/somnambulist-tech/read-models-bundle?logo=github)](https://github.com/somnambulist-tech/read-models-bundle/blob/master/LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/somnambulist/read-models-bundle?logo=php&logoColor=white)](https://packagist.org/packages/somnambulist/read-models-bundle)
[![Current Version](https://img.shields.io/packagist/v/somnambulist/read-models-bundle?logo=packagist&logoColor=white)](https://packagist.org/packages/somnambulist/read-models-bundle)

Integrates [read-models](https://github.com/somnambulist-tech/read-models) into Symfony via a bundle.

## Installation

Install using composer, or checkout / pull the files from github.com.

 * composer require somnambulist/read-models-bundle
 * add the bundle class to `config/bundles.php` as the last bundle
   * __Note:__ if not configured last, then any custom doctrine types may not be assigned to
     the type caster as the Manager will boot too soon.
 * add a config file (`config/packages/somnambulist.yaml`) with the configuration
 * map any custom casters via the `services.yaml` and tags
 * make some models
 * load some data: `<model>::find()`

An example config in `config/packages/somnambulist.yaml` could be:

```yaml
somnambulist_read_models:
    connections:
        'default': 'doctrine.dbal.default_connection'
        'App\Models\User': 'doctrine.dbal.user_connection'
    subscribers:
        request_manager_clearer: true
        messenger_manager_clearer: true
```

There are 2 event subscribers that are auto-registered:

 * identity map clear on kernel request, terminate
 * messenger clear on message handled / failed
 
If messenger is not installed, the listener will be disabled automatically.

### Connections

Each model type can have a custom connection by using the fully qualified class name as the
key. When referencing the Doctrine connections, be sure to leave off the `@` prefix if copying
from elsewhere.

Note that if the default is not specified, then the currently configured Doctrine default will
be auto-registered.

### Adding Attribute Casters

Add attribute casters as services and tag them with: `somnambulist.read_models.type_caster` to have
them automatically registered with the `Manager`s attribute caster instance.

To use the `attribute-model` generic type casters, and them as services and configure as needed:

```yaml
services:
    app.type_casters.some_service_identity:
        class: Somnambulist\Components\AttributeModel\TypeCasters\ExternalIdentityCaster
        arguments:
            $providerAttribute: 'some_service_name' 
            $identityAttribute: 'some_service_id'
            $remove: true 
            $types:
                -
                    some_service_id
        tags: ['somnambulist.read_models.type_caster']

    app.type_casters.my_value_object:
        class: Somnambulist\Components\AttributeModel\TypeCasters\SimpleValueObjectCaster
        arguments:
            $class: 'App\\Entities\\SomeEntityValueObject' 
            $types:
                -
                    short_name
        tags: ['somnambulist.read_models.type_caster']
```

Or if you don't need configuration, or have your own; use a resource:

```yaml
services:
    App\TypeCasters\:
        resource: '../../src/TypeCasters/'
        tags: ['somnambulist.read_models.type_caster']
```

The attribute names should be the array key names that the values are expected to appear in from
the data source. For example: if a `Product` model accesses a `products` table that has `unit_value`
and `unit_cur` fields, this could be converted to a `Money` object by using the `MoneyCaster`:

 ```yaml
 services:
     app.type_casters.product_money_caster:
         class: Somnambulist\Components\AttributeModel\TypeCasters\MoneyCaster
         arguments:
             $amtAttribute: 'unit_value' 
             $curAttribute: 'unit_cur'
             $remove: true 
             $types:
                 -
                     product_price
         tags: ['somnambulist.read_models.type_caster']
```

Then the caster can be referenced in the casts as: `'price' => 'product_price'` and the unit_value
and unit_cur attributes will be removed in favour of the single "price" attribute that will be a
Money value object. If the originals should be left in place, set `$remove` to `false`.

Additional casters can be added at any time; and existing casters may be overridden by re-using an
existing type name however this is discouraged. Note that once a type is registered it cannot be
removed.

## Usage

See [read-models](https://github.com/somnambulist-tech/read-models) for detailed docs on usage and
[attribute-models](https://github.com/somnambulist-tech/attribute-model) for more on the attribute caster.
