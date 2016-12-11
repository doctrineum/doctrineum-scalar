<?php
namespace Doctrineum\Scalar;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrineum\SelfRegisteringType\AbstractSelfRegisteringType;
use Granam\Scalar\ScalarInterface;
use Granam\Scalar\Tools\ToScalar;
use Granam\Scalar\Tools\ToString;
use Granam\Tools\ValueDescriber;

/**
 * @method static ScalarEnumType getType($name),
 */
class ScalarEnumType extends AbstractSelfRegisteringType
{

    const SCALAR_ENUM = 'scalar_enum';
    /**
     * @var string[][]
     */
    private static $subTypeEnums = [];

    /**
     * You can register a class just once.
     *
     * @param string $subTypeEnumClass
     * @param string $subTypeEnumValueRegexp
     * @return bool
     * @throws \Doctrineum\Scalar\Exceptions\SubTypeEnumIsAlreadyRegistered
     * @throws \Doctrineum\Scalar\Exceptions\SubTypeEnumClassNotFound
     * @throws \Doctrineum\Scalar\Exceptions\SubTypeEnumHasToBeEnum
     * @throws \Doctrineum\Scalar\Exceptions\InvalidRegexpFormat
     */
    public static function registerSubTypeEnum($subTypeEnumClass, $subTypeEnumValueRegexp)
    {
        if (!static::hasSubTypeEnum($subTypeEnumClass, $subTypeEnumValueRegexp)) {
            // registering same subtype enum class but with different regexp cause exception in following method
            return static::addSubTypeEnum($subTypeEnumClass, $subTypeEnumValueRegexp);
        }

        return false;
    }

    /**
     * @param $subTypeClassName
     * @param string|null $subTypeEnumValueRegexp
     * @return bool
     * @throws \Doctrineum\Scalar\Exceptions\InvalidRegexpFormat
     */
    public static function hasSubTypeEnum($subTypeClassName, $subTypeEnumValueRegexp = null)
    {
        return
            isset(self::$subTypeEnums[static::getSubTypeEnumInnerNamespace()][$subTypeClassName])
            && (
                $subTypeEnumValueRegexp === null
                || (
                    self::guardRegexpValid($subTypeEnumValueRegexp)
                    && self::$subTypeEnums[static::getSubTypeEnumInnerNamespace()][$subTypeClassName] === (string)$subTypeEnumValueRegexp
                )
            );
    }

    /**
     * @return string
     */
    protected static function getSubTypeEnumInnerNamespace()
    {
        return static::class;
    }

    /**
     * Warning: Behave of registering more classes on same regexp (or simply matching same string) is undefined.
     *
     * @param string $subTypeEnumClass
     * @param string|ScalarInterface $subTypeEnumValueRegexp
     * @return bool
     * @throws \Doctrineum\Scalar\Exceptions\SubTypeEnumIsAlreadyRegistered
     * @throws \Doctrineum\Scalar\Exceptions\SubTypeEnumClassNotFound
     * @throws \Doctrineum\Scalar\Exceptions\SubTypeEnumHasToBeEnum
     * @throws \Doctrineum\Scalar\Exceptions\InvalidRegexpFormat
     */
    public static function addSubTypeEnum($subTypeEnumClass, $subTypeEnumValueRegexp)
    {
        if (static::hasSubTypeEnum($subTypeEnumClass)) {
            throw new Exceptions\SubTypeEnumIsAlreadyRegistered(
                'SubType enum ' . ValueDescriber::describe($subTypeEnumClass) . ' is already registered with regexp '
                . self::$subTypeEnums[static::getSubTypeEnumInnerNamespace()][$subTypeEnumClass]
                . ' (requested to register with regexp ' . ValueDescriber::describe($subTypeEnumValueRegexp) . ')'
            );
        }
        /** The class has to be self-registering to by-pass enum and enum type bindings, @see ScalarEnum::createEnum */
        static::checkIfKnownEnum($subTypeEnumClass);
        static::guardRegexpValid($subTypeEnumValueRegexp);
        self::$subTypeEnums[static::getSubTypeEnumInnerNamespace()][$subTypeEnumClass] = (string)$subTypeEnumValueRegexp;

        return true;
    }

    /**
     * @param string $subTypeClassName
     * @throws \Doctrineum\Scalar\Exceptions\SubTypeEnumClassNotFound
     * @throws \Doctrineum\Scalar\Exceptions\SubTypeEnumHasToBeEnum
     */
    protected static function checkIfKnownEnum($subTypeClassName)
    {
        if (!class_exists($subTypeClassName)) {
            throw new Exceptions\SubTypeEnumClassNotFound(
                'Sub-type class ' . ValueDescriber::describe($subTypeClassName) . ' has not been found'
            );
        }
        if (!is_a($subTypeClassName, ScalarEnum::getClass(), true)) {
            throw new Exceptions\SubTypeEnumHasToBeEnum(
                'Sub-type class ' . ValueDescriber::describe($subTypeClassName) . ' has to be child of ' . ScalarEnum::getClass()
            );
        }
    }

    /**
     * @param string $regexp
     * @return bool
     * @throws \Doctrineum\Scalar\Exceptions\InvalidRegexpFormat
     */
    private static function guardRegexpValid($regexp)
    {
        try {
            $stringRegexp = ToString::toString($regexp);
        } catch (\Granam\Scalar\Tools\Exceptions\WrongParameterType $invalidRegexp) {
            throw new Exceptions\InvalidRegexpFormat(
                'Given regexp is not safely convertible to string: ' . ValueDescriber::describe($regexp),
                $invalidRegexp->getCode(),
                $invalidRegexp
            );
        }
        if (!preg_match('~^(.).*\1$~', $stringRegexp)) {
            // the regexp does not start and end with same characters
            throw new Exceptions\InvalidRegexpFormat(
                'The given regexp is not enclosed by same delimiters and therefore is not valid: '
                . ValueDescriber::describe($stringRegexp)
            );
        }

        return true;
    }

    /**
     * @param $subTypeEnumClass
     * @return bool
     * @throws \Doctrineum\Scalar\Exceptions\SubTypeEnumIsNotRegistered
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
     * Gets the strongly recommended name of this type.
     * Its used at @see \Doctrine\DBAL\Platforms\AbstractPlatform::getDoctrineTypeComment
     *
     * Note: also PhpStorm use it for click-through via @Column(type="foo-bar") notation,
     * if and only if is the value a constant (direct return of a string or constant).
     *
     * @return string
     */
    public function getName()
    {
        return self::SCALAR_ENUM;
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
     * @param ScalarEnumInterface $value
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     *
     * @throws Exceptions\UnexpectedValueToDatabaseValue
     * @return string|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }
        if (!is_object($value) || !is_a($value, ScalarEnumInterface::class)) {
            throw new Exceptions\UnexpectedValueToDatabaseValue(
                'Expected NULL or instance of ' . ScalarEnumInterface::class . ', got ' . ValueDescriber::describe($value)
            );
        }

        return $value->getValue();
    }

    /**
     * Convert database string value to Enum instance
     *
     * This does NOT cast non-string scalars into string (integers, floats etc).
     * Even null remains null in returned Enum.
     * (But saving the value into database and pulling it back probably will do the to-string conversion)
     *
     * @param string|int|float|bool|null $value
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     * @return ScalarEnum|null
     * @throws \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     * @throws \Doctrineum\Scalar\Exceptions\CouldNotDetermineEnumClass
     * @throws \Doctrineum\Scalar\Exceptions\EnumClassNotFound
     * @throws \Doctrineum\Scalar\Exceptions\CanNotCreateInstanceOfAbstractEnum
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value === null
            ? null
            : $this->convertToEnum($value);
    }

    /**
     * @param $enumValue
     * @return ScalarEnum|ScalarEnumInterface
     * @throws \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     * @throws \Doctrineum\Scalar\Exceptions\CouldNotDetermineEnumClass
     * @throws \Doctrineum\Scalar\Exceptions\EnumClassNotFound
     * @throws \Doctrineum\Scalar\Exceptions\CanNotCreateInstanceOfAbstractEnum
     */
    protected function convertToEnum($enumValue)
    {
        $enumValue = $this->sanitizeValueForEnum($enumValue);
        // class of main enum or its registered sub-type, according to enum type and current value
        $enumClass = static::getEnumClass($enumValue);

        return $enumClass::getEnum($enumValue);
    }

    /**
     * @param $valueForEnum
     * @return float|int|null|string
     * @throws \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    protected function sanitizeValueForEnum($valueForEnum)
    {
        try {
            return ToScalar::toScalar($valueForEnum);
        } catch (\Granam\Scalar\Tools\Exceptions\WrongParameterType $exception) {
            throw new Exceptions\UnexpectedValueToEnum(
                'Unexpected value to convert. Expected scalar or null, got '
                . ValueDescriber::describe($valueForEnum),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @param int|float|string|null $enumValue
     * @return string|ScalarEnum Enum class absolute name
     * @throws \Doctrineum\Scalar\Exceptions\CouldNotDetermineEnumClass
     * @throws \Doctrineum\Scalar\Exceptions\EnumClassNotFound
     */
    protected static function getEnumClass($enumValue)
    {
        if (!array_key_exists(static::getSubTypeEnumInnerNamespace(), self::$subTypeEnums)
            || count(self::$subTypeEnums[static::getSubTypeEnumInnerNamespace()]) === 0
        ) {
            // no subtype is registered at all
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
     * @throws \Doctrineum\Scalar\Exceptions\CouldNotDetermineEnumClass
     * @throws \Doctrineum\Scalar\Exceptions\EnumClassNotFound
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
