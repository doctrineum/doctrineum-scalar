<?php
namespace Doctrineum\Scalar;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
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
            throw new Exceptions\UnexpectedValueToDatabaseValue('Expected object of class ' . EnumInterface::class . ', got ' . gettype($value));
        }
        if (!is_a($value, EnumInterface::class)) {
            throw new Exceptions\UnexpectedValueToDatabaseValue('Expected ' . EnumInterface::class . ', got ' . get_class($value));
        }

        /** @var Enum $value probably */

        return $value->getEnumValue();
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
     * @throws Exceptions\InvalidArgument
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
        // no subtype is registered
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
        return preg_replace('~Type$~', '', static::class);
    }

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
                'SubType enum class ' . var_export($subTypeEnumClass, true) . ' is already registered'
            );
        }

        static::checkSubTypeEnumClass($subTypeEnumClass);
        static::checkRegexp($subTypeEnumValueRegexp);
        self::$subTypeEnums[static::getSubTypeEnumInnerNamespace()][$subTypeEnumClass] = $subTypeEnumValueRegexp;

        return static::hasSubTypeEnum($subTypeEnumClass);
    }

    /**
     * @return string
     */
    protected static function getSubTypeEnumInnerNamespace()
    {
        return static::class;
    }

    protected static function checkSubTypeEnumClass($subtypeClassName)
    {
        if (!is_a($subtypeClassName, EnumInterface::class, true /* allow tested class as a string */)) {
            throw new Exceptions\InvalidClassForSubTypeEnum(
                'SubType enum class ' . var_export($subtypeClassName, true) . ' has to be ' . EnumInterface::class
            );
        }
    }

    /**
     * @param string $regexp
     */
    protected static function checkRegexp($regexp)
    {
        // the regexp does not start and end with same characters
        if (!preg_match('~^(.).*\1$~', $regexp)) {
            throw new Exceptions\InvalidRegexpFormat(
                'The given regexp is not enclosed by same delimiters and therefore is not valid: ' . var_export($regexp, true)
            );
        }
    }

    /**
     * @return EnumType
     */
    public static function getIt()
    {
        return static::getType(static::getTypeName());
    }

    /**
     * Add current type to registry
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function registerSelf()
    {
        if (static::isRegistered()) {
            return false;
        }

        static::addType(static::getTypeName(), static::class);

        return static::hasType(static::getTypeName());
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
     * @param $subTypeClassName
     *
     * @return bool
     */
    public static function hasSubTypeEnum($subTypeClassName)
    {
        return isset(self::$subTypeEnums[static::getSubTypeEnumInnerNamespace()][$subTypeClassName]);
    }

    /**
     * @param $subTypeEnumClass
     *
     * @return bool
     */
    public static function removeSubTypeEnum($subTypeEnumClass)
    {
        if (!static::hasSubTypeEnum($subTypeEnumClass)) {
            throw new \LogicException('Sub-type of class ' . var_export($subTypeEnumClass, true) . ' is not registered');
        }

        unset(self::$subTypeEnums[static::getSubTypeEnumInnerNamespace()][$subTypeEnumClass]);

        return !static::hasSubTypeEnum($subTypeEnumClass);
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
     * Gets the strongly recommended name of this type.
     * Its used at @see \Doctrine\DBAL\Platforms\AbstractPlatform::getDoctrineTypeComment
     *
     * @return string
     */
    public static function getTypeName()
    {
        // Doctrineum\Scalar\EnumType = EnumType
        $baseClassName = preg_replace('~(\w+\\\)*(\w+)~', '$2', static::class);
        // EnumType = Enum
        $baseTypeName = preg_replace('~Type$~', '', $baseClassName);

        // FooBarEnum = Foo_Bar_Enum = foo_bar_enum
        return strtolower(preg_replace('~(\w)([A-Z])~', '$1_$2', $baseTypeName));
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
