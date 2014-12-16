Enumeration type for Doctrine 2.4+

About custom Doctrine types, see the official [documentation](http://doctrine-orm.readthedocs.org/en/latest/cookbook/custom-mapping-types.html).

Custom type registration:

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

Installation:
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
