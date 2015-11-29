<?php
namespace Doctrineum\Scalar;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Granam\Scalar\Tools\ValueDescriber;
use Granam\Strict\Object\StrictObjectTrait;

/**
 * Class EnumType
 * @package Doctrineum\Scalar
 * @method static EnumType getType($name),
 * @see Type::getType
 */
class EnumType extends Type
{
    use StrictObjectTrait;

    /**
     * Its not directly used this library - the exactly same value is generated and used by
     * @see \Doctrineum\Scalar\SelfTypedEnum::getTypeName
     *
     * This constant exists to follow Doctrine type conventions.
     */
    const ENUM = 'enum';

    /**
     * @var string[][]
     */
    private static $subTypeEnums = [];

    /**
     * @param string $subTypeEnumClass
     * @param string $subTypeEnumValueRegexp
     *
     * @return bool
     */
    public static function addSubTypeEnum($subTypeEnumClass, $subTypeEnumValueRegexp)
    {
        if (static::hasSubTypeEnum($subTypeEnumClass)) {
            throw new Exceptions\SubTypeEnumIsAlreadyRegistered(
                'SubType enum ' . ValueDescriber::describe($subTypeEnumClass) . ' is already registered'
            );
        }
        /**
         * The class has to be self-registering to by-pass enum and enum type bindings,
         * @see SelfTypedEnum::createByValue
         */
        static::checkIfKnownEnum($subTypeEnumClass);
        static::checkRegexp($subTypeEnumValueRegexp);
        self::$subTypeEnums[static::getSubTypeEnumInnerNamespace()][$subTypeEnumClass] = $subTypeEnumValueRegexp;

        return static::hasSubTypeEnum($subTypeEnumClass);
    }

    /**
     * @param $subTypeClassName
     *
     * @return bool
     */
    public static function hasSubTypeEnum($subTypeClassName)
    {
        return isset(self::$subTypeEnums[static::getSubTypeEnumInnerNamespace()][$subTypeClassName]);
    }

    /**
     * @return string
     */
    protected static function getSubTypeEnumInnerNamespace()
    {
        return get_called_class();
    }

    /**
     * @param string $subTypeClassName
     */
    protected static function checkIfKnownEnum($subTypeClassName)
    {
        if (!class_exists($subTypeClassName)) {
            throw new Exceptions\SubTypeEnumClassNotFound(
                'Sub-type class ' . ValueDescriber::describe($subTypeClassName) . ' has not been found'
            );
        }
        if (!is_a($subTypeClassName, Enum::getClass(), true)) {
            throw new Exceptions\SubTypeEnumHasToBeEnum(
                'Sub-type class ' . ValueDescriber::describe($subTypeClassName) . ' has to be child of ' . Enum::getClass()
            );
        }
    }

    /**
     * @param string $regexp
     */
    protected static function checkRegexp($regexp)
    {
        if (!preg_match('~^(.).*\1$~', $regexp)) {
            // the regexp does not start and end with same characters
            throw new Exceptions\InvalidRegexpFormat(
                'The given regexp is not enclosed by same delimiters and therefore is not valid: '
                . ValueDescriber::describe($regexp)
            );
        }
    }

    /**
     * @return bool If enum has not been registered before and was registered now
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function registerSelf()
    {
        if (static::hasType(static::getTypeName())) {
            static::checkRegisteredType();

            return false;
        }

        static::addType(static::getTypeName(), get_called_class());

        return true;
    }

    /**
     * Gets the strongly recommended name of this type.
     * Its used at @see \Doctrine\DBAL\Platforms\AbstractPlatform::getDoctrineTypeComment
     *
     * @return string
     */
    public static function getTypeName()
    {
        // Doctrineum\Scalar\EnumType = EnumType
        $baseClassName = preg_replace('~(\w+\\\)*(\w+)~', '$2', get_called_class());
        // EnumType = Enum
        $baseTypeName = preg_replace('~Type$~', '', $baseClassName);

        // FooBarEnum = Foo_Bar_Enum = foo_bar_enum
        return strtolower(preg_replace('~(\w)([A-Z])~', '$1_$2', $baseTypeName));
    }

    protected static function checkRegisteredType()
    {
        $alreadyRegisteredType = static::getType(static::getTypeName());
        if (get_class($alreadyRegisteredType) !== get_called_class()) {
            throw new Exceptions\TypeNameOccupied(
                'Under type of name ' . ValueDescriber::describe(static::getTypeName()) .
                ' is already registered different type ' . get_class($alreadyRegisteredType)
            );
        }
    }

    /**
     * Finds out if current type is already in registry
     *
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function isRegistered()
    {
        return static::hasType(static::getTypeName());
    }

    /**
     * @param $subTypeEnumClass
     *
     * @return bool
     */
    public static function removeSubTypeEnum($subTypeEnumClass)
    {
        if (!static::hasSubTypeEnum($subTypeEnumClass)) {
            throw new Exceptions\SubTypeEnumIsNotRegistered(
                'Sub-type ' . ValueDescriber::describe($subTypeEnumClass) . ' is not registered'
            );
        }

        unset(self::$subTypeEnums[static::getSubTypeEnumInnerNamespace()][$subTypeEnumClass]);

        return !static::hasSubTypeEnum($subTypeEnumClass);
    }

    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array $fieldDeclaration The field declaration.
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform The currently used database platform.
     *
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'VARCHAR(' . $this->getDefaultLength($platform) . ')';
    }

    /**
     * @param AbstractPlatform $platform
     *
     * @return int
     */
    public function getDefaultLength(AbstractPlatform $platform)
    {
        return 64;
    }

    /**
     * Convert enum instance to database string (or null) value
     *
     * @param EnumInterface $value
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     *
     * @throws Exceptions\UnexpectedValueToDatabaseValue
     * @return string|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!is_object($value)) {
            throw new Exceptions\UnexpectedValueToDatabaseValue(
                'Expected object Doctrineum\Scalar\EnumInterface, got ' . gettype($value)
            );
        }
        if (!is_a($value, 'Doctrineum\Scalar\EnumInterface')) {
            throw new Exceptions\UnexpectedValueToDatabaseValue(
                'Expected Doctrineum\Scalar\EnumInterface, got ' . get_class($value)
            );
        }

        /** @var Enum $value probably */

        return $value->getValue();
    }

    /**
     * Convert database string value to Enum instance
     *
     * This does NOT cast non-string scalars into string (integers, floats etc).
     * Even null remains null in returned Enum.
     * (But saving the value into database and pulling it back probably will.)
     *
     * @param string|int|float|bool|null $value
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     *
     * @return Enum
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $this->convertToEnum($value);
    }

    /**
     * @param $enumValue
     *
     * @return Enum
     */
    protected function convertToEnum($enumValue)
    {
        if (!is_scalar($enumValue) && !is_null($enumValue)
            && (!is_object($enumValue) || !method_exists($enumValue, '__toString'))
        ) {
            throw new Exceptions\UnexpectedValueToEnum(
                'Unexpected value to convert. Expected scalar or null, got ' . gettype($enumValue)
            );
        }

        $enumClass = static::getEnumClass($enumValue);

        /** @var Enum $enumClass */

        return $enumClass::getEnum($enumValue);
    }

    /**
     * @param int|float|string|null $enumValue
     *
     * @return string Enum class absolute name
     */
    protected static function getEnumClass($enumValue)
    {
        // no subtype is registered at all
        if (!isset(self::$subTypeEnums[static::getSubTypeEnumInnerNamespace()]) || !count(self::$subTypeEnums[static::getSubTypeEnumInnerNamespace()])) {
            return static::getDefaultEnumClass();
        }

        foreach (self::$subTypeEnums[static::getSubTypeEnumInnerNamespace()] as $subTypeEnumClass => $subTypeEnumValueRegexp) {
            if (preg_match($subTypeEnumValueRegexp, $enumValue)) {
                return $subTypeEnumClass;
            }
        }

        // no subtype matched
        return static::getDefaultEnumClass();
    }

    /**
     * @return string
     */
    protected static function getDefaultEnumClass()
    {
        $enumTypeClass = get_called_class();
        $enumInSameNamespace = preg_replace('~Type$~', '', $enumTypeClass);
        if ($enumInSameNamespace === $enumTypeClass) {
            throw new Exceptions\CouldNotDetermineEnumClass('Enum class could not be parsed from enum type class ' . $enumTypeClass);
        }
        if (class_exists($enumInSameNamespace)) {
            return $enumInSameNamespace;
        }

        $inParentNamespace = preg_replace('~\\\(\w+)\\\(\w+)$~', '\\\$2', $enumInSameNamespace);
        if (class_exists($inParentNamespace)) {
            return $inParentNamespace;
        }

        throw new Exceptions\EnumClassNotFound('Default enum class not found for enum type ' . self::getClass());
    }

    /**
     * Gets the strongly recommended name of this type.
     * Its used at @see \Doctrine\DBAL\Platforms\AbstractPlatform::getDoctrineTypeComment
     *
     * @return string
     */
    public function getName()
    {
        return static::getTypeName();
    }

    /**
     * If this Doctrine Type maps to an already mapped database type,
     * reverse schema engineering can't take them apart. You need to mark
     * one of those types as commented, which will have Doctrine use an SQL
     * comment to type-hint the actual Doctrine Type.
     *
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     *
     * @return boolean
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
