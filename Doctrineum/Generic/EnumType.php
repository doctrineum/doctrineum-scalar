<?php
namespace Doctrineum\Generic;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Granam\Strict\Object\StrictObjectTrait;

/**
 * Class EnumType
 * @package Doctrineum\Generic
 * @method static EnumType getType($name),
 * @see Type::getType
 */
class EnumType extends Type
{
    use StrictObjectTrait;

    const TYPE = 'enum';

    // default enum class; you can control it by platform, or by explicit overload it in child class, if needed
    const ENUM_CLASS = Enum::class;

    // default SQL column length; you can control it by platform, or by explicit overload in child class
    const VARCHAR_LENGTH = 64;

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
     * @return int
     */
    public function getDefaultLength(AbstractPlatform $platform)
    {
        return self::VARCHAR_LENGTH;
    }

    /**
     * Convert enum instance to database string (or null) value
     *
     * @param Enum $value
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     * @throws Exceptions\UnexpectedValueToDatabaseValue
     * @return string|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!is_object($value)) {
            throw new Exceptions\UnexpectedValueToDatabaseValue('Expected object of class ' . Enum::class . ', got ' . gettype($value));
        }
        if (!is_a($value, Enum::class)) {
            throw new Exceptions\UnexpectedValueToDatabaseValue('Expected ' . Enum::class . ', got ' . get_class($value));
        }

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
     * @throws Exceptions\InvalidArgument
     * @return Enum
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $this->convertToEnum($value);
    }

    /**
     * @param $value
     * @return Enum
     */
    protected function convertToEnum($value)
    {
        // note: forcing the value to string is not intended
        if (!is_scalar($value) && !is_null($value)) {
            throw new Exceptions\UnexpectedValueToEnum('Unexpected value to convert. Expected scalar or null, got ' . gettype($value));
        }

        $enumClass = static::getEnumClass();
        /** @var Enum $enumClass */
        return $enumClass::get($value);
    }

    /**
     * @return string Enum class absolute name
     */
    protected static function getEnumClass()
    {
        return static::ENUM_CLASS;
    }

    /**
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName()
    {
        return static::TYPE;
    }

    /**
     * If this Doctrine Type maps to an already mapped database type,
     * reverse schema engineering can't take them apart. You need to mark
     * one of those types as commented, which will have Doctrine use an SQL
     * comment to type-hint the actual Doctrine Type.
     *
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     * @return boolean
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
