[![Build Status](https://travis-ci.org/jaroslavtyc/doctrineum-scalar.svg?branch=master)](https://travis-ci.org/jaroslavtyc/doctrineum-scalar)
[![Latest Stable Version](https://poser.pugx.org/doctrineum/scalar/v/stable.svg)](https://packagist.org/packages/doctrineum/scalar)
[![License](https://poser.pugx.org/doctrineum/scalar/license.svg)](http://en.wikipedia.org/wiki/MIT_License)

##### Customizable enumeration type for Doctrine 2.4+

About custom Doctrine types, see the [official documentation](http://doctrine-orm.readthedocs.org/en/latest/cookbook/custom-mapping-types.html).
For default custom types see the [official documentation as well](http://doctrine-dbal.readthedocs.org/en/latest/reference/types.html).

#### Requires PHP 5.4

## <span id="usage">Usage</span>
1. [Installation](#installation)
2. [Custom type registration](#custom-type-registration)
3. [Map property as an enum](#map-property-as-an-enum)
4. [Create enum](#create-enum)
5. [Register subtype enum](#register-subtype-enum)
6. [NULL is still an enum class](#null-is-still-an-enum-class)
7. [Understand the basics](#understand-the-basics)
8. [Exceptions philosophy](#exceptions-philosophy)

### <span id="installation">Installation</span>
Edit composer.json at your project, add
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
use Doctrineum\Scalar\ScalarEnumType;
// ...
// Register type
Type::addType(ScalarEnumType::getTypeName(), '\Doctrineum\ScalarEnumType');
Type::addType(BarScalarEnumType::getTypeName(), '\Foo\BarScalarEnumType');
```

Or better with PHP [5.5+](http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.class.class)
```php
// ...
Type::addType(ScalarEnumType::getTypeName(), ScalarEnumType::class);
Type::addType(BarScalarEnumType::getTypeName(), BarScalarEnumType::class);
```

Or just by helper method
```php
ScalarEnum::registerSelf(); // quick self-registration
```

For Symfony2 using the config is the best approach
```yaml
# app/config/config.yml
doctrine:
    dbal:
        # ...
        types:
            scalar_enum: Doctrineum\Scalar\ScalarEnumType
            baz: Foo\BarScalarEnumType
            #...
```

### Map property as an enum
```php
<?php
class Foo
{
    /** @Column(type="scalar_enum") */
    protected $field;
}
```

### Create enum
```php
<?php
use Doctrineum\Scalar\ScalarEnum;
$enum = \Doctrineum\Scalar\ScalarEnum::getEnum('foo bar');
```

### Register subtype enum
You can register infinite number of enums, which are used according to a regexp of your choice.
```php
<?php
use Doctrineum\Scalar\ScalarEnumType;
ScalarEnumType::addSubTypeEnum('\Foo\Bar\YourEnum', '~get me different enum for this value~');
// ...
$enum = $ScalarEnumType->convertToPHPValue('foo');
get_class($enum) === '\Doctrineum\Scalar\ScalarEnum'; // true
get_class($enum) === '\Foo\Bar\YourEnum'; // false
$byRegexpDeterminedEnum = $ScalarEnumType->convertToPHPValue('And now get me different enum for this value.');
get_class($byRegexpDeterminedEnum) === '\Foo\Bar\YourEnum'; // true
```

### NULL is still an enum class
You can create ScalarEnum with NULL value, persist it (in database it will be simple as a NULL) and fetch it again
 and you will get ScalarEnum with NULL again. Not just NULL.
So again, **any** NULL value on place of ScalarEnum is converted to ScalarEnum with NULL.
Even not-set-at-all enums are after persist-flush-fetch built as ScalarEnum with NULL inside.

Beware on using subtypes only with main enum as an abstract class. You have to resolve database-NULL-to-PHP-value conversion,
or register subtype for NULL value, otherwise fatal error by abstract class instance creation occurs.

Note: more type-specific enums like string enum, integer enum and so have different behaviour.
They convert null to their primitive type on enum creation, on fetch of NULL from database it remains null.

#### Understand the basics
There are two roles - the factory and the value.

 - ScalarEnumType is the factory (as part of the Doctrine\DBAL\Types\Type family), building an ScalarEnum by following ScalarEnumType rules.
 - ScalarEnum is the value holder, de facto singleton, represented by a class. And class, as you know, can do a lot of things, which makes enum more sexy then whole scalar value.
 - Subtype is an ScalarEnumType, but ruled not just by type, but also by current value itself. One type can has any number of subtypes, in dependence on your imagination and used enum values.

##### Exceptions philosophy
Doctrineum adopts [Granam exception hierarchy ideas](https://github.com/jaroslavtyc/granam-exception-hierarchy).
That means every exceptionable state is probably by a **logic** mistake, rather than a runtime situation.
