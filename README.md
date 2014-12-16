Enumeration type for Doctrine 2.4+

About custom Doctrine types, see the [official documentation](http://doctrine-orm.readthedocs.org/en/latest/cookbook/custom-mapping-types.html).
For default custom types see the [official documentation as well](http://doctrine-dbal.readthedocs.org/en/latest/reference/types.html).

## Usage
### Custom type registration:

```php
<?php
// in bootstrapping code
// ...
use Doctrine\DBAL\Types\Type;
// ...
// Register type
Type::addType(\Doctrineum\EnumType::TYPE, '\Doctrineum\EnumType');
/*
 Or better for PHP 5.5+
 Type::addType(\Doctrineum\EnumType::TYPE, \Doctrineum\EnumType::class);
*/
```

### Map property as an enum
```php
<?php
class Foo
{
    /** @Column(type="\Doctrineum\Enum") */
    protected $field;
}
```

*note: the type has the same name as the Enum class itself, but its just a string; you can change it at child class anytime; see **EnumType::TYPE** constant*

### Installation:
Edit composer.json at your project, add
```json
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/jaroslavtyc/doctrineum.git"
        }
    ],
```
then extend in the same composer.json file the field require by doctrineum
```json
    "require": {
        ...
        "jaroslavtyc/doctrineum": "1.0.*@alpha"
    }
```
