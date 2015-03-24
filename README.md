[![Build Status](https://travis-ci.org/jaroslavtyc/doctrineum-scalar.svg?branch=master)](https://travis-ci.org/jaroslavtyc/doctrineum-scalar)

##### Customizable enumeration type for Doctrine 2.4+

About custom Doctrine types, see the [official documentation](http://doctrine-orm.readthedocs.org/en/latest/cookbook/custom-mapping-types.html).
For default custom types see the [official documentation as well](http://doctrine-dbal.readthedocs.org/en/latest/reference/types.html).

## <span id="usage">Usage</span>
1. [Installation](#installation)
2. [Custom type registration](#custom-type-registration)
3. [Map property as an enum](#map-property-as-an-enum)
3. [Create enum](#create-enum)
4. [Understand the basics](#understand-the-basics)

### <span id="installation">Installation</span>
Edit composer.json at your project, add
```json
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/jaroslavtyc/doctrineum-scalar.git"
        }
    ],
```
then extend in the same composer.json file the field require by doctrineum
```json
    "require": {
        "doctrineum/scalar": "dev-master"
    }
```

### Custom type registration

```php
<?php
// in bootstrapping code
// ...
use Doctrine\DBAL\Types\Type;
use Doctrineum\Scalar\EnumType;
// ...
// Register type
Type::addType(EnumType::getTypeName(), '\Doctrineum\EnumType');
Type::addType(BarEnumType::getTypeName(), '\Foo\BarEnumType');
```

Or better for PHP [5.5+](http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.class.class)
```php
// ...
Type::addType(EnumType::getTypeName(), EnumType::class);
Type::addType(BarEnumType::getTypeName(), BarEnumType::class);
```

For Symfony2 using the config is the best approach

```yaml
# app/config/config.yml
doctrine:
    dbal:
        # ...
        types:
            enum: Doctrineum\Scalar\EnumType
            baz: Foo\BarEnumType
            #...
```

### Map property as an enum
```php
<?php
class Foo
{
    /** @Column(type="enum") */
    protected $field;
}
```

### Create enum
```php
<?php
$enum = \Doctrineum\Scalar\Enum::getEnum('foo bar');
```

*note: the type has the same (lowercased) name as the Enum class itself, but its just a string; you can change it at child class anytime; see \Doctrineum\Scalar\EnumType::getTypeName()*

#### Understand the basics
There are two roles - the factory and the value.
 - EnumType is the factory (as part of the Doctrine\DBAL\Types\Type family), building an Enum following rules.
 - Enum is the value holder, de facto singleton, represented by a class (and class, as you know, can do a lot of things, which is reason why enum is more sexy then whole scalar value).
