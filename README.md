Enumeration type for Doctrine 2.4+

About custom Doctrine types, see the [official documentation](http://doctrine-orm.readthedocs.org/en/latest/cookbook/custom-mapping-types.html).
For default custom types see the [official documentation as well](http://doctrine-dbal.readthedocs.org/en/latest/reference/types.html).

## <span id="usage">Usage</span>
1. #### [Installation](#installation)
2. #### [Custom type registration](#custom_type_registration)
3. #### [Map property as an enum](#map_property_as_an_enum)
4. #### [Customization (advanced)](#customization)

### <span id="installation">Installation</span>
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

### <span id="custom_type_registration">Custom type registration</span>

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

### <span id="map_property_as_an_enum">Map property as an enum</span>
```php
<?php
class Foo
{
    /** @Column(type="\Doctrineum\Enum") */
    protected $field;
}
```

*note: the type has the same name as the Enum class itself, but its just a string; you can change it at child class anytime; see **EnumType::TYPE** constant*

### <span id="customization">Customization</span>
*This is quite advanced. That means it is not trivial and for first start you do not need it.*
```php
<?php
// bootstrap.php
require_once "vendor/autoload.php";

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$paths = array("/path/to/entity-files");
$isDevMode = false;

// the connection configuration
$dbParams = array(
    'driver'   => 'custom_enum_pdo_mysql', // see "your" CustomEnumPdoMysql class bellow
    'user'     => 'root',
    'password' => '',
    'dbname'   => 'foo',
);

$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
$entityManager = EntityManager::create($dbParams, $config);
```

```php
<?php
// example of extended default driver
use Doctrine\DBAL\Driver\PDOMySql\Driver;
use Doctrineum\DoctrineumPlatform

class CustomEnumPdoMysql extends Driver implements DoctrineumPlatform {

    public function getEnumSQLDeclaration() {
        // write your very own SQL column representation here
    }

    public function convertToEnum($valueFromDatabase) {
        // place for your specific SQL value to PHP enum class translation
    }
}
```
